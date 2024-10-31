<?php

/**
 * Class RentMy_Order
 */
Class RentMy_Order extends RentMy
{
    function __construct() {
        if(!headers_sent() && '' == session_id())
            session_start();
    }

    function viewOrderDetails($order_id) {
        try {
            $response = self::fetch(
                '/orders/'.$order_id.'/complete',
                [
                    'token' => get_option('rentmy_accessToken'),
                    'location' => get_option('rentmy_locationId')
                ]
            );

//    custom product fields
            $cartResponse = [];

            if (!empty($response['data'])) {
                $cartResponse = $response['data'];

                $order_items = $cartResponse['order_items'];
                foreach ($order_items as $key => $item) {
                    $customFields = [];
                    if (!empty($item['order_product_options'])) {
                        foreach ($item['order_product_options'] as $options) {

                            foreach ($options['options'] as $option) {
                                if (array_key_exists($option['label'], $customFields)) {
                                    array_push($customFields[$option['label']], $option);
                                } else {
                                    $customFields[$option['label']][] = $option;
                                }

                            }
                        }

                    }
                    $order_items[$key]['custom_fields'] = $customFields;
                }
                $cartResponse['order_items'] = $order_items;
                $response['data'] = $cartResponse;

            }

            return $response;
        } catch (Exception $e) {

        }
    }

    function reviewOrderDetials($order_id) {
        try {
            $response = self::fetch(
                '/orders/'.$order_id,
                [
                    'token' => get_option('rentmy_accessToken'),
                    'location' => get_option('rentmy_locationId')
                ]
            );
            return $response;
        } catch (Exception $e) {

        }
    }

    public function status(){
        try{
            $response = self::fetch(
                '/order/status',
                [
                    'token' => get_option('rentmy_accessToken'),
                    'location' => get_option('rentmy_locationId')
                ]
            );
            return $response;
        }catch(Exception $e){
            
        }
    }
    public function orderAdditionalCharges($order_id, $type="order"){

        try {
            $response = self::fetch(
                '/orders/view-charges/'. $order_id .'?type='.$type,
                [
                    'token' => get_option('rentmy_accessToken'),
                    'location' => get_option('rentmy_locationId')
                ]
            );
            return $response;
        } catch (Exception $e) {

        }
    }

    public function deleteAdditionalCharge($params){
        try {
            $response = self::http_delete(
                '/orders/delete-charge/'. $params['id'],
                [
                    'token' => get_option('rentmy_accessToken'),
                    'location' => get_option('rentmy_locationId')
                ]
            );
            return $response;
        } catch (Exception $e) {

        }
    }


}
