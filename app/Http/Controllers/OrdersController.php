<?php

namespace App\Http\Controllers;

use App\Libraries\Magento\MagentoApi;
use App\Libraries\Magento\MOrder;
use App\Libraries\OrderStatus;
use App\MonthlyRecurringPlan;
use App\MonthlyRecurringPlanOrders;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Order;
use App\OrderRefund;
use App\OrderRefundDetail;
use App\OrderShippingAddress;
use App\Status;
use App\OrderDetails;
use App\PaymentSession;
use App\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class OrdersController extends Controller
{
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware('auth');
	}

	public function index(Request $request)
	{
		$this->authorize('plan_view-manage-orders');
		$order_list = Order::select(
			'orders.*',
			'osa.first_name',
			'osa.last_name',
			'st1.name as status1',
			'st1.color as color1',
			'st2.name as status2',
			'st2.color as color2',
			'st1.id as financial_status',
			'st2.id as fulfillment_status'
		)
			->join('order_shipping_address as osa', 'orders.id', 'osa.id_order')
			->join('status as st1', 'st1.id', 'orders.financial_status')
			->join('status as st2', 'st2.id', 'orders.fulfillment_status')
			->where('orders.id_customer', Auth::user()->id);

		$is_notification = false;
		if ($request->notifications != '' && $request->notifications) {
			$is_notification = true;
			$order_list = $order_list->where('financial_status', OrderStatus::Outstanding)
				->where('fulfillment_status', OrderStatus::NewOrder);
		}

		$total_count = $order_list->count();

		return view('orders', array(
			'status' => Status::get(),
			'is_notification' => $is_notification,
			'total_count' => $total_count
		));
	}

	public function show($id_shopify)
	{
		$this->authorize('plan_view-manage-orders');
		$order = Order::select('*')->where('id_shopify', $id_shopify)->first();
		if ($order != null) {
			if (Auth::user()->id == $order->id_customer) {
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
				if($order->financial_status == 14 || $order->financial_status == 3 || $order->financial_status == 15) {
						$refund = OrderRefund::where('order_id', $order->id)->latest('created_at')->first();
				}

				$order_products = OrderDetails::select(
					'order_details.sku',
					'order_details.price',
					'order_details.quantity',
					'products.name',
					'products.images'
				)
					->join('products', 'order_details.sku', 'products.sku')
					->where('order_details.id_order', $order->id)->get();

				foreach ($order_products as $pro) {
					if ($pro['images'] != null && count(json_decode($pro['images'])) > 0) {
											if (json_decode($pro['images'])[0]->file == '') {
							$pro->image_url = '/img/default_image_75.png';
						} else {
							$pro->image_url = env('URL_MAGENTO_IMAGES') . '/dc09e1c71e492175f875827bcbf6a37c' . json_decode($pro->images)[0]->file;
						}
					} else {
						$pro->image_url = '/img/default_image_75.png';
					}
				}
				$sessionPay = PaymentSession::where('id_orders', 'like', "%$order->id%")
            ->whereJsonContains('data->client_reference_id', strval($order->id_customer))
            ->first();

				$user_canceled = User::find($order->user_id_canceled);
				$user_canceled_name = '';
				if ($user_canceled != null) {
					$user_canceled_name = $user_canceled->name;
				}
				$api = MagentoApi::getInstance();
				$criteria = [
					'searchCriteria[filterGroups][1][filters][0][field]' => 'increment_id',
					'searchCriteria[filterGroups][1][filters][0][value]' => $order->magento_order_id,
					'searchCriteria[filterGroups][1][filters][0][condition_type]' => "eq"
				];
				$mg_order = $api->query('GET', 'orders', $criteria);

				self::GDSLOG('View Order Detail', 'View Order Detail => ' . $order->id);

				return view('order_detail', array(
					'order' => $order,
					'mg_order' => json_decode($mg_order) ? (json_decode($mg_order)->total_count ? json_decode($mg_order)->items[0] : '') : '',
					'osa' => $osa,
					'fs' => $fs,
					'os' => $os,
					'refund' => $refund,
					'payment_intent' => !is_null($sessionPay) ? $sessionPay->payment_intent : '',
					'payment_card_number' => !is_null($sessionPay) ? $sessionPay->card_last4 : '',
					'order_products' => $order_products,
					'isValidOrder' => self::isValidOrderLimit($order->id),
					'user_canceled' => $user_canceled_name,
					'states' => MOrder::USAstates(),
					'state_key' => MOrder::getSateIdByName($osa->province),
					'shopify_url' => Auth::user()->shopify_url
				));
			} else {
				return redirect('orders');
			}
		} else {
			return redirect('orders');
		}
	}


	//deprecated
	public static function isValidOrderLimit($order_id)
	{

		if (Auth::user()->plan == 'basic') {
			$order = Order::find($order_id);
			$current_period = MonthlyRecurringPlan::where('current', 1)->where('merchant_id', Auth::user()->id)->first();
			if ($current_period != null) {
				if (date('Y-m-d', strtotime($order->created_at)) <= date($current_period->start_date)) {
					return true;
				}
				//takes into account the orders which are within the LIMIT_ORDERS to validate
				$current_period_orders = MonthlyRecurringPlanOrders::where('period_id', $current_period->id)->orderBy('order_id', 'asc')
					->take(env('LIMIT_ORDERS', 1))->get();
				$found = $current_period_orders->first(function ($value, $key) use ($order_id) {
					return $value->order_id == $order_id;
				});
				return $found != null;
			}
			return false;
		}
		return true;
	}
	//deprecated
	public function createCart(Request $request)
	{
		$order = Order::find($request->order_id);
		$cart_id = MOrder::createCart(json_decode($order->data), $order);
		$result = MOrder::getEstimatesShipping(json_decode($order->data), $cart_id);

		return response()->json(collect(json_decode($result))->sortBy('amount')->toArray());
	}

	public function saveAddress(Request $request)
	{
		$order = OrderShippingAddress::where('id_order', $request->order_id)->first();
		if ($order != null) {

			self::GDSLOG('Update Address', 'Update Address => ' . $request->order_id);

			//Current address
			self::GDSLOG('Current Address', '------');
			self::GDSLOG('address1', '=>' . $order->address1);
			self::GDSLOG('address2', '=>' . $order->address2);
			self::GDSLOG('city', '=>' . $order->city);
			self::GDSLOG('province', '=>' . $order->province);
			self::GDSLOG('province_code', '=>' . $order->province_code);
			self::GDSLOG('zip', '=>' . $order->zip);

			$order->address1 = $request->address1;
			$order->address2 = $request->address2;
			$order->city = $request->city;
			$order->province = MOrder::getSateById($request->state);
			$order->province_code = MOrder::getSateCodeByName($order->province);
			$order->zip = $request->zip;
			$order->update_merchant_id = Auth::user()->id;
			$order->update_date = date('Y-m-d H:i:s');
			$order->save();

			//update address
			self::GDSLOG('New Address', '------');
			self::GDSLOG('address1', '=>' . $order->address1);
			self::GDSLOG('address2', '=>' . $order->address2);
			self::GDSLOG('city', '=>' . $order->city);
			self::GDSLOG('province', '=>' . $order->province);
			self::GDSLOG('province_code', '=>' . $order->province_code);
			self::GDSLOG('zip', '=>' . $order->zip);
		}
		return redirect('orders/' . $request->id_shopify);
	}

	public function cancelRequest(Order $order)
	{

		$res = MOrder::changeStatus($order->magento_quote_id);
		if ($res) {
			$order->fulfillment_status = 11; //canceled Request
			$order->user_id_canceled = Auth::user()->id;
			$order->canceled_at = date('Y-m-d H:i:s');
			$order->save();
			$this->updateLimitOrderWhenCanceling($order);
			self::GDSLOG('Cancel Order', 'Cancel Request => ' .  $order->id);
		}
		return redirect('orders/');
	}

	public function requestRefund(Request $request)
	{
		$order_id = $request->get('order_id');
		$request_amount = $request->get('request_amount');
		$refund_reason = $request->get('refund_reason');
		$refund_notes = $request->get('refund_notes');
		$shipping_cost = $request->get('shipping_cost');

		// get quantities for each items
		$qtys = $request->get('qtys');
		
		// find order
		$order = Order::where('id', $order_id)->first();

		// get available total amount
		$total_amount = number_format(($order->total + $order->shipping_price), 2);

		if($order->financial_status != 2) {
			return back()->with('error', 'Bad request');
		} else {
			if($request_amount < 0 || $request_amount > $total_amount){
				return back()->with('error', 'You can request the amount at most US $' . $total_amount);
			}

			// process request refund
			$orderRefund = new OrderRefund();
			$orderRefund->order_id = $order->id;
			$orderRefund->request_amount = $request_amount;
			$orderRefund->request_shipping_cost = $shipping_cost;

			if($refund_reason == 1) {
				$orderRefund->refund_reason = 'Requested by Merchant';
			} else if ($refund_reason == 2) {
				$orderRefund->refund_reason = 'Exceeds Lead-time';
			} else {
				$orderRefund->refund_reason = 'Other';
			}

			$orderRefund->refund_notes = isset($refund_notes) ? $refund_notes : '';
			$orderRefund->save();

			//change order status to Request Refund 
			$order->financial_status = 14;
			$order->save();


			// save refund order details
			foreach($qtys as $sku => $qty){
				// if($qty != "0"){
					$ord = new OrderRefundDetail();
					$ord->order_id = $order_id;
					$ord->sku = $sku;
					$ord->quantity = $qty;
					$ord->save();
				// }
			}

			// updates total request refund counts
			$refundReqCount = Order::where('financial_status', 14)->count();

			view()->share('refundReqCount', $refundReqCount);
			self::GDSLOG('Request Refund', 'Request Refund => '.$order->id);

			return redirect('orders/')->with('success', 'Sent Refund request successfully');
		}
	}

	public function cancel(Order $order)
	{
		$order->fulfillment_status = 9; //canceled
		$order->user_id_canceled = Auth::user()->id;
		$order->canceled_at = date('Y-m-d H:i:s');
		$order->save();
		self::GDSLOG('Cancel Order', 'Cancel Order => ' . $order->id);
		$this->updateLimitOrderWhenCanceling($order);
		return redirect('orders/');
	}

	public static function GDSLOG($action, $message)
	{
		$log = date('Y-m-d H:i:s') . '| Merchant ' . Auth::user()->id . '| Shop ' . Auth::user()->name . ' | ' .  $action . ' | ' . $message;
		Storage::disk('local')->append("gds/" . date('Y-m') . '.txt', $log);
	}

	//Update within the current period
	public function updateLimitOrderWhenCanceling($order)
	{
		$current_period = MonthlyRecurringPlan::where('current', 1)->where('merchant_id', Auth::user()->id)->first();
		if ($current_period != null) {
			$current_period_orders = MonthlyRecurringPlanOrders::where('period_id', $current_period->id)->orderBy('order_id', 'asc')
				->take(env('LIMIT_ORDERS', 1))->get();
			$found = $current_period_orders->first(function ($value, $key) use ($order) {
				return $value->order_id == $order->id;
			});
			if ($found != null) {
				$found->delete();
			}
		}
	}

	public function isOutOfLimit($merchantId)
	{
		$current_period = MonthlyRecurringPlan::where('current', 1)->where('merchant_id', $merchantId)->first();
		if ($current_period != null) {
			$count = MonthlyRecurringPlanOrders::where('period_id', $current_period->id)
				->orderBy('order_id', 'asc')->count();
			if ($count >= env('LIMIT_ORDERS', 1)) {
				return true;
			}
		}
		return false;
	}

	public function test(Request $request)
	{
		$order_id = $request->get('order_id');
		$refund_amount = $request->get('refund_amount');
		$refund_reason = $request->get('refund_reason');
		self::GDSLOG('test', 'Request => ' . $request->get('order_id') . '    ' . $refund_amount . '    ' . $refund_reason);
	}
}
