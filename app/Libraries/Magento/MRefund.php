<?php

namespace App\Libraries\Magento;

class MRefund 
{

  // /V1/orders/{id} https://developer.adobe.com/commerce/webapi/rest/quick-reference/
  public static function getOrderDetail($magento_entity_id){
    $api = MagentoApi::getInstance();

    $result = $api->query('GET', 'orders/' . $magento_entity_id);

    return $result;
  }

  // /V1/creditmemo/refund https://developer.adobe.com/commerce/webapi/rest/quick-reference/
  public static function createCreditMemo($data){
    $api = MagentoApi::getInstance();

    exit;
    $result = $api->query('POST', 'creditmemo/refund', [], json_encode($data));

    return $result;
  }
}