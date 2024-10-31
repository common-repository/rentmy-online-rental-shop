<?php

/**
 * Class RentMy_Checkout
 */
Class RentMy_Checkout extends RentMy
{
    public $shipping_type = ['fedex' => 4, 'ups' => 5, 'standard' => 6];

    function __construct()
    {
        if (!headers_sent() && '' == session_id())
            session_start();
    }

    // capture data from first step of checkout
    function saveInfo($params)
    {
        self::setCheckoutSession('info', $params);
        return $params;

    }

    // capture data from second step of checkout
    function saveFulfilment($params)
    {
        if ($params['type'] == 'instore') {
            $data['delivery'] = $params;
            $data['shipping_method'] = 1;
            self::setCheckoutSession('fulfillment', $data);
        } elseif ($params['type'] == 'delivery') {
            $data['shipping_method'] = 1;
            $data['delivery'] = $params;
            self::setCheckoutSession('fulfillment', $data);
        } elseif ($params['type'] == 'shipping') {
            $data = $params;
            $data['delivery'] = json_decode(stripslashes($params['shipping']), true);
            $data['delivery']['type'] = $params['type'];
            unset($data['shipping']);
            self::setCheckoutSession('fulfillment', $data);
        }

        return $params;

    }


    // get checkout custom fields
    function getCustomFields()
    {
        try {
            $response = self::fetch(
                '/custom-fields?section=checkout',
                [
                    'token' => get_option('rentmy_accessToken'),
                ]
            );
            return $response;
        } catch (Exception $e) {

        }
    }

    // get terms and conditions fields
    function termsAndCondition()
    {
        try {
            $response = self::fetch(
                '/pages/terms-and-conditions',
                [
                    'token' => get_option('rentmy_accessToken'),
                    'location' => get_option('rentmy_locationId')
                ]
            );
            if(empty($response['data'])){
                if(empty($response['data']['contents'])){
                    $response['data']['contents'] = json_encode(['content'=>'','heading'=>'']);
                }
            }
            return $response;
        } catch (Exception $e) {

        }
    }

    // check free shipping for the cart token
    function checkFreeShipping()
    {
        try {
            $response = self::fetch(
                '/free-shipping/' . $_SESSION['rentmy_cart_token'],
                [
                    'token' => get_option('rentmy_accessToken'),
                    'location' => get_option('rentmy_locationId')
                ]
            );
            return $response;
        } catch (Exception $e) {

        }
    }

    // upload files upon custom fields files field
    function uploadMedia($media)
    {
        try {
            $uploadDir = wp_upload_dir();
            $uploadedfile = $media['file'];
            $upload_overrides = array('test_form' => false);
            $movefile = wp_handle_upload($uploadedfile, $upload_overrides);
            if ($movefile) {
                $file = $movefile['file'];
            }

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => RENTMY_API_URL . '/media/upload',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => array('file'=> new CURLFILE($file),'type' => $media['type']),
                CURLOPT_HTTPHEADER => array(
                    "Authorization: Bearer ".get_option('rentmy_accessToken')
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);
            return json_decode($response,true);
        } catch (Exception $e) {

        }
    }

    // get currency configurations
    function getCurrencyConfig()
    {
        try {
            $response = self::fetch(
                '/currency-config',
                [
                    'token' => get_option('rentmy_accessToken'),
                ]
            );
            return $response;
        } catch (Exception $e) {

        }
    }


    // get checkout location lists
    function getLocationLists()
    {
        try {
            $response = self::fetch(
                '/locations/list',
                [
                    'token' => get_option('rentmy_accessToken'),
                ]
            );
            return $response;
        } catch (Exception $e) {

        }
    }

    /**
     * get shipping methods
     * @param $data
     * @return mixed|string|null
     */
    function getShippingList($data)
    {
        unset($data['loc']);
        $response = self::rentmy_fetch(
            '/shipping/rate',
            [
                'token' => get_option('rentmy_accessToken'),
            ],
            [
                'address' => $data,
                'pickup' => get_option('rentmy_locationId'),
                'token' => $_SESSION['rentmy_cart_token']
            ]
        );
        if ($response['status'] == 'NOK') {
            return $response;
        }
        if ($response['status'] == 'OK') {
            $rentmy_config = new RentMy_Config();
            $store_content = $rentmy_config->store_contents();
            $checkout_labels = $store_content[0]['contents']['checkout_info'];
            //print_r("<pre>");print_r($GLOBALS['checkout_labels']);print_r("</pre>");
            if (!empty($response['result'])) {
                $fulfillment = [];
                $i = 0;
                $res = '';
                $html_head = '<h4 class="shipping-choose-label">'.$checkout_labels['title_select_shipping_method']?? "Select Shipping Method".'</h4>';
                foreach ($response['result'] as $key => $shippings) {

                    if (strtolower($key) == 'standard') {
                        $shipping_method = 6;
                    } else {
                        $shipping_method = 4;
                    }

//                    $shipping_method = $this->shipping_type[$shipping['response']['carrier_code']];

                    foreach ($shippings as $shipping) {
                        $html = '<label class="radio-container radiolist-container">';
                        $json = json_encode($shipping);
                        $html .= "<input type='radio' data-type='" . $shipping_method . "'   data-amount='" . $shipping['charge'] . "' data-tax='" . $shipping['tax'] . "' name='shipping_method' value='" . $json . "'><span class='rentmy-radio-text'>" . $shipping['service_name'] . "</span>";
                        $html .= '<span class="rentmy-radio-date">Estimated Delivery Date: ' . date("F j, Y", strtotime($shipping['delivery_date'])) . '</span>';
                        $html .= '<span class="rentmy-radio-day">  Delivery days: ' . $shipping['delivery_days'] . '</span>';
                        $html .= '<span class="rentmy-radio-price">' . self::currency($shipping['charge']) . '</span>';
                        $html .= '<span class="checkmark"></span></label>';

                        $res .= $html;
                        $fulfillment['data'][$i] = ['html' => $html, 'cost' => $shipping['charge']];
                        $i++;
                    }
                }
                $fulfillment['html'] = $html_head . $res;
            }

        } else {
            $fulfillment = [];
        }
//        print_r( [
//            'address' => $data,
//            'pickup'=> 130,//get_option('rentmy_locationId'),
//            'token' => 1571943865922 //$_COOKIE['rentmy_cart_token']
//        ]);
        return $fulfillment;
    }

    /**
     * get shipping methods by Arafat
     * @param $data
     * @return mixed|string|null
     */
    function getShippingListByKN($data)
    {
        unset($data['loc']);
        $response = self::rentmy_fetch(
            '/shipping/rate',
            [
                'token' => get_option('rentmy_accessToken'),
            ],
            [
                'address' => $data,
                'pickup' => get_option('rentmy_locationId'),
                'token' => $_SESSION['rentmy_cart_token']
            ]
        );
        if ($response['status'] == 'NOK') {
            return $response;
        }
        if ($response['status'] == 'OK') {
            $rentmy_config = new RentMy_Config();
            $store_content = $rentmy_config->store_contents();
            $checkout_labels = $store_content[0]['contents']['checkout_info'];
            $response['config'] = $rentmy_config;
            $response['contents'] = $store_content;
            $response['labels'] = $checkout_labels;
            return $response;

        } else {
            $fulfillment = [];
        }

        return $fulfillment;
    }

    /**
     * @param $data
     * @return mixed|string|null
     */
    function getDeliveryCost($data)
    {
        try {
            $response = self::fetch(
                '/delivery-charge-list',
                [
                    'token' => get_option('rentmy_accessToken'),
                ],
                [
                    'address' => $data,
                ]
            );

            return $response;
        } catch (Exception $e) {

        }
    }



    /**
     * this function will post pickup+drop-off info and will calculate estimate delivery charges
     * @API (@POST /order/delivery/estimate)
     * @return mixed
     */
    public function estimateMultiStoreDeliveryCharge($data){
        try {

            $data['location_id'] = get_option('rentmy_locationId');
            $data['token'] = $_SESSION['rentmy_cart_token'];

            return self::fetch(
                '/order/delivery/estimate',
                [
                    'token' => get_option('rentmy_accessToken'),
                    'location' => get_option('rentmy_locationId')
                ],

                $data
            );
        } catch (Exception $e) {

        }
    }


    // get delivery addresses methods
    function addShippingToCarts($params)
    {
        try {
            if (!empty($_SESSION['rentmy_cart_token'])) {
                $response = self::rentmy_fetch(
                    '/carts/delivery',
                    [
                        'token' => get_option('rentmy_accessToken'),
                    ],
                    [
                        'shipping_cost' => $params['shipping_cost'],
                        'shipping_method' => $params['shipping_method'],
//                        'tax' => $params['tax'],
//                        'tax_id'=> $params['tax_id'],
                        'address' => $params['address'],
                        'token' => $_SESSION['rentmy_cart_token'],
                    ]
                );
                return $response;
            } else {
                return ['status' => 'NOK', 'message' => 'Invalid cart token'];
            }
        } catch (Exception $e) {

        }
    }

    function singleSubmitCheckout($data)
    {

        $info = json_decode(stripslashes($data));
        $info = (array)$info;

        try {

            $cartToken = $_SESSION['rentmy_cart_token'];

            if (empty($cartToken)) {
                return ['status' => 'NOK', 'message' => 'Invalid cart.'];
            }

            $checkout_info = [];
            $cols = [
                'first_name','last_name','mobile','email','address_line1','address_line2','city','state','country','zipcode',
                'custom_values','special_instructions','special_requests','driving_license',
                'delivery','shipping_method','combinedAddress','fieldSelection','fieldText',
                'shipping_address1','shipping_address2','shipping_city','shipping_country','shipping_state','shipping_zipcode',
                'currency'
            ];

            foreach ($cols as $col) {
                $checkout_info[$col] = isset($info[$col]) ? $info[$col] : '';
            }

            $checkout_info['pickup'] = $info['pickup'];
            // $checkout_info['pickup'] = 26;
            $checkout_info['token'] = $cartToken;

            $checkout_info['signature'] = $info['signature'];
            $checkout_info['gateway_id'] = $info['payment_gateway_id'];
            $checkout_info['type'] = ($info['payment_gateway_type']=='offline') ? 2 : 1;
            $checkout_info['payment_gateway_name'] = trim($info['payment_gateway_name']);
            $checkout_info['account'] = $info['card_no'];
            if ($checkout_info['payment_gateway_name'] == 'Stripe'){
                if (!empty($info['for_enduring'])){
                    $checkout_info['account'] = [
                        'for_enduring' => true,
                        'payment_method_id' => !empty($info['payment_method_id'])?$info['payment_method_id']:''
                    ];
                }else{
                    $checkout_info['account'] = [
                        'id' => $info['card_no'],
                        'customer' => !empty($info['customer'])?$info['customer']:''
                    ];
                }

                if(!empty($info['requires_action']))
                    $checkout_info['account']['requires_action'] = true;

            }
            $checkout_info['order_source'] = 'WP Plugin';

            $checkout_info['shipping_email'] = $info['shipping_email'];
            $checkout_info['shipping_first_name'] = $info['shipping_first_name'];
            $checkout_info['shipping_last_name'] = $info['shipping_last_name'];
            $checkout_info['shipping_mobile'] = $info['shipping_mobile'];
            $checkout_info["expiry"] = $info['exp_month'] . $info['exp_year'];
            $checkout_info['cvv2'] = $info['cvv'];
            if (isset($info['amount'])){
                $checkout_info['payment_amount'] = $info['amount'];
            }

            if (isset($info['delivery_multi_store'])){

                $checkout_info['delivery_multi_store'] = $info['delivery_multi_store'];
            }

            if ($info['is_customer_account']){
                $checkout_info['is_customer_account'] = $info['is_customer_account'];
            }

            $checkout_info['custom_values']=[];
            if (!empty($info['custom_values'])) {
                foreach($info['custom_values'] as $i=>$custom_value) {
                    $checkout_info['custom_values'][$i]['field_label'] = $custom_value->field_label;
                    $checkout_info['custom_values'][$i]['field_name'] = $custom_value->field_name;
                    $checkout_info['custom_values'][$i]['field_values'] = $custom_value->field_values;
                    $checkout_info['custom_values'][$i]['id'] = $custom_value->id;
                    $checkout_info['custom_values'][$i]['type'] = $custom_value->type;
                }
            }
            // if (!empty($info['additional_charges'])) {
            //     foreach($info['additional_charges'] as $i=>$additional_charge) {
            //         if(!empty($additional_charge->value)){
            //             $checkout_info['additional_charges'][$i]['id'] = $additional_charge->id;
            //             $checkout_info['additional_charges'][$i]['value'] = $additional_charge->value;
            //         }
            //     }
            // }
            $url = RENTMY_API_URL . '/orders/online';
            $ch = curl_init($url);
            $payload = json_encode($checkout_info);
            //    print_r("<pre>");print_r($payload);print_r("</pre>");
            //      print_r("<pre>");print_r(get_option('rentmy_accessToken'));print_r("</pre>");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Location: ' . get_option('rentmy_locationId'),
                'Authorization: Bearer ' . get_option('rentmy_accessToken')
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $json_response = curl_exec($ch);

            $response = json_decode($json_response, true);

//            print_r("<pre>");print_r($response);print_r("</pre>");
            if (!$response['result']['data']['payment']['success']) {
                if (empty($response['result']['data']['payment']['message'])) {
                    $message = "Payment not completed successfully . Order can't be created. Please try again.";
                } else {
                    $message = $response['result']['data']['payment']['message'];
                }
                return ['status' => 'NOK', 'message' => $message];
            } else if (!$response['result']['data']['availability']['success']) {
                return ['status' => 'NOK', 'message' => "Order can't be created . Some products may not available . Please try again . "];
            }

            $_SESSION['order_uid'] = $response['result']['data']['order']['data']['uid'];
            $_SESSION['order_id'] = $response['result']['data']['payment']['order_id'];
            // delete session && cookie
            unset($_SESSION['rentmy_cart_token']);
            unset($_SESSION['rentmy_checkout']);

            return ['status' => 'OK', 'uid' => $_SESSION['order_uid'],'id'=>$_SESSION['order_id'],'message'=> 'Order created successfully'];
        } catch (Exception $e) {

        }
    }

    public function affiliatePluginNotifier($checkoutResponse, $amount=0, $affiliateId='')
    {

        $storeID = get_option('rentmy_storeId');
        if ((int)$storeID != 534){
            return;
        }
        if ( !function_exists( 'affwp_add_referral' )  || !function_exists( 'affwp_calc_referral_amount' ) ) {
            return;
        }

        if (empty($affiliateId))
            return;

        $order_id = $checkoutResponse['uuid'];

        $params = [
            'affiliate_id' => $affiliateId,
            'amount'    => affwp_calc_referral_amount($amount, $affiliateId),
            'type'  => 'sale', //"sale", "lead" and "opt-in".
            'context'  => 'RentMy WP',
            'reference'  => $order_id,
            'date'  => date('m/d/y', strtotime('now'))
        ];

        if ($_COOKIE['affwp_ref_visit_id'])
            $params['visit_id'] = $_COOKIE['affwp_ref_visit_id'];

        affwp_add_referral($params);
    }

    // finally do the checkout process
    function doCheckout($data)
    {

        try {
            $info = $_SESSION['rentmy_checkout']['info'];
            $fulfillment = $_SESSION['rentmy_checkout']['fulfillment'];
            $payment = $data;
            $cartToken = $_SESSION['rentmy_cart_token'];
            if (empty($cartToken)) {
                return ['status' => 'NOK', 'message' => 'Invalid cart.'];
            }
            if($fulfillment['delivery']['type']== 'delivery'){
                $fulfillment = $fulfillment['delivery'];
                $fulfillment['delivery'] = json_decode(preg_replace('/\\\\/', '', $fulfillment['shipping']),true);
                $fulfillment['delivery']['type'] = 'delivery';
            }
            $checkout_info = [
                'first_name' => $info['first_name'],
                'last_name' => $info['last_name'],
                'mobile' => $info['mobile'],
                'email' => $info['email'],
                'address_line1' => $info['address_line1'],
                'address2' => $info['address_line2'],
                'city' => $info['city'],
                'state' => $info['state'],
                'combinedAddress' => "",
                'country' => 'us',
                'zipcode' => $info['zipcode'],
                'custom_values' => null,
                'special_instructions' => $info['special_instructions'],
                'special_requests' => $info['special_requests'],
                'driving_license' => $info['driving_license'],
                'fieldSelection' => null,
                'fieldText' => null,
                //'pickup' => 130,
                'delivery' => $fulfillment['delivery'],
                'shipping_method' => $fulfillment['shipping_method'],
                'currency' => 'USD',
                'token' => $cartToken,
                'signature' => null,
                'gateway_id' => $payment['payment_gateway_id'],
                'type' => $payment['payment_gateway_type'],
                'note' => $payment['note'],
                'payment_gateway_name' => trim($payment['payment_gateway_name']),
                'account' => $payment['card_no'],
                'order_source'=> 'WP Plugin'
            ];
            if (!empty($info['signature'])) {
                $checkout_info['signature'] = trim($info['signature']);
            }
            if (in_array($fulfillment['delivery']['type'],[ 'shipping','delivery'])) {
                $checkout_info['shipping_address1'] = $fulfillment['shipping_address1'];
                $checkout_info['shipping_address2'] = $fulfillment['shipping_address2'];
                $checkout_info['shipping_city'] = $fulfillment['shipping_city'];
                $checkout_info['shipping_country'] = $fulfillment['shipping_country'];
                $checkout_info['shipping_email'] = $info['email'];
                $checkout_info['shipping_first_name'] = $info['first_name'];
                $checkout_info['shipping_last_name'] = $info['last_name'];
                $checkout_info['shipping_mobile'] = $info['mobile'];
                $checkout_info['shipping_state'] = $fulfillment['shipping_state'];
                $checkout_info['shipping_zipcode'] = $fulfillment['shipping_zipcode'];

                // if($fulfillment['delivery']['type'] == 'shipping'){
                //     $checkout_info['shipping_first_name'] = $fulfillment['shipping_first_name'];
                //     $checkout_info['shipping_last_name'] = $fulfillment['shipping_last_name'];
                //     $checkout_info['shipping_mobile'] = $fulfillment['shipping_mobile'];
                // }

                // if($fulfillment['delivery']['type'] == 'delivery'){
                //     $checkout_info['shipping_first_name'] = $fulfillment['delivery_first_name'];
                //     $checkout_info['shipping_last_name'] = $fulfillment['delivery_last_name'];
                //     $checkout_info['shipping_mobile'] = $fulfillment['delivery_mobile'];
                // }
            }
            if ($checkout_info['payment_gateway_name'] != 'Stripe' && $checkout_info['type'] == 1) {
                $checkout_info["expiry"] = $payment['exp_month'] . $payment['exp_year'];
                $checkout_info['cvv2'] = $payment['cvv'];
            }

            if (!empty($data['custom_values'])) {
                $checkout_info['custom_values'] = $data['custom_values'];
            }


            // added for partial payments
            if (!empty($payment['payment_amount'])) {
                $checkout_info['payment_amount'] = $payment['payment_amount'];
                $checkout_info['amount_tendered'] = 0;
            }
            // partial payment endsSSSS

            $url = RENTMY_API_URL . '/orders/online';
            $ch = curl_init($url);
            $payload = json_encode($checkout_info);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Location: ' . get_option('rentmy_locationId'),
                'Authorization: Bearer ' . get_option('rentmy_accessToken')
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $json_response = curl_exec($ch);
            $response = json_decode($json_response, true);
            

            if (!$response['result']['data']['payment']['success']) {
                if (empty($response['result']['data']['payment']['message'])) {
                    $message = "Payment not completed successfully . Order can't be created. Please try again.";
                } else {
                    $message = $response['result']['data']['payment']['message'];
                }
                return ['status' => 'NOK', 'message' => $message];
            } else if (!$response['result']['data']['availability']['success']) {
                return ['status' => 'NOK', 'message' => "Order can't be created . Some products may not available . Please try again . "];
            }

            $_SESSION['order_uid'] = $response['result']['data']['order']['data']['uid'];
            // delete session && cookie
            unset($_SESSION['rentmy_cart_token']);
            unset($_SESSION['rentmy_checkout']);

            //linked affiliate notifier plugin hook installed
            $this->affiliatePluginNotifier($response['result']['data']['order']['data']);

            return ['status' => 'OK', 'uid' => $_SESSION['order_uid']];
        } catch (Exception $e) {

        }
    }


    public function getPaymentIntent($params){
        try {
            $params['store_id'] = get_option('rentmy_storeId');
            $params['source'] = 'online';
//            if (isset($params['customer']) && empty($params['customer']))
                unset($params['customer']);

            $response = self::rentmy_fetch(
                '/payments/stripe-intent',
                [
                    'token' => get_option('rentmy_accessToken'),
                    'location' => get_option('rentmy_locationId')
                ],
                $params
            );

            return $response;
        } catch (Exception $e) {

        }
    }

    /**
     * @param $type - info for billing details , fulfillment for shipping details
     * @param $data
     */
    function setCheckoutSession($type, $data)
    {
        $_SESSION['rentmy_checkout'][$type] = $data;
    }

    /**
     * @param string $type type = '' return full checkout details, info for billing, fulfillment for shipping/delivery
     * @return mixed
     */
    function getCheckoutSession($type = '')
    {
        if (empty($type)) {
            return $_SESSION['rentmy_checkout'];
        } else {
            return $_SESSION['rentmy_checkout'][$type];
        }
    }


    public function getTrasafeIframeAttr($params){
        try {
            $params['store_id'] = get_option('rentmy_storeId');
            $params['source'] = 'online';
            $response = self::rentmy_fetch(
                '/payments/transafe-iframe-attr?client_host='.$params['client_host'],
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
