<?php

namespace App\Http\Controllers;

use App\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\DashboardSteps;
use App\ImportList;
use App\Libraries\OrderStatus;
use App\Libraries\Shopify\ShopifyAdminApi;
use App\MonthlyRecurringPlan;
use App\MonthlyRecurringPlanOrders;
use App\MyProducts;
use App\Settings;
use App\Order;
use App\User;
use App\Products;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AjaxController extends Controller
{

    public function index(Request $parameters)
    {

        if ($parameters['action'] == 'add_check') {

            if ($row = DashboardSteps::find($parameters['id_user'])) {
                if ($parameters['step'] == 1) {
                    $row->step1 = $parameters['value'];
                }
                if ($parameters['step'] == 2) {
                    $row->step2 = $parameters['value'];
                }
                if ($parameters['step'] == 3) {
                    $row->step3 = $parameters['value'];
                }
                if ($parameters['step'] == 4) {
                    $row->step4 = $parameters['value'];
                }
                if ($parameters['step'] == 5) {
                    $row->step5 = $parameters['value'];
                }
                if ($parameters['step'] == 6) {
                    $row->step6 = $parameters['value'];
                }
                $row->save();
            } else {
                $row = new DashboardSteps;
                $row->id = $parameters['id_user'];
                $row->step1 = 0;
                $row->step2 = 0;
                $row->step3 = 0;
                $row->step4 = 0;
                $row->step5 = 0;
                $row->step6 = 0;
                $row->save();

                $row = DashboardSteps::find($parameters['id_user']);
                if ($parameters['step'] == 1) {
                    $row->step1 = $parameters['value'];
                }
                if ($parameters['step'] == 2) {
                    $row->step2 = $parameters['value'];
                }
                if ($parameters['step'] == 3) {
                    $row->step3 = $parameters['value'];
                }
                if ($parameters['step'] == 4) {
                    $row->step4 = $parameters['value'];
                }
                if ($parameters['step'] == 5) {
                    $row->step5 = $parameters['value'];
                }
                if ($parameters['step'] == 6) {
                    $row->step6 = $parameters['value'];
                }
                $row->save();
            }

            echo json_encode(1);
        }

        if ($parameters['action'] == 'add_import_list') {
            $product = Products::where('sku', $parameters['sku'])->first();
            if ($product != null) {
                $import_product = ImportList::where('id_customer', Auth::User()->id)
                    ->where('id_product', $product->id)->first();
                if ($import_product == null) {
                    $row = new ImportList;
                    $row->id_customer = Auth::user()->id;
                    $row->id_product = $product->id;
                    $row->save();
                }
                return json_encode([
                    'result' => true,
                    'sku' => $parameters['sku']
                ]);
            } else {
                return json_encode([
                    'result' => false
                ]);
            }
        }

        if ($parameters['action'] == 'delete_import_list') {

            $this->authorize('plan_delete-product-import-list');
            $row = ImportList::whereIn('id', $parameters['id_import_list']);
            $result = false;
            if (count($row->get())) {
                $result = true;
            }
            $row->delete();

            return json_encode(['result' => $result]);
        }

        if ($parameters['action'] == 'update_notes') {
            $row = Order::find($parameters['id_order']);
            $row->notes = $parameters['notes'];
            $row->save();
            return json_encode(['notes' => $row->notes]);
        }

        if ($parameters['action'] == 'update-user') {
            $user = User::where('id', '!=', Auth::user()->id)
                ->where('email', $parameters['email'])->first();
            if ($user == null) {
                User::find(Auth::user()->id)->update([
                    'name' => $parameters['name'],
                    'email' => $parameters['email'],
                    'password' => Hash::make($parameters['password'])
                ]);
                return json_encode(['result' => true]);
            } else {
                return json_encode(['result' => false]);
            }
        }

        if ($parameters['action'] == 'create-user') {
            $user = User::where('email', $parameters['email'])->first();
            if ($user == null) {
                $row = new User;
                $row->name = $parameters['name'];
                $row->email = $parameters['email'];
                $row->password = Hash::make($parameters['password']);
                $row->role = 'admin';
                $row->save();
                return json_encode($row);
            } else {
                return json_encode(['result' => false]);
            }
        }

        if ($parameters['action'] == 'admin-users') {
            $page_number = $parameters['page_number'];
            $page_size = $parameters['page_size'];
            $users = User::where('role', 'admin')->where('id', '!=', Auth::user()->id);
            if ($parameters['name'] != '') {
                $users = $users->where('name', 'like', '%' . $parameters['name'] . '%');
            }
            if ($parameters['email'] != '') {
                $users = $users->where('email', 'like', '%' . $parameters['email'] . '%');
            }
            if ($parameters['active'] != '') {
                $users = $users->where('active', $parameters['active']);
            }
            $total_count = $users->count();
            $users = $users->orderBy('users.id', 'asc')
                ->skip(($page_number - 1) * $page_size)
                ->take($page_size)->get();
            return json_encode([
                'users' => $users,
                'total_count' => $total_count,
                'page_size' => $page_size,
                'page_number' => $page_number
            ]);
        }

        if ($parameters['action'] == 'admin-user-name') {
            $names = DB::table('users')
                ->where('role', 'admin')
                ->where('name', 'like', '%' . $parameters['name'] . '%')
                ->orderBy('name')->pluck('name');
            return json_encode(['names' => $names]);
        }

        if ($parameters['action'] == 'admin-user-email') {
            $emails = DB::table('users')
                ->where('role', 'admin')
                ->where('email', 'like', '%' . $parameters['email'] . '%')
                ->orderBy('email')->pluck('email');
            return json_encode(['emails' => $emails]);
        }

        if ($parameters['action'] == 'admin-change-password') {
            $old_password = json_decode($parameters['old_password']);
            $new_password = json_decode($parameters['new_password']);
            $password = User::find(Auth::user()->id)->password;
            if (Hash::check($old_password, $password)) {
                User::find(Auth::user()->id)->update(['password' => Hash::make($new_password)]);
                return json_encode(['result' => true]);
            } else {
                return json_encode(['result' => false]);
            }
        }

        if ($parameters['action'] == 'my-products') {
            $page_number = $parameters['page_number'];
            $page_size = $parameters['page_size'];
            $prods = Products::select('products.*', 'my_products.id_imp_product as id_my_product', 'my_products.id_shopify', 'my_products.id as id_my_products', 'my_products.profit')
                ->join('import_list', 'import_list.id_product', '=', 'products.id')
                ->join('my_products', 'my_products.id_imp_product', '=', 'import_list.id')
                ->where('my_products.id_customer', Auth::user()->id)
                ->whereNull('my_products.deleted_at');
            $total_count = $prods->count();
            if (ceil($total_count / $page_size) < $page_number) {
                $page_number = ceil($total_count / $page_size);
            }
            $prods = $prods->orderBy('my_products.id', 'desc')->skip(($page_number - 1) * $page_size)->take($page_size)->get();
            $search = new SearchController;
            foreach ($prods as $product) {
                $product['brand'] = $search->getAttributeByCode($product, 'brand');
                if ($product->images != null && count(json_decode($product->images)) > 0) {
                    if (json_decode($product['images'])[0]->file == '') {
                        $product['image_url_75'] = '/img/default_image_75.png';
                        $product['image_url_285'] = '/img/default_image_285.png';
                    } else {
                        $product->image_url_75 = env('URL_MAGENTO_IMAGES') . '/dc09e1c71e492175f875827bcbf6a37c' . json_decode($product->images)[0]->file;
                        $product->image_url_285 = env('URL_MAGENTO_IMAGES') . '/e793809b0880f758cc547e70c93ae203' . json_decode($product->images)[0]->file;
                    }
                } else {
                    $product->image_url_75 = '/img/default_image_75.png';
                    $product->image_url_285 = '/img/default_image_285.png';
                }
            }
            return json_encode([
                'prods' => $prods,
                'total_count' => $total_count,
                'page_size' => $page_size,
                'page_number' => $page_number
            ]);
        }

        if ($parameters['action'] == 'import-list') {
            $this->authorize('view-merchant-import-list');
            $page_number = $parameters['page_number'];
            $page_size = $parameters['page_size'];
            $prods = Products::select('products.*', 'import_list.id as id_import_list')
                ->join('import_list', 'import_list.id_product', '=', 'products.id')
                ->whereNotIn('import_list.id', MyProducts::where('id_customer', Auth::user()->id)->pluck('id_imp_product'))
                ->where('import_list.id_customer', Auth::user()->id)
                ->orderBy('import_list.id', 'desc');

            $total_count = $prods->count();
            if (ceil($total_count / $page_size) < $page_number) {
                $page_number = ceil($total_count / $page_size);
            }
            $prods = $prods->skip(($page_number - 1) * $page_size)->take($page_size)->get();
            foreach ($prods as $product) {
                if ($product['images'] != null && count(json_decode($product['images'])) > 0) {
                    if (json_decode($product['images'])[0]->file == '') {
                        $product['image_url'] = '/img/default_image_285.png';
                        $product['delete_image_url'] = '/img/default_image_75.png';
                    } else {
                        $product['image_url'] = env('URL_MAGENTO_IMAGES') . '/e793809b0880f758cc547e70c93ae203' . json_decode($product['images'])[0]->file;
                        $product['delete_image_url'] = env('URL_MAGENTO_IMAGES') . '/dc09e1c71e492175f875827bcbf6a37c' . json_decode($product['images'])[0]->file;
                    }
                } else {
                    $product['image_url'] = '/img/default_image_285.png';
                    $product['delete_image_url'] = '/img/default_image_75.png';
                }

                $search = new SearchController;
                $images = [];
                foreach (json_decode($product['images']) as $image) {
                    if ($image->file) {
                        $images[] = env('URL_MAGENTO_IMAGES') . '/3a98496dd7cb0c8b28c4c254a98f915a' . $image->file;
                    }
                }
                $product['description'] = $search->getAttributeByCode($product, 'description');
                $product['size'] = $search->getAttributeByCode($product, 'size');
                $product['images'] = $images;
                $product['ship_height'] = round($search->getAttributeByCode($product, 'ship_height'), 2);
                $product['ship_width'] = round($search->getAttributeByCode($product, 'ship_width'), 2);
                $product['ship_length'] = round($search->getAttributeByCode($product, 'ship_length'), 2);
                $tags = '';
                if ($product['categories']) {
                    if (substr($product['categories'], 0, 1) != '[') {
                        foreach (explode('>', $product['categories']) as $category) {
                            if (trim($category) != '') {
                                $tags .= trim($category).', ';
                            }
                        }
                    }
                }
                $tags .= explode(':', $product['name'])[0].', ';
                if (count(explode(':', $product['name'])) > 1) {
                    $elements = explode(':', $product['name'])[1];
                    foreach (explode(' ', $elements) as $element) {
                        $element = trim($element);
                        $element = str_replace(',', '', $element);
                        $element = str_replace('+', '', $element);
                        $element = str_replace('(', '', $element);
                        $element = str_replace(')', '', $element);
                        $element = str_replace('-', '', $element);
                        $element = str_replace('.', '', $element);
                        $element = str_replace('#', '', $element);
                        $element = str_replace('&', '', $element);
                        if (strlen($element) > 2 && !floatval($element)) {
                            $tags .= $element.', ' ;
                        }
                    }
                }
                $tags = substr($tags, 0, -2);
                $product['tags'] = $tags;
                $type = '';
                $collection = '';
                if ($product['categories'] != null) {
                    if (substr($product['categories'], 0, 1) != '[') {
                        $elements = explode('>', $product['categories']);
                        $type = $elements[count($elements) - 1];
                        $type = trim(str_replace(',', ' &', $type));
                        if (count($elements) > 1) {
                            $collection = $elements[count($elements) - 2];
                        } else {
                            $collection = $elements[count($elements) - 1];
                        }
                        $collection = trim(str_replace(',', ' &', $collection));
                    }
                }
                $product['type'] = $type;
                $types = DB::table('shopify_collections')
                    ->where('user_id', Auth::User()->id)
                    ->where('title', 'like', '%'.$type.'%')
                    ->orderBy('title')->pluck('title');
                $product['types'] = $types;
                $product['collection'] = $collection;
                $collections = DB::table('user_collections_tags_types')
                    ->where([['user_id', Auth::User()->id], ['type', 'X']])
                    ->where('value', 'like', '%'.$product['collection'].'%')
                    ->orderBy('value')->pluck('value');
                $product['collections'] = $collections;
            }
            $settings = Settings::where('id_merchant', Auth::user()->id)->first();
            if ($settings == null) {
                $settings = new Settings();
                $settings->set8 = 0;
            }
            return json_encode([
                'improds' => [
                    'products' => $prods,
                    'profit' => $settings->set8,
                    'plan' => Auth::User()->plan,
                    'shopify_url' => Auth::User()->shopify_url
                ],
                'total_count' => $total_count,
                'page_size' => $page_size,
                'page_number' => $page_number
            ]);
        }

        if ($parameters['action'] == 'import-list-undo') {
            $row = new ImportList;
            $row->id_customer = Auth::user()->id;
            $row->id_product = $parameters['id'];
            $row->save();
            $product = Products::select('products.*', 'import_list.id as id_import_list')
                ->join('import_list', 'import_list.id_product', '=', 'products.id')
                ->where('import_list.id_customer', Auth::user()->id)
                ->where('products.id', $parameters['id'])->first();
            if ($product['images'] != null && count(json_decode($product['images'])) > 0) {
                $product['image_url'] = env('URL_MAGENTO_IMAGES') . '/e793809b0880f758cc547e70c93ae203' . json_decode($product['images'])[0]->file;
                $product['delete_image_url'] = env('URL_MAGENTO_IMAGES') . '/dc09e1c71e492175f875827bcbf6a37c' . json_decode($product['images'])[0]->file;
            } else {
                $product['image_url'] = '/img/default_image_285.png';
                $product['delete_image_url'] = '/img/default_image_75.png';
            }

            $search = new SearchController;
            $images = [];
            foreach (json_decode($product['images']) as $image) {
                if ($image->file) {
                    $images[] = env('URL_MAGENTO_IMAGES') . '/3a98496dd7cb0c8b28c4c254a98f915a' . $image->file;
                }
            }
            $product['description'] = $search->getAttributeByCode($product, 'description');
            $product['size'] = $search->getAttributeByCode($product, 'size');
            $product['images'] = $images;
            $product['ship_height'] = round($search->getAttributeByCode($product, 'ship_height'), 2);
            $product['ship_width'] = round($search->getAttributeByCode($product, 'ship_width'), 2);
            $product['ship_length'] = round($search->getAttributeByCode($product, 'ship_length'), 2);
            $tags = '';
            if ($product['categories']) {
                if (substr($product['categories'], 0, 1) != '[') {
                    foreach (explode('>', $product['categories']) as $category) {
                        if (trim($category) != '') {
                            $tags .= trim($category).', ';
                        }
                    }
                }
            }
            $tags .= explode(':', $product['name'])[0].', ';
            if (count(explode(':', $product['name'])) > 1) {
                $elements = explode(':', $product['name'])[1];
                foreach (explode(' ', $elements) as $element) {
                    $element = trim($element);
                    $element = str_replace(',', '', $element);
                    $element = str_replace('+', '', $element);
                    $element = str_replace('(', '', $element);
                    $element = str_replace(')', '', $element);
                    $element = str_replace('-', '', $element);
                    $element = str_replace('.', '', $element);
                    $element = str_replace('#', '', $element);
                    $element = str_replace('&', '', $element);
                    if (strlen($element) > 2 && !floatval($element)) {
                        $tags .= $element.', ' ;
                    }
                }
            }
            $tags = substr($tags, 0, -2);
            $product['tags'] = $tags;
            $collection = '';
            if ($product['categories'] != null) {
                if (substr($product['categories'], 0, 1) != '[') {
                    $elements = explode('>', $product['categories']);
                    $collection = $elements[count($elements) - 1];
                    $collection = trim(str_replace(',', ' &', $collection));
                }
            }
            $product['collection'] = $collection;
            $collections = DB::table('shopify_collections')
                ->where('user_id', Auth::User()->id)
                ->where('title', 'like', '%'.$collection.'%')
                ->orderBy('title')->pluck('title');
            $product['collections'] = $collections;
            $product['type'] = trim($product['brand']);
            $types = DB::table('user_collections_tags_types')
                ->where([['user_id', Auth::User()->id], ['type', 'X']])
                ->where('value', 'like', '%'.$product['type'].'%')
                ->orderBy('value')->pluck('value');
            $product['types'] = $types;
            $settings = Settings::where('id_merchant', Auth::user()->id)->first();
            if ($settings == null) {
                $product['profit'] = 0;
            } else {
                $product['profit'] = $settings->set8;
            }
            return json_encode($product);
        }

        if ($parameters['action'] == 'migration-count') {
            $count = ShopifyAdminApi::countProducts(Auth::user());
            if (isset($count['body']['errors'])) {
                return json_encode([
                    'error' => 'A problem has occured while bringing the products from your store.<br>Please log into this app again through Shopify. <br> <a href="https://greendropship.com/contact-us/" target="_blank">Contact our support team</a> if you have any questions.'
                ]);
            }
            return json_encode([
                'action' => 'migration',
                'Total_count' => $count['body']['count'],
                'index' => 0,
                'location_id' => 0,
                'count' => 0
            ]);
        }

        if ($parameters['action'] == 'migration') {
            $rows = [];
            $products = (object) [];
            if ($parameters['location_id'] == 0) {
                $res = self::getLocationId($parameters['index']);
                if ($res) {
                    $products = $res['products'];
                    $parameters['location_id'] = $res['location_id'];
                    $parameters['index'] = $res['index'];
               } else {
                    return json_encode([
                        'mig_products' => [],
                        'total_count' => 0,
                        'count' => $parameters['count'],
                        'page_number' => 1,
                        'page_size' => 10
                    ]);
                }
            } else {
                $products = ShopifyAdminApi::getProducts(Auth::User(), $parameters['index']);
                $parameters['index'] = $products['body']['products'][count($products['body']['products']) - 1]['id'];
            }
            foreach ($products['body']['products'] as $product) {
                if (substr($product['variants'][0]['sku'], 0, 2) == 'KH') {
                    $rows[] = [
                        'sku' => $product['variants'][0]['sku'],
                        'price' => $product['variants'][0]['price'],
                        'id_shopify' => $product['id'],
                        'id_variant_shopify' => $product['variants'][0]['id'],
                        'inventory_item_id_shopify' => $product['variants'][0]['inventory_item_id'],
                        'location_id_shopify' => $parameters->location_id,
                        'user_id' => Auth::user()->id,
                        'payload' => json_encode([
                            'name' => $product['title'],
                            'image_url' => $product['image'] != null ? $product['image']['src'] : '/img/default_image_75.png'
                        ]),
                        'type' => 'migration'
                    ];
                }
            }
            DB::table('temp_migrate_products')->insert($rows);
            $parameters['count'] = $parameters['count'] + count($products['body']['products']);
            if ($parameters['count'] * 1 == $parameters['Total_count'] * 1) {
                $mig_products = DB::table('temp_migrate_products')
                    ->select('temp_migrate_products.*', 'products.price as cost')
                    ->leftJoin('products', 'temp_migrate_products.sku', '=', 'products.sku')
                    ->where('user_id', Auth::user()->id)->orderByDesc('id_shopify');
                $total_count = $mig_products->count();
                $mig_products = $mig_products->skip(0)->take(10)->get();
                return json_encode([
                    'mig_products' => $mig_products,
                    'total_count' => $total_count,
                    'Total_count' => $parameters['Total_count'],
                    'count' => $parameters['count'],
                    'page_number' => 1,
                    'page_size' => 10
                ]);
            } else {
                return json_encode([
                    'action' => 'migration',
                    'index' => $parameters['index'],
                    'location_id' => $parameters['location_id'],
                    'Total_count' => $parameters['Total_count'],
                    'count' => $parameters['count'],
                ]);
            }
        }

        if ($parameters['action'] == 'migrate-products') {
            $page_number = $parameters['page_number'];
            $page_size = $parameters['page_size'];
            $mig_products = DB::table('temp_migrate_products')
                ->where('user_id', Auth::user()->id)->orderByDesc('id_shopify');
            $total_count = $mig_products->count();
            if (ceil($total_count / $page_size) < $page_number) {
                $page_number = ceil($total_count / $page_size);
            }
            $mig_product_skus = $mig_products->skip(($page_number - 1) * $page_size)->take($page_size)->pluck('sku');
            $mig_products = DB::table('temp_migrate_products')
                ->select('temp_migrate_products.*', 'products.price as cost')
                ->leftJoin('products', 'temp_migrate_products.sku', '=', 'products.sku')
                ->where('user_id', Auth::user()->id)->whereIn('temp_migrate_products.sku', $mig_product_skus)->get();
            return json_encode([
                'mig_products' => $mig_products,
                'page_size' => $page_size,
                'page_number' => $page_number,
                'total_count' => $total_count
            ]);
        }

        if ($parameters['action'] == 'set-default-profit') {
            $mig_skus = DB::table('temp_migrate_products')
                ->where('user_id', Auth::User()->id)
                ->whereIn('id_shopify', json_decode($parameters['shopify_ids']))
                ->where('type', 'migration')->pluck('sku');
            $setting = Settings::where('id_merchant', Auth::user()->id)->first();
            if ($setting == null) {
                $setting->set8 = 0;
            }
            foreach ($mig_skus as $sku) {
                $cost = DB::table('products')->where('sku', $sku)->pluck('price')->first();
                DB::table('temp_migrate_products')
                    ->where('user_id', Auth::User()->id)
                    ->where('sku', $sku)
                    ->update(['price' => $cost * (100 + $setting->set8) / 100]);
            }
            return json_encode(['result' => true]);
        }

        if ($parameters['action'] == 'change-profit') {
            $cost = DB::table('products')->where('sku', $parameters['sku'])->pluck('price')->first();
            DB::table('temp_migrate_products')->where('user_id', Auth::User()->id)
                ->where('sku', $parameters['sku'])
                ->update(['price' => $cost * (100 + $parameters['profit']) / 100]);
            return json_encode(['result' => true]);
        }

        if ($parameters['action'] == 'product_collection') {
            $collections = DB::table('shopify_collections')
                ->where('user_id', Auth::User()->id)
                ->where('title', 'like', '%' . $parameters['collection'] . '%')
                ->orderBy('title')->pluck('title');
            return json_encode(['collections' => $collections]);
        }

        if ($parameters['action'] == 'product_type') {
            $types = DB::table('user_collections_tags_types')
                ->where([['user_id', Auth::User()->id], ['type', 'X']])
                ->where('value', 'like', '%' . $parameters['type'] . '%')
                ->orderBy('value')->pluck('value');
            return json_encode(['types' => $types]);
        }

        if ($parameters['action'] == 'product_tag') {
            $tags = DB::table('user_collections_tags_types')
                ->where([['user_id', Auth::User()->id], ['type', 'T']])
                ->where('value', 'like', '%' . $parameters['tag'] . '%')
                ->orderBy('value')->pluck('value');
            return json_encode(['tags' => $tags]);
        }

        if ($parameters['action'] == 'admin-order-number') {
            $magento_numbers = DB::table('orders')->distinct()
                ->where('magento_order_id', 'like', '%' . $parameters['number'] . '%')
                ->orderBy('magento_order_id')
                ->pluck('magento_order_id')->toArray();
            $shopify_numbers = DB::table('orders')->distinct()
                ->where('id_shopify', 'like', '%' . $parameters['number'] . '%')
                ->orderBy('id_shopify')
                ->pluck('id_shopify')->toArray();
            $customer_numbers = DB::table('orders')->distinct()
                ->where('order_number_shopify', 'like', '%' . $parameters['number'] . '%')
                ->orderBy('order_number_shopify')
                ->pluck('order_number_shopify')->toArray();
            $numbers = array_merge_recursive($magento_numbers, $shopify_numbers, $customer_numbers);
            return json_encode(['numbers' => $numbers]);
        }

        if ($parameters['action'] == 'admin-order-merchant') {
            $names = DB::table('users')
                ->where('name', 'like', '%' . $parameters['name'] . '%')
                ->orderBy('name')
                ->pluck('name');
            return json_encode(['names' => $names]);
        }

        if ($parameters['action'] == 'admin-orders') {
            $page_number = $parameters['page_number'];
            $page_size = $parameters['page_size'];
            $order_list = Order::select('orders.*', 'st1.name as status1', 'st1.color as color1', 'st2.name as status2', 'st2.color as color2', 'us.name as merchant_name')
                ->join('order_shipping_address as osa', 'orders.id', 'osa.id_order')
                ->join('status as st1', 'st1.id', 'orders.financial_status')
                ->join('status as st2', 'st2.id', 'orders.fulfillment_status')
                ->join('users as us', 'us.id', 'orders.id_customer');
            if ($parameters['order_number'] != '') {
                $order_list = $order_list->where('magento_order_id', 'like', '%' . $parameters['order_number'] . '%')
                    ->orWhere('id_shopify', 'like', '%' . $parameters['order_number'] . '%')
                    ->orWhere('order_number_shopify', 'like', '%' . $parameters['order_number'] . '%');
            }

            if ($parameters['from'] != '') {
                $order_list = $order_list->whereDate('orders.created_at', '>=', $parameters['from']);
            }

            if ($parameters['to'] != '') {
                $order_list = $order_list->whereDate('orders.created_at', '<=', $parameters['to']);
            }

            if ($parameters['payment_status'] > 0) {
                $order_list = $order_list->where('orders.financial_status', $parameters['payment_status']);
            }

            if ($parameters['order_state'] > 0) {
                $order_list = $order_list->where('orders.fulfillment_status', $parameters['order_state']);
            }

            if ($parameters['merchant_name'] != '') {
                $order_list = $order_list->where('us.name', 'like', '%' . $parameters['merchant_name'] . '%');
            }

            if ($parameters['id_customer'] != '') {
                $order_list = $order_list->where('orders.id_customer', $parameters['id_customer']);
            }
            $total_count = $order_list->count();
            $order_list = $order_list->orderBy('orders.updated_at', 'desc')->skip(($page_number - 1) * $page_size)->take($page_size)->get();
            $timezone = count($order_list) ? $order_list[0]->created_at->tz() : '';
            return json_encode([
                'order_list' => [
                    'order_list' => $order_list,
                    'timezone' => $timezone
                ],
                'page_number' => $page_number,
                'page_size' => $page_size,
                'total_count' => $total_count,
            ]);
        }

        if ($parameters['action'] == 'change-user-status') {
            DB::table('users')
                ->where('id', $parameters['user_id'])
                ->update(['active' => $parameters['active']]);
            return json_encode(['result' => true]);
        }

        if ($parameters['action'] == 'admin-merchants') {
            $page_number = $parameters['page_number'];
            $page_size = $parameters['page_size'];
            $merchants_list = User::select('users.*')->where('role', 'merchant');
            if ($parameters['name'] != '') {
                $merchants_list = $merchants_list->where('name', 'like', '%' . $parameters['name'] . '%');
            }

            if ($parameters['email'] != '') {
                $merchants_list = $merchants_list->where('email', 'like', '%' . $parameters['email'] . '%');
            }

            if ($parameters['url'] != '') {
                $merchants_list = $merchants_list->where('shopify_url', 'like', '%' . $parameters['url'] . '%');
            }

            if ($parameters['plan'] != '') {
                if ($parameters['plan'] == '0') {
                    $merchants_list = $merchants_list->whereNull('plan');
                } else {
                    $merchants_list = $merchants_list->where('plan', $parameters['plan']);
                }
            }

            if ($parameters['active'] != '') {
                $merchants_list = $merchants_list->where('active', $parameters['active']);
            }
            $total_count = $merchants_list->count();
            $merchants_list = $merchants_list->skip(($page_number - 1) * $page_size)->take($page_size)->get();
            foreach ($merchants_list as $merchant) {
                $order_count = Order::select('*')->where('id_customer', $merchant->id)->count();
                $merchant->order_count = $order_count;
            }
            return json_encode([
                'merchants' => $merchants_list,
                'page_number' => $page_number,
                'page_size' => $page_size,
                'total_count' => $total_count
            ]);
        }

        if ($parameters['action'] == 'admin-merchant-name') {
            $names = DB::table('users')
                ->where('role', 'merchant')
                ->where('name', 'like', '%' . $parameters['name'] . '%')
                ->orderBy('name')->pluck('name');
            return json_encode(['names' => $names]);
        }

        if ($parameters['action'] == 'admin-merchant-email') {
            $emails = DB::table('users')
                ->where('role', 'merchant')
                ->where('email', 'like', '%' . $parameters['email'] . '%')
                ->orderBy('email')->pluck('email');
            return json_encode(['emails' => $emails]);
        }

        if ($parameters['action'] == 'admin-merchant-url') {
            $urls = DB::table('users')
                ->where('role', 'merchant')
                ->where('shopify_url', 'like', '%' . $parameters['shopify_url'] . '%')
                ->orderBy('shopify_url')->pluck('shopify_url');
            return json_encode(['urls' => $urls]);
        }

        if ($parameters['action'] == 'my-orders') {
            $page_number = $parameters['page_number'];
            $page_size = $parameters['page_size'];
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

            $total_period_orders = Order::where('id_customer', Auth::user()->id);

            $is_notification = false;
            if ($parameters['notifications'] != '' && $parameters['notifications']) {
                $is_notification = true;
                $order_list = $order_list->where('financial_status', OrderStatus::Outstanding)
                    ->where('fulfillment_status', OrderStatus::NewOrder);
                $total_period_orders = $total_period_orders->where('financial_status', OrderStatus::Outstanding)
                    ->where('fulfillment_status', OrderStatus::NewOrder);
            }

            $order_count = $order_list->count();

            $current_period = MonthlyRecurringPlan::where('current', 1)->where('merchant_id', Auth::user()->id)->first();

            if ($parameters['from'] != '' && $parameters['to'] != '') {
                $total_period_orders = $total_period_orders->whereDate('created_at', '>=', $parameters['from'])
                ->whereDate('created_at', '<=', $parameters['to']);
            } else {
                if ($current_period != null) {
                    $total_period_orders = $total_period_orders->whereDate('created_at', '>=', $current_period->start_date)
                        ->whereDate('created_at', '<=', $current_period->end_date);
                }
            }

            if ($parameters['order_number'] != '' && $parameters['order_number'] > 0) {
                $order_list = $order_list->where('order_number_shopify', '#' . $parameters['order_number']);
                $total_period_orders = $total_period_orders->where('order_number_shopify', '#' . $parameters['order_number']);
            } else {
                if ($parameters['from'] != '' && $parameters['to'] != '') {
                    $order_list = $order_list->whereDate('created_at', '>=', $parameters['from'])
                        ->whereDate('created_at', '<=', $parameters['to']);
                    $total_period_orders = $total_period_orders->whereDate('created_at', '>=', $parameters['from'])
                        ->whereDate('created_at', '<=', $parameters['to']);
                }
            }

            $basic_period = '';
            if (Auth::user()->plan == 'basic' && $current_period != null) {
                $basic_period = $current_period->start_date . ' - ' . $current_period->end_date;
            }

            if ($parameters['payment_status'] > 0) {
                $order_list = $order_list->where('orders.financial_status', $parameters['payment_status']);
                $total_period_orders = $total_period_orders->where('orders.financial_status', $parameters['payment_status']);
            }

            if ($parameters['order_state'] > 0) {
                $order_list = $order_list->where('orders.fulfillment_status', $parameters['order_state']);
                $total_period_orders = $total_period_orders->where('orders.fulfillment_status', $parameters['order_state']);
            }

            $notifications = Order::join('order_shipping_address as osa', 'orders.id', 'osa.id_order')
                ->join('status as st1', 'st1.id', 'orders.financial_status')
                ->join('status as st2', 'st2.id', 'orders.fulfillment_status')
                ->where('financial_status', OrderStatus::Outstanding)
                ->where('fulfillment_status', OrderStatus::NewOrder)
                ->where('orders.id_customer', Auth::user()->id)
                ->count();

            $total_period_orders = $total_period_orders->count();
            $total_count = $order_list->count();

            return json_encode([
                'my_orders' => [
                    'orders' => $order_list->orderBy('orders.id', 'desc')->skip(($page_number - 1) * $page_size)->take($page_size)->get(),
                    'notifications' => $notifications,
                    'from' => $parameters['from'],
                    'to' => $parameters['to'],
                    'basic_period' => $basic_period,
                    'total_period_orders' => $total_period_orders,
                    'limit_orders' => env('LIMIT_ORDERS'),
                    'total_count' => $order_count,
                    'is_notification' => $is_notification
                ],
                'total_count' => $total_count,
                'page_size' => $page_size,
                'page_number' => $page_number,
            ]);
        }

        if ($parameters['action'] == 'import-products') {
            $rows = [];
            $skus = [];
            $nonskus = [];
            foreach (json_decode($parameters['skus']) as $sku) {
                $product = Products::where('sku', $sku)->first();
                if ($product != null) {
                    $import_product = ImportList::where('id_customer', Auth::User()->id)
                        ->where('id_product', $product->id)->first();
                    if ($import_product == null) {
                        $rows[] = [
                            'id_customer' => Auth::User()->id,
                            'id_product' => $product->id,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s'),
                        ];
                    }
                    $skus[] = $sku;
                } else {
                    $nonskus[] = $sku;
                } 
            }
            DB::table('import_list')->insert($rows);
            return json_encode([
                'skus' => $skus,
                'nonskus' => $nonskus,
            ]);
        }

        if ($parameters['action'] == 'introduction-new-products') {
            $new_products = Products::where('created_at', '>', date('Y-m-d', strtotime('-2 months')))
                ->where('stock', '>', 0)
                ->orderBy('created_at', 'DESC')
                ->skip($parameters['page_size'] * $parameters['page_number'])->take($parameters['page_size'])->get();
            return json_encode([
                'new_products' => $new_products,
            ]);
        }

        if ($parameters['action'] == 'introduction-all-products') {
            $new_products = Products::orderBy('sku')->where('stock', '>', 0)
                ->skip($parameters['page_size'] * $parameters['page_number'])->take($parameters['page_size'])->get();
            return json_encode([
                'new_products' => $new_products,
            ]);
        }
        
        if ($parameters['action'] == 'introduction-discount-products') {
            $discount_products = Products::select('*', DB::raw('(CASE WHEN monthly_special * 1 > price * 1 THEN (suggested_retail-monthly_special)/suggested_retail*100 ELSE (suggested_retail-price)/suggested_retail*100 END) AS discount'))
                ->where('stock', '>', 0)
                ->where('monthly_special', '>', 0)
                ->where('suggested_retail', '>', 0)
                ->where(DB::raw('(suggested_retail-monthly_special)/suggested_retail*100'), '>', 0)
                ->orderBy('discount')
                ->skip($parameters['page_size'] * $parameters['page_number'])->take($parameters['page_size'])->get();
            return json_encode([
                'discount_products' => $discount_products,
            ]);
        }

        if ($parameters['action'] == 'new-products') {
            $new_products = Products::where('created_at', '>', date('Y-m-d', strtotime('-2 months')))->where('stock', '>', 0);
            if ($parameters['search_key'] != '') {
                if (substr($parameters['search_key'], -1) == ',') {
                    $search_key = substr($parameters['search_key'], 0, -1);
                } else {
                    $search_key = $parameters['search_key'];
                }
                $search_keys = [];
                foreach (explode(',', $search_key) as $keys) {
                    foreach (explode(' ', trim($keys)) as $key) {
                        $search_keys[] = $key;
                    }
                }
                foreach ($search_keys as $search_key) {
                    $new_products = $new_products->where('name', 'like', '%'.$search_key.'%');
                }
                $new_products = $new_products->distinct();
            }
            if ($parameters['sort_key'] != '') {
                if ($parameters['sort_key'] == 'a-z') {
                    $new_products = $new_products->orderBy('name');
                } else if ($parameters['sort_key'] == 'z-a') {
                    $new_products = $new_products->orderBy('name', 'DESC');
                } else if ($parameters['sort_key'] == 'l-h') {
                    $new_products = $new_products->orderBy('price');
                } else if ($parameters['sort_key'] == 'h-l') {
                    $new_products = $new_products->orderBy('price', 'DESC');
                }
            } else {
                $new_products = $new_products->orderBy('created_at', 'DESC');
            }
            $new_products = $new_products->skip(60 * $parameters['page_number'])->take(60)->get();
            $imported_products = Products::select('products.*', 'import_list.id as id_import_list')
                ->join('import_list', 'import_list.id_product', '=', 'products.id')
                ->whereNotIn('import_list.id', MyProducts::where('id_customer', Auth::User()->id)->pluck('id_imp_product'))
                ->where('import_list.id_customer', Auth::user()->id)
                ->pluck('products.sku');
            return json_encode([
                'new_products' => $new_products,
                'imported_products' => $imported_products
            ]);
        }
        
        if ($parameters['action'] == 'discount-products') {
            $discount_products = Products::select('*', DB::raw('(CASE WHEN monthly_special * 1 > price * 1 THEN (suggested_retail-monthly_special)/suggested_retail*100 ELSE (suggested_retail-price)/suggested_retail*100 END) AS discount'))
                ->where('stock', '>', 0)
                ->where('monthly_special', '>', 0)
                ->where('suggested_retail', '>', 0)
                ->where(DB::raw('(suggested_retail-monthly_special)/suggested_retail*100'), '>', 0);
            if ($parameters['search_key'] != '') {
                $search_keys = [];
                foreach (explode(',', $parameters['search_key']) as $keys) {
                    foreach (explode(' ', trim($keys)) as $key) {
                        $search_keys[] = $key;
                    }
                }
                foreach ($search_keys as $search_key) {
                    $discount_products = $discount_products->where('name', 'like', '%'.$search_key.'%');
                }
                $discount_products = $discount_products->distinct();
            }
            if ($parameters['sort_key'] != '') {
                if ($parameters['sort_key'] == 'a-z') {
                    $discount_products = $discount_products->orderBy('name');
                } else if ($parameters['sort_key'] == 'z-a') {
                    $discount_products = $discount_products->orderBy('name', 'DESC');
                } else if ($parameters['sort_key'] == 'l-h') {
                    $discount_products = $discount_products->orderBy('monthly_special');
                } else if ($parameters['sort_key'] == 'h-l') {
                    $discount_products = $discount_products->orderBy('monthly_special', 'DESC');
                }
            } else {
                $discount_products = $discount_products->orderBy('discount');
            }
            $discount_products = $discount_products->skip(60 * $parameters['page_number'])->take(60)->get();
            $imported_products = Products::select('products.*', 'import_list.id as id_import_list')
                ->join('import_list', 'import_list.id_product', '=', 'products.id')
                ->whereNotIn('import_list.id', MyProducts::where('id_customer', Auth::User()->id)->pluck('id_imp_product'))
                ->where('import_list.id_customer', Auth::user()->id)
                ->pluck('products.sku');
            return json_encode([
                'discount_products' => $discount_products,
                'imported_products' => $imported_products
            ]);
        }
    }

    public function saveSettings(Request $request)
    {
        $request->validate([
            'set8' => 'required|integer',
        ]);

        $settings = Settings::where('id_merchant', Auth::user()->id)->first();
        if ($settings == null) {
            $settings = new Settings();
            $settings->id_merchant = Auth::user()->id;
        }
        $settings->set1 = $request->set1 == 'true' ? 1 : 0;
        $settings->set8 = $request->set8;
        $settings->inventory_threshold = $request->inventory_threshold;
        $settings->sync_inventory = $request->sync_inventory == 'true' ? 1 : 0;
        $settings->sync_price = $request->sync_price == 'true' ? 1 : 0;
        $settings->save();
        return response()->json(['res' => 'ok']);
    }

    public static function getLocationId($index)
    {
        $products = ShopifyAdminApi::getProducts(Auth::User(), $index);
        $flag = true;
        foreach ($products['body']['products'] as $product) {
            if ($product['variants'][0]['fulfillment_service'] == 'greendropship') {
                $location_id = ShopifyAdminApi::getItemLocationId(Auth::User(), $product['variants'][0]['inventory_item_id']);
                $flag = false;
                return [
                    'location_id' => $location_id['body']['inventory_levels'][0]['location_id'],
                    'products' => $products,
                    'index' => $products['body']['products'][count($products['body']['products']) - 1]['id']
                ];
            }
        }
        if ($flag) {
            if (count($products['body']['products']) == 0) {
                return false;
            } else {
                $index = $products['body']['products'][count($products['body']['products']) - 1]['id'];
                self::getLocationId($index);
            }
        }
    }
}
