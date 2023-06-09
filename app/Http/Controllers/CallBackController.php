<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\User;
use App\Libraries\Shopify\ShopifyAdminApi;

class CallBackController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Support\Renderable|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function index(Request $request)
    {
        $user = User::where('shopify_url', $request->input('shop'))->first();
        $mode = '';

        if (!$user) {
            $mode = 'per-user';
        }

        return redirect("https://" . $request->input('shop') . "/admin/oauth/authorize?client_id="
            . env('SHOPIFY_API_KEY') . "&scope=read_orders,write_orders,write_products,read_inventory,write_inventory,read_locations,read_fulfillments,write_fulfillments&redirect_uri=" . urlencode(env('APP_URL')) . "/callback&grant_options[]=" . $mode);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function callback(Request $request)
    {
        $params = $request->all(); // Retrieve all request parameters
        $hmac = $request->input('hmac'); // Retrieve HMAC request parameter
        $params = array_diff_key($params, array('hmac' => '')); // Remove hmac from params
        ksort($params); // Sort params lexographically
        $computed_hmac = hash_hmac('sha256', http_build_query($params), env('SHOPIFY_SECRET_KEY'));

        // Use hmac data to check that the response is from Shopify or not
        if (hash_equals($hmac, $computed_hmac)) {
            $result = $this->generateToken($params['code'], $params['shop']);
            $user = User::where('shopify_url', $params['shop'])->first();

            if (!$user) {
                $user = new User();
                $user->name = $result['associated_user']['first_name'] .' '. $result['associated_user']['last_name'];
                $user->email = $result['associated_user']['email'];
                $user->password = Hash::make('password');
                $user->shopify_url =  $params['shop'];
                $user->role =  'merchant';

                $user->shopify_token = $result['access_token'];
                $user->save();

                //Installing FulfillmentService in Merchant Store
                ShopifyAdminApi::createFulfillmentService($user);


                //Installing webhook to get new orders from Shopify
                ShopifyAdminApi::createWebhook($user, 'orders/create', env('APP_URL') . '/create-order-webkook');




                auth()->login($user); // Login and "remember" the given user...
            } else {
                $user->shopify_token = $result['access_token'];
                $user->save();

                auth()->login($user); // Login and "remember" the given user...


            }
            if ($user->plan == 'basic' || $user->plan == 'advanced') {
                return redirect('/import-list');
            } else {
                return redirect('/introduction');
            }
        } else {
            die('This request is NOT from Shopify!');
        }
    }

    protected function generateToken($code, $shop)
    {
        $query = [
            'client_id' => env('SHOPIFY_API_KEY'),
            'client_secret' => env('SHOPIFY_SECRET_KEY'),
            'code' => $code
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, "https://" . $shop . "/admin/oauth/access_token");
        curl_setopt($ch, CURLOPT_POST, count($query));
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($query));
        $result = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($result, true);

        return  $result;
    }
}
