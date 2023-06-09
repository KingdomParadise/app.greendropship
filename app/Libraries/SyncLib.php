<?php

namespace App\Libraries;

use App\MyProducts;
use App\User;
use App\Order;
use App\Category;
use App\Products;
use App\Libraries\Shopify\ShopifyAdminApi;
use App\Libraries\Magento\MCategory;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SyncLib
{

    public static function shopifyUpgraded()
    {
        //get users with api_status = pending
        $users_list = User::select('id')->where('api_status', 'pending')->get();
        foreach ($users_list as $ul) {
            $user = User::find($ul["id"]);
            $res = ShopifyAdminApi::getStatusRecurringCharge($user);
            //Validate plan's status
            if ($res == 'accepted' || $res == 'active') {
                $user->api_status = 'accepted';
                $user->plan = 'basic';
                $user->save();
            }
            echo '<p>id user: ' . $ul["id"] . '</p>';
        }
        return 'success';
    }

    public static function syncStock()
    {
        $t = time();
        echo ('Start: ' . date("h:i:s", $t));
        $stocksData = collect(DB::connection('mysql_magento')->select('SELECT * FROM `mg_inventory_stock_1`'))->where('is_salable', 1);
        $rows = [];
        foreach ($stocksData as $task) {
            $row['product_id']  = $task->product_id;
            $row['quantity']  = $task->quantity;
            $row['sku']  = $task->sku;
            $rows[] = implode(',', $row);
        }
        Storage::disk('local')->put('magento_stock.csv', implode("\n", $rows));
        DB::statement("TRUNCATE TABLE temp_mg_product");
        $path = str_replace("\\", "/", base_path());
        DB::connection()->getpdo()->exec(
            "LOAD DATA LOCAL INFILE '" . $path . "/storage/app/magento_stock.csv' INTO TABLE temp_mg_product
            FIELDS TERMINATED BY ','"
        );

        DB::statement(
            "UPDATE products
            INNER JOIN temp_mg_product ON products.sku = temp_mg_product.sku
            SET products.stock = temp_mg_product.quantity
            WHERE products.stock != temp_mg_product.quantity"
        );
        DB::statement(
            "UPDATE my_products
            INNER JOIN temp_mg_product ON my_products.id_product = temp_mg_product.product_id
            SET my_products.cron = 1, my_products.stock = temp_mg_product.quantity
            WHERE my_products.stock != temp_mg_product.quantity"
        );

        $t = time();
        echo ('End: ' . date("h:i:s", $t));
        return 'success';
    }

    public static function syncShopifyStock($request)
    {
        $myProducts = MyProducts::whereNotNull('inventory_item_id_shopify');
        if (\Auth::User())
            $myProducts = $myProducts->where('id_customer', \Auth::User()->id)->take(20);
        else
            $myProducts = $myProducts->take(100);
        $myProducts = $myProducts->where('cron', '1')->get();
        $updatedCount = 0;
        foreach ($myProducts as $mp) {
            try {
                $product = Products::find($mp->id_product);
                $price = $product->price * (100 + $mp->profit) / 100;
                $merchant = User::find($mp->id_customer);
                // GET LOCATION FROM SHOPIFY IF LOCATION IS NOT SET
                if (!($mp->location_id_shopify > 0)) {
                    $res = ShopifyAdminApi::getLocationIdForIvewntory($merchant, $mp->inventory_item_id_shopify);
                    $mp->location_id_shopify = $res['inventory_levels'][0]['location_id'];
                    sleep(1);
                }
                $mp->cron = 0;
                $mp->save();

                //UPDATE STOCK & COST & PRICE IN SHOPIFY STORES
                ShopifyAdminApi::updateStock($merchant, $mp);
                sleep(1);
                ShopifyAdminApi::updateCostPrice($merchant, $mp, $price, $product->price);
                $updatedCount++;
            } catch (Exception $ex) {
                echo $ex->getMessage();
            }
        }
        echo $updatedCount . " items' stock has been updated";
    }

    public static function setTrackingNumber()
    {
        $orders = Order::whereNotNull('magento_entity_id')->whereNull('tracking_code')->get();
        foreach ($orders as $order) {
            $querymg = DB::connection('mysql_magento')->select('SELECT *
                FROM `mg_sales_shipment_track` WHERE order_id = ' . $order->magento_entity_id);
            if (count($querymg)) {
                //Update tracking number in middleware DB
                $order->tracking_code = $querymg[0]->track_number;
                $order->fulfillment_status = 6;
                $order->save();

                //Update fulfillment in shopify store

                $user = User::where('id', $order->id_customer)->first();

                //Step 1.  Get shopify order to know item lines
                $shopify_order = ShopifyAdminApi::getOrderInformation($user, $order->id_shopify);

                $i = 0;
                foreach ($shopify_order['body']['order']['line_items'] as $li) {
                    //fulfillmente service validation
                    if ($li['fulfillment_service'] == 'greendropship') {
                        //Step 2.  Get shopify inventory item id
                        $iii = ShopifyAdminApi::getInventoryItemId($user, $li['variant_id']);

                        //Step 3. Get shopify item location id
                        $location = ShopifyAdminApi::getItemLocationId($user, $iii['body']['variant']['inventory_item_id']);

                        //Step 4. Post Tracking Number in shopify
                        $fulfill = ShopifyAdminApi::fulfillItem($user, $location['body']['inventory_levels'][0]['location_id'], $order->tracking_code, $li['id'], $order->id_shopify, $order->shipping_carrier_code);

                        //Step 5. Fulfilled
                        $fulfilled = ShopifyAdminApi::fulfilledOrder($user, $order->id_shopify, $fulfill['body']['fulfillment']['id']);
                    }
                    $i++;
                }
            }
        }
    }

    public static function syncWP()
    {
        //1. Get collection of records from Wordpress
        $tokens = DB::connection('mysql_wp')
            ->select('SELECT
                wp_rftpn0v78k_pmpro_membership_orders.id AS id_order,
                wp_rftpn0v78k_pmpro_membership_orders.code AS token,
                wp_rftpn0v78k_pmpro_membership_orders.user_id,
                wp_rftpn0v78k_pmpro_memberships_users.status,
                wp_rftpn0v78k_pmpro_memberships_users.enddate,
                wp_rftpn0v78k_users.display_name,
                wp_rftpn0v78k_users.user_email
                FROM wp_rftpn0v78k_pmpro_membership_orders
                JOIN wp_rftpn0v78k_pmpro_memberships_users ON wp_rftpn0v78k_pmpro_memberships_users.user_id = wp_rftpn0v78k_pmpro_membership_orders.user_id
                JOIN wp_rftpn0v78k_users ON wp_rftpn0v78k_users.id = wp_rftpn0v78k_pmpro_membership_orders.user_id
                WHERE wp_rftpn0v78k_pmpro_memberships_users.status = "active"
            ');

        //2. Clean Middeware token table
        $rows = [];
        foreach ($tokens as $key => $tk) {
            if ($tk->enddate != '0000-00-00 00:00:00') {
                $row['id'] = $key;
                $row['token'] = $tk->token;
                $row['status'] = $tk->status;
                $row['id_order'] = $tk->id_order;
                $row['user_id'] = $tk->user_id;
                $row['enddate'] = $tk->enddate;
                $row['display_name'] = $tk->display_name;
                $row['user_email'] = $tk->user_email;
                $rows[] = implode(',', $row);
            }
        }
        Storage::disk('local')->put('magento_tokens.csv', implode("\n", $rows));
        DB::statement("TRUNCATE TABLE token");
        $path = str_replace("\\", "/", base_path());
        DB::connection()->getpdo()->exec(
            "LOAD DATA LOCAL INFILE '" . $path . "/storage/app/magento_tokens.csv' INTO TABLE token
            FIELDS TERMINATED BY ','"
        );
        return 'Success';
    }

    public static function updateStatusWhenCancelingMagento()
    {
        $orders = Order::where('fulfillment_status', 11)->whereNotNull('magento_order_id')->whereNotNull('magento_entity_id')->get();
        echo count($orders);
        foreach ($orders as $order) {
            $orderM = DB::connection('mysql_magento')->select('SELECT * FROM `mg_sales_order` WHERE entity_id = ' . $order->magento_entity_id);
            if (count($orderM)) {
                if ($orderM[0]->status == 'canceled' && $orderM[0]->state == 'canceled') {
                    $order->fulfillment_status = 9;
                    $order->financial_status = 3;
                    $order->save();
                    echo 'order: ' . $order->id . ' has updated its status<br>';
                }
            }
        }

        //Update state Pending to Processing, shipping orders and closed orders
        $orders = Order::where('fulfillment_status', 5)->whereNotNull('magento_order_id')->whereNotNull('magento_entity_id')->get();
        echo count($orders);
        foreach ($orders as $order) {
            $orderM = DB::connection('mysql_magento')->select('SELECT * FROM `mg_sales_order` WHERE entity_id = ' . $order->magento_entity_id);
            if (count($orderM)) {
                if ($orderM[0]->status == 'pending' && $orderM[0]->state == 'new') {
                    DB::connection('mysql_magento')->update('UPDATE `mg_sales_order` SET `status` = "processing",`state` = "processing" WHERE entity_id = ' . $order->magento_entity_id);
                    DB::connection('mysql_magento')->update('UPDATE `mg_sales_order_status_history` SET `status` = "processing" WHERE parent_id = ' . $order->magento_entity_id);
                    DB::connection('mysql_magento')->update('UPDATE `mg_sales_order_grid` SET `status` = "processing" WHERE entity_id = ' . $order->magento_entity_id);
                    echo 'order: ' . $order->id . ' has updated its status in magento<br>';
                }
                if (($orderM[0]->status == 'complete' && $orderM[0]->state == 'complete') || ($orderM[0]->status == 'closed' && $orderM[0]->state == 'closed')) {
                    $order->fulfillment_status = 6;
                    $order->financial_status = 2;
                    $order->save();
                    echo 'order: ' . $order->id . ' has updated its status<br>';
                }
            }
        }
        return 'success';
    }

    public static function syncCategories()
    {
        $filter = [
            'searchCriteria[filterGroups][1][filters][0][field]' => 'status',
            'searchCriteria[filterGroups][1][filters][0][value]' => 1,
            'searchCriteria[filterGroups][1][filters][0][condition_type]' => "eq"
        ];
        $categoriesIds = [];
        (new SyncLib())->getRecursiveCategories(json_decode(MCategory::get($filter))->children_data, $categoriesIds);
        DB::table('categories')->whereNotIn('id', $categoriesIds)->delete();
        return 'success';
    }

    public static function syncProducts()
    {
        echo 'Start: ' . gmdate('h:i:s', time());

        DB::statement("TRUNCATE TABLE temp_products");
        Storage::disk('local')->delete('magento_products.csv');

        $file = file_get_contents('https://members.greendropship.com/downloads/products.csv');
        Storage::disk('local')->put('magento_products.csv', $file);
        $path = str_replace("\\", "/", base_path());
        DB::connection()->getpdo()->exec(
            'LOAD DATA INFILE "' . $path . '/storage/app/magento_products.csv"
            INTO TABLE temp_products
            COLUMNS TERMINATED BY ","
            OPTIONALLY ENCLOSED BY "\""
            IGNORE 1 LINES'
        );

        DB::statement(
            "UPDATE temp_products
            SET monthly_special = 0
            WHERE monthly_special = ''"
        );

        DB::statement(
            "UPDATE temp_products
            SET suggested_retail = 0
            WHERE suggested_retail = ''"
        );

        DB::statement(
            "UPDATE temp_products
            SET `weight` = 0
            WHERE `weight` = ''"
        );

        DB::statement(
            "DELETE FROM temp_products
            WHERE sku LIKE '% %'"
        );

        $price_different_ids = DB::table('products')
            ->join('temp_products', function ($join) {
                $join->on('temp_products.sku', '=', 'products.sku');
                $join->on('temp_products.price', '!=', 'products.price');
            })
            ->join('my_products', 'my_products.id_product', '=', 'products.id')
            ->pluck('my_products.id');
        if (count($price_different_ids) > 0) {
            $str = '(';
            foreach ($price_different_ids as $id) {
                $str .= $id . ",";
            }
            $str = substr($str, 0, -1);
            $str .= ')';
            DB::statement(
                "UPDATE products P
                INNER JOIN temp_products T ON T.sku = P.sku
                INNER JOIN my_products M ON M.id_product = P.id
                SET P.price = T.price, M.cron = 1
                WHERE M.id IN " . $str
            );
        }

        $updateable_ids = DB::table('products')
            ->join('temp_products', 'temp_products.sku', '=', 'products.sku')
            ->pluck('products.sku');
        if (count($updateable_ids) > 0) {
            $str = '(';
            foreach ($updateable_ids as $id) {
                $str .= "'" . $id . "',";
            }
            $str = substr($str, 0, -1);
            $str .= ')';
            DB::statement(
                "UPDATE products P
                INNER JOIN temp_products T ON P.sku = T.sku
                SET P.name = T.name,
                    P.price = T.price,
                    P.stock = T.qty,
                    P.brand = T.brand,
                    P.image_url = SUBSTR(T.images_1, 18),
                    P.weight = T.weight,
                    P.type_id = 'simple',
                    P.status = 1,
                    P.categories = T.categories,
                    P.images = CASE
                        WHEN T.images_2 = '' AND T.images_3 = '' AND T.images_4 = '' THEN JSON_ARRAY(
                            JSON_OBJECT('media_type', 'image', 'label', null, 'position', 1, 'disabled', false, 'types', JSON_ARRAY('image', 'small_image', 'thumbnail'), 'file', SUBSTR(T.images_1, 18))
                        )
                        WHEN T.images_2 != '' AND T.images_3 = '' AND T.images_4 = '' THEN JSON_ARRAY(
                            JSON_OBJECT('media_type', 'image', 'label', null, 'position', 1, 'disabled', false, 'types', JSON_ARRAY('image', 'small_image', 'thumbnail'), 'file', SUBSTR(T.images_1, 18)),
                            JSON_OBJECT('media_type', 'image', 'label', null, 'position', 1, 'disabled', false, 'types', JSON_ARRAY('image', 'small_image', 'thumbnail'), 'file', SUBSTR(T.images_2, 18))
                        )
                        WHEN T.images_2 != '' AND T.images_3 != '' AND T.images_4 = '' THEN JSON_ARRAY(
                            JSON_OBJECT('media_type', 'image', 'label', null, 'position', 1, 'disabled', false, 'types', JSON_ARRAY('image', 'small_image', 'thumbnail'), 'file', SUBSTR(T.images_1, 18)),
                            JSON_OBJECT('media_type', 'image', 'label', null, 'position', 1, 'disabled', false, 'types', JSON_ARRAY('image', 'small_image', 'thumbnail'), 'file', SUBSTR(T.images_2, 18)),
                            JSON_OBJECT('media_type', 'image', 'label', null, 'position', 1, 'disabled', false, 'types', JSON_ARRAY('image', 'small_image', 'thumbnail'), 'file', SUBSTR(T.images_3, 18))
                        )
                        WHEN T.images_2 != '' AND T.images_3 != '' AND T.images_4 != '' THEN JSON_ARRAY(
                            JSON_OBJECT('media_type', 'image', 'label', null, 'position', 1, 'disabled', false, 'types', JSON_ARRAY('image', 'small_image', 'thumbnail'), 'file', SUBSTR(T.images_1, 18)),
                            JSON_OBJECT('media_type', 'image', 'label', null, 'position', 1, 'disabled', false, 'types', JSON_ARRAY('image', 'small_image', 'thumbnail'), 'file', SUBSTR(T.images_2, 18)),
                            JSON_OBJECT('media_type', 'image', 'label', null, 'position', 1, 'disabled', false, 'types', JSON_ARRAY('image', 'small_image', 'thumbnail'), 'file', SUBSTR(T.images_3, 18)),
                            JSON_OBJECT('media_type', 'image', 'label', null, 'position', 1, 'disabled', false, 'types', JSON_ARRAY('image', 'small_image', 'thumbnail'), 'file', SUBSTR(T.images_4, 18))
                        )
                    END,
                    P.attributes = JSON_ARRAY(
                        JSON_OBJECT('attribute_code', 'image', 'value', SUBSTR(T.images_1, 18)),
                        JSON_OBJECT('attribute_code', 'description', 'value', T.description),
                        JSON_OBJECT('attribute_code', 'ship_width', 'value', T.width),
                        JSON_OBJECT('attribute_code', 'ship_length', 'value', T.length),
                        JSON_OBJECT('attribute_code', 'ship_height', 'value', T.height),
                        JSON_OBJECT('attribute_code', 'brand', 'value', T.brand),
                        JSON_OBJECT('attribute_code', 'upc', 'value', SUBSTR(T.upc, 2)),
                        JSON_OBJECT('attribute_code', 'cube', 'value', T.cubic_volume),
                        JSON_OBJECT('attribute_code', 'size', 'value', T.size),
                        JSON_OBJECT('attribute_code', 'size_uom', 'value', T.size_uom),
                        JSON_OBJECT('attribute_code', 'storage', 'value', '')
                    ),
                    P.stock_info = T.storage,
                    P.upc = SUBSTR(T.upc, 2),
                    P.updated_at = UTC_TIMESTAMP(),
                    P.suggested_retail = T.suggested_retail,
                    P.lead_time = T.`lead-time`,
                    P.monthly_special = T.monthly_special
                WHERE P.sku IN " . $str
            );
        }

        $insertable_ids = DB::table('temp_products')
            ->leftJoin('products', 'products.sku', '=', 'temp_products.sku')
            ->whereNull('products.sku')
            ->pluck('temp_products.sku');
        if (count($insertable_ids) > 0) {
            $str = '(';
            foreach ($insertable_ids as $id) {
                $str .= "'" . $id . "',";
            }
            $str = substr($str, 0, -1);
            $str .= ')';
            DB::statement(
                "INSERT INTO `products`(`sku`,`name`,`price`,`stock`,`brand`,`image_url`,`weight`,`type_id`,`status`,`categories`, `images`,`attributes`,`stock_info`,`upc`, `created_at`, `updated_at`, `suggested_retail`, `lead_time`, `monthly_special`)
                SELECT T.sku,T.name,T.price, T.qty, T.brand, SUBSTR(T.images_1, 18),T.weight,'simple',1,T.categories,
                    (
                        CASE
                            WHEN T.images_2 = '' AND T.images_3 = '' AND T.images_4 = '' THEN JSON_ARRAY(
                                JSON_OBJECT('media_type', 'image', 'label', null, 'position', 1, 'disabled', false, 'types', JSON_ARRAY('image', 'small_image', 'thumbnail'), 'file', SUBSTR(T.images_1, 18))
                            )
                            WHEN T.images_2 != '' AND T.images_3 = '' AND T.images_4 = '' THEN JSON_ARRAY(
                                JSON_OBJECT('media_type', 'image', 'label', null, 'position', 1, 'disabled', false, 'types', JSON_ARRAY('image', 'small_image', 'thumbnail'), 'file', SUBSTR(T.images_1, 18)),
                                JSON_OBJECT('media_type', 'image', 'label', null, 'position', 1, 'disabled', false, 'types', JSON_ARRAY('image', 'small_image', 'thumbnail'), 'file', SUBSTR(T.images_2, 18))
                            )
                            WHEN T.images_2 != '' AND T.images_3 != '' AND T.images_4 = '' THEN JSON_ARRAY(
                                JSON_OBJECT('media_type', 'image', 'label', null, 'position', 1, 'disabled', false, 'types', JSON_ARRAY('image', 'small_image', 'thumbnail'), 'file', SUBSTR(T.images_1, 18)),
                                JSON_OBJECT('media_type', 'image', 'label', null, 'position', 1, 'disabled', false, 'types', JSON_ARRAY('image', 'small_image', 'thumbnail'), 'file', SUBSTR(T.images_2, 18)),
                                JSON_OBJECT('media_type', 'image', 'label', null, 'position', 1, 'disabled', false, 'types', JSON_ARRAY('image', 'small_image', 'thumbnail'), 'file', SUBSTR(T.images_3, 18))
                            )
                            WHEN T.images_2 != '' AND T.images_3 != '' AND T.images_4 != '' THEN JSON_ARRAY(
                                JSON_OBJECT('media_type', 'image', 'label', null, 'position', 1, 'disabled', false, 'types', JSON_ARRAY('image', 'small_image', 'thumbnail'), 'file', SUBSTR(T.images_1, 18)),
                                JSON_OBJECT('media_type', 'image', 'label', null, 'position', 1, 'disabled', false, 'types', JSON_ARRAY('image', 'small_image', 'thumbnail'), 'file', SUBSTR(T.images_2, 18)),
                                JSON_OBJECT('media_type', 'image', 'label', null, 'position', 1, 'disabled', false, 'types', JSON_ARRAY('image', 'small_image', 'thumbnail'), 'file', SUBSTR(T.images_3, 18)),
                                JSON_OBJECT('media_type', 'image', 'label', null, 'position', 1, 'disabled', false, 'types', JSON_ARRAY('image', 'small_image', 'thumbnail'), 'file', SUBSTR(T.images_4, 18))
                            )
                        END
                    ),
                    JSON_ARRAY(
                        JSON_OBJECT('attribute_code', 'image', 'value', SUBSTR(T.images_1, 18)),
                        JSON_OBJECT('attribute_code', 'description', 'value', T.description),
                        JSON_OBJECT('attribute_code', 'ship_width', 'value', T.width),
                        JSON_OBJECT('attribute_code', 'ship_length', 'value', T.length),
                        JSON_OBJECT('attribute_code', 'ship_height', 'value', T.height),
                        JSON_OBJECT('attribute_code', 'brand', 'value', T.brand),
                        JSON_OBJECT('attribute_code', 'upc', 'value', SUBSTR(T.upc, 2)),
                        JSON_OBJECT('attribute_code', 'cube', 'value', T.cubic_volume),
                        JSON_OBJECT('attribute_code', 'size', 'value', T.size),
                        JSON_OBJECT('attribute_code', 'size_uom', 'value', T.size_uom),
                        JSON_OBJECT('attribute_code', 'storage', 'value', '')
                    ),T.storage, SUBSTR(T.upc, 2), UTC_TIMESTAMP(), UTC_TIMESTAMP(), T.suggested_retail, T.`lead-time`, T.monthly_special
                FROM temp_products T
                WHERE T.sku IN " . $str
            );
        }

        $unprovideable_ids = DB::table('products')
            ->leftJoin('temp_products', 'temp_products.sku', '=', 'products.sku')
            ->whereNull('temp_products.sku')
            ->where('products.stock', '!=', 0)
            ->pluck('products.sku');
        if (count($unprovideable_ids) > 0) {
            $str = '(';
            foreach ($unprovideable_ids as $id) {
                $str .= "'" . $id . "',";
            }
            $str = substr($str, 0, -1);
            $str .= ')';
            DB::statement(
                "UPDATE products P
                LEFT JOIN temp_products T ON T.sku = P.sku
                SET P.stock = 0, P.updated_at = UTC_TIMESTAMP()
                WHERE P.sku IN " . $str
            );
        }
        echo 'End: ' . gmdate('h:i:s', time());
        return 'Success';
    }

    public function getRecursiveCategories($children_data, &$categoriesIds)
    {

        foreach ($children_data as $Mcategory) {
            try {
                $categoriesIds[] = $Mcategory->id;
                $category = Category::find($Mcategory->id);
                if ($category == null) {
                    $category = new Category();
                    $category->id = $Mcategory->id;
                    $category->is_active = $Mcategory->is_active;
                }
                $category->parent_id = $Mcategory->parent_id;
                $category->name = $Mcategory->name;
                $category->level = $Mcategory->level;
                $category->position = $Mcategory->position;
                $category->save();
                if (count($Mcategory->children_data)) {
                    $this->getRecursiveCategories($Mcategory->children_data, $categoriesIds);
                }
            } catch (Exception $ex) {
                echo 'Error' . $ex->getMessage();
            }
        }
    }
}
