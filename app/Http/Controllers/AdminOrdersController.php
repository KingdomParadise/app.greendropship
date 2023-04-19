<?php

namespace App\Http\Controllers;

use App\Libraries\Magento\MagentoApi;
use Illuminate\Http\Request;
use App\Order;
use App\OrderRefund;
use App\OrderRefundDetail;
use App\OrderShippingAddress;
use App\Status;
use App\OrderDetails;
use App\PaymentSession;
use App\User;
use App\Libraries\Magento\MRefund;
use Illuminate\Support\Facades\Auth;
use Stripe\Refund;
use Stripe\Stripe;
use Stripe\Charge;
use Stripe\PaymentIntent;
use Illuminate\Support\Facades\Log;
use Stripe\Balance;

class AdminOrdersController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        Stripe::setApiKey(config('stripe.api_keys.secret_key'));
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $this->authorize('view-admin-orders');

        $order_list = Order::join('order_shipping_address as osa', 'orders.id', 'osa.id_order')
            ->join('status as st1', 'st1.id', 'orders.financial_status')
            ->join('status as st2', 'st2.id', 'orders.fulfillment_status')
            ->join('users as us', 'us.id', 'orders.id_customer');
        if ($request->merchantid != '') {
            $order_list = $order_list->where('orders.id_customer', $request->merchantid);
        }

        return view('admin_orders', [
            'status' => Status::get(),
            'merchant_name' => $request->merchantid != '' ? User::find($request->merchantid)->name : '',
            'total_count' => $order_list->count()
        ]);
    }

    public function show($id_shopify)
    {
        $order = Order::select('*')->where('id_shopify', $id_shopify)->first();
        $osa = OrderShippingAddress::select('order_shipping_address.*')
            ->where('order_shipping_address.id_order', $order->id)
            ->first();

        $fs = Status::select('status.*')
            ->where('status.id', $order->financial_status)
            ->first();

        $os = Status::select('status.*')
            ->where('status.id', $order->fulfillment_status)
            ->first();

        $refund = null;
        $ord = null;
        if($order->financial_status == 14 || $order->financial_status == 3 || $order->financial_status == 15) {
            $refund = OrderRefund::where('order_id', $order->id)->latest('created_at')->first();
            // order refund details
            $ord = OrderRefundDetail::select(
                'order_refund_details.sku',
                'order_refund_details.quantity'
            )
                ->join('products', 'order_refund_details.sku', 'products.sku')
                ->where('order_refund_details.order_id', $order->id)->get();
        }

        $merchant = User::find($order->id_customer);

        $order_products = OrderDetails::select(
            'order_details.sku',
            'order_details.price',
            'order_details.quantity',
            'products.name',
            'products.images',
        )
            ->join('products', 'order_details.sku', 'products.sku')
            ->where('order_details.id_order', $order->id)->get();

        foreach ($order_products as $pro) {
            if ($pro['images'] != null && count(json_decode($pro['images'])) > 0) {
                $pro->image_url = env('URL_MAGENTO_IMAGES') . '/dc09e1c71e492175f875827bcbf6a37c' . json_decode($pro->images)[0]->file;
            } else {
                $pro->image_url = '/img/default_image_75.png';
            }
        }

        $sessionPay = PaymentSession::where('id_orders', 'like', "%$order->id%")
            ->whereJsonContains('data->client_reference_id', strval($order->id_customer))
            ->first();

        $user_canceled = User::find($order->user_id_canceled);
        $user_canceled_name = '';
        $api = MagentoApi::getInstance();
        $criteria = [
            'searchCriteria[filterGroups][1][filters][0][field]' => 'increment_id',
            'searchCriteria[filterGroups][1][filters][0][value]' => $order->magento_order_id,
            'searchCriteria[filterGroups][1][filters][0][condition_type]' => "eq"
        ];
        $mg_order = $api->query('GET', 'orders', $criteria);

        if ($user_canceled != null) {
            $user_canceled_name = $user_canceled->name;
        }

        return view('admin_orders_detail', [
            'order' => $order,
            'mg_order' => json_decode($mg_order) ? (json_decode($mg_order)->total_count ? json_decode($mg_order)->items[0] : '' ) : '',
            'osa' => $osa,
            'fs' => $fs,
            'os' => $os,
            'refund'=> $refund,
            'ord' => $ord,
            'payment_intent' => !is_null($sessionPay) ? $sessionPay->payment_intent : '',
            'payment_card_number' => !is_null($sessionPay) ? $sessionPay->card_last4 : '',
            'order_products' => $order_products,
            'merchant' => $merchant,
            'user_canceled' => $user_canceled_name
        ]);
    }

    public function cancel(Order $order)
    {
        $order->fulfillment_status = 9; //canceled
        $order->user_id_canceled = Auth::user()->id;
        $order->canceled_at = date('Y-m-d H:i:s');
        $order->save();

        return redirect('admin/orders');
    }

    public function rejectRefund(Request $request)
    {
        $order_id = $request->get('order_id');
        $note = $request->get('reject_notes');

        $order = Order::where('id', $order_id)->first();

        try {
            $order->financial_status = 15;
            $order->canceled_at = date('Y-m-d H:i:s');
            $order->notes = $note;
            $order->save();
            return redirect('admin/orders')->with('success', 'Reject refund request successfully');
        } catch(Exception $e) {
            return redirect('admin/orders')->with('error', 'Something went wrong. Please try again');
        }
    }

    public function approveRefund(Request $request)
    {
        try{
            $order_id = $request->get('order_id');
            $refund_amount = $request->get('refund_amount');
            $order = Order::whereId($order_id)->first();
            $shipping_cost = $request->get('shipping_cost');
            $orderRefund = OrderRefund::where('order_id', $order_id)->latest('created_at')->first();
            $ords = OrderRefundDetail::where('order_id', $order_id)->get();
            $qtys = $request->get('qtys');

            $mods = $this->getMOrderDetails($order->magento_entity_id); // get magento order details

            $creditMemoData = null; // credit memo data

            Log::info('Magento Order Details', ['mods' => $mods]);

            foreach ($qtys as $sku => $quantity) {
                $ord = $ords->where('sku', $sku)->where('order_id', $order_id)->first();
                $ord->quantity = $quantity;
                $ord->save();

                if($quantity != "0" && $quantity != ""){
                    foreach ($mods as $order_item) {
                        if ($order_item['sku'] == $sku) {
                            $creditMemoData['creditmemo']['items'][] = [
                                'qty' => intval($quantity),
                                'order_item_id' => $order_item['item_id'],
                                'price' => $order_item['price'],
                            ];
                        }
                    }
                }
            }

            $creditMemoData['creditmemo']['order_currency_code'] = 'USD';
            $creditMemoData['creditmemo']['order_id'] = $order->magento_entity_id;
            $creditMemoData['creditmemo']['shipping_amount'] = floatval($shipping_cost);
            $creditMemoData['offlineRequested'] = true;

            // Log::info('Credit Memo Data', ['creditData' => $creditMemoData]);

            $balance = Balance::retrieve();
            Log::info('Balance', ['balance' => $balance->available[0]->amount]);

            exit;
            //<----------- Start Refund Process ------------->//
            // get payment session id from order_id
            $sessionPay = PaymentSession::where('id_orders', 'like', '%' . $order->id . '%')
                ->whereJsonContains('data->client_reference_id', strval($order->id_customer))
                ->first();

            // get available total amount
		    $total_amount = number_format(($order->total + $order->shipping_price) * 0.95, 2);

            // Test
            // $pi = "pi_3MuPlwKmDdoQblW013lNqqXu"; // This is test Payment Intent

            // Real PI
            $pi = $sessionPay->payment_intent;
            // Log::info('payment intent id', ['payment intent id' => $pi]);

            $paymentIntent = PaymentIntent::retrieve($pi);

            $chargeId = $paymentIntent->latest_charge;

            $charge = Charge::retrieve($chargeId);

            if($charge->refunded) {
                $order->financial_status = 3; //Refunded
                $order->save();

                $orderRefund->refunded_amount = $total_amount;
                $orderRefund->save();

                $refundReqCount = Order::where('financial_status', 14)->count();

                view()->share('refundReqCount', $refundReqCount);

                return redirect('admin/orders')->with('error', 'This transaction was refunded already');
            }

            // Validate amount of refund
            if($refund_amount < 0 || $refund_amount > $total_amount){
				return back()->with('error', 'You can refund the amount at most US $' . $total_amount);
			}

            $refund = Refund::create([
                'charge' => $chargeId,
                'reason' => 'requested_by_customer',
                'amount' => $refund_amount * 100
            ]);

            Log::info('status', ['status' => $refund->status]);

            if($refund->status === 'succeeded') {
                $order->financial_status = 3; // Refunded
                $order->save();

                $orderRefund->refunded_amount = $refund_amount;
                $orderRefund->refunded_shipping_cost = $shipping_cost;
                $orderRefund->save();

                $refundReqCount = Order::where('financial_status', 14)->count();

                view()->share('refundReqCount', $refundReqCount);

                return redirect('admin/orders')->with('success', 'Refund successfully');
            } else {
                return redirect('admin/orders')->with('error', 'Something went wrong. Please try again');
            }
        } catch (Exception $e) {
            return redirect('admin/orders')->with('error', 'Something went wrong. Please try again');
        }
    }

    public function getMOrderDetails($magento_entity_id)
    {
        $order_detail = MRefund::getOrderDetail($magento_entity_id);

        $details = array();

        foreach (json_decode($order_detail)->items as $item) {
            $details[] = array(
                'sku' => $item->sku,
                'name' => $item->name,
                'price' => $item->price,
                'item_id' => $item->item_id
            );
        }

        return $details;
    }

    // public function makeCreditData()
}
