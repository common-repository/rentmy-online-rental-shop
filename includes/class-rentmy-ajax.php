<?php

/**
 * ajax call methods and other stuffs
 */
class RentMy_Ajax
{

    function __construct()
    {
      //  add_action('wp_ajax_nopriv_rentmy_add_to_cart', array($this, 'rentmy_add_to_cart'));
     //   add_action('wp_ajax_rentmy_add_to_cart', array($this, 'rentmy_add_to_cart'));
        add_action('wp_ajax_nopriv_rentmy_checkout_information', array($this, 'rentmy_checkout_information'));
        add_action('wp_ajax_rentmy_checkout_information', array($this, 'rentmy_checkout_information'));

        add_action('wp_ajax_nopriv_rentmy_options', array($this, 'rentmy_options'));
        add_action('wp_ajax_rentmy_options', array($this, 'rentmy_options'));

        add_action('wp_ajax_nopriv_rentmy_order_details', array($this, 'rentmy_order_details'));
        add_action('wp_ajax_rentmy_order_details', array($this, 'rentmy_order_details'));

        add_action('wp_ajax_nopriv_rentmy_cart_topbar', array($this, 'rentmy_cart_topbar'));
        add_action('wp_ajax_rentmy_cart_topbar', array($this, 'rentmy_cart_topbar'));
    }

    function rentmy_add_to_cart($data)
    {
        $add_to_cart = new RentMy_Cart();
        $response = $add_to_cart->addProductToCart($data);
        return $response;
    }

    function rentmy_remove_from_cart($data)
    {
        $remove_cart = new RentMy_Cart();
        $response = $remove_cart->deleteCart($data);
        return $response;
    }

    function rentmy_update_cart($data)
    {
        $view_update_cart = new RentMy_Cart();
        $response = $view_update_cart->viewCart();
        return $response;
    }

    function rentmy_update_cart_item($data)
    {
        $update_cart_item = new RentMy_Cart();
        $response = $update_cart_item->updateCart($data);
        return $response;
    }

    function rentmy_apply_coupon($data)
    {
        $apply_coupon = new RentMy_Cart();
        $response = $apply_coupon->applyCoupon($data);
        return $response;
    }

    function get_price_value($data)
    {
        $get_price_value = new RentMy_Products();
        $response = $get_price_value->get_price_value($data);
        return $response;
    }
    function get_package_value($data)
    {
        $get_price_value = new RentMy_Products();
        $response = $get_price_value->get_package_value($data);
        return $response;
    }
    function get_cart_availability($data)
    {
        $get_available_products = new RentMy_Cart();
        $response = $get_available_products->getCartAvailability($data);
        return $response;
    }

    function get_exact_duration($data)
    {
        $get_exact_duration = new RentMy_Products();
        $response = $get_exact_duration->getExactDuration($data['start_date']);
        return $response;
    }

    function get_dates_from_duration($data)
    {
        $get_dates_from_duration = new RentMy_Products();
        $response = $get_dates_from_duration->getDatesFromDuration($data);
        return $response;
    }

    function get_dates_price_duration($data)
    {
        $get_dates_price_duration = new RentMy_Products();
        $response = $get_dates_price_duration->getDatesPriceDuration($data);
        return $response;
    }


    function rentmy_checkout_information()
    {
        $data = [];

        $checkout_info = new RentMy_Checkout();

        if ($_POST['step'] == 'info') {
            parse_str($_POST['data'], $data);
            $response = $checkout_info->saveInfo($data);
        } elseif ($_POST['step'] == 'fulfillment') {
            $data= $_POST['data'];
            $response = $checkout_info->saveFulfilment($data);
        } elseif ($_POST['step'] == 'payment') {
            $response = $checkout_info->savePayment($data);
        } else {
            wp_send_json([
                'status' => 'NOK',
                'message' => '',
            ]);
        }

        wp_send_json($response);
    }


    /**
     * $action_type = 'get_variant_chain' ->get change variant
     * $action_type = 'get_last_variant' -> get final product details of the chain
     *
     */
    function rentmy_options()
    {
        $data = $_POST;
        $action = $data['action_type'];
        switch ($action) {
            case 'get_variant_chain' :
                $params=['product_id'=> $data['data']['product_id'],'variant_id'=> $data['data']['variant_id'],'chain_id'=> $data['data']['chain_id']];
                $response=(new RentMy_Products())->get_product_variant_chain($params);
                break;
            case 'get_last_variant':
                $params=['product_id'=> $data['data']['product_id'],'variant_id'=> $data['data']['variant_id'],'chain_id'=> $data['data']['chain_id']];
                $response=(new RentMy_Products())->get_product_fromchain($params);
                if(!empty($response['prices'][0])){
                    foreach($response['prices'][0] as $key=>$prices){
                        $fPrice = '';
                        if($key == 'base' && !empty($prices)){
                            $fPrice="<h6>".$GLOBALS['RentMy']::currency($prices['price']). "</h6>";
                            $response['prices'][0][$key]['html']= $fPrice;
                        }else{

                           if ($key == 'fixed'){
                               $className = ($counter == 0) ? 'first-element-selection' : '';
                               $checked = ($counter == 0) ? 'checked' : '';
                               if (!empty($price['duration'])){
                                   $fPrice="<h6>".$GLOBALS['RentMy']::currency($prices['price']). " for ". $price['duration']. " ".$price['label']."</h6>";
                               }else{
                                   $fPrice="<h6>".$GLOBALS['RentMy']::currency($prices['price']) ."</h6>";
                               }

                               $priceOptions='<label class="radio-container">';
                               $priceOptions.= '<input type="radio" '. $checked .' data-label="' . $prices['label'] . '" data-duration="' . $price['duration'] . '" data-price="' . $price['price'] . '" data-price_id="' . $price['id'] . '" class="'. $className .'" name="rental-price" value="'.$price['id'].'">';
                               $priceOptions.= $GLOBALS['RentMy']::currency($price['price']) . ' /' . $prices['duration'] . ' ' . $price['label'];
                               $priceOptions.= '<span class="checkmark"></span></label>';
                               $response['prices'][0][$key]['price_options']= $priceOptions;
                               $response['prices'][0][$key]['html']= $fPrice;
                           }else{
                               $counter = 0;
                               foreach($prices as $i=>$price){

                                   $className = ($counter == 0) ? 'first-element-selection' : '';
                                   $checked = ($counter == 0) ? 'checked' : '';
                                   if (!empty($price['duration'])){
                                       $fPrice="<h6>".$GLOBALS['RentMy']::currency($price['price']). " for ". $price['duration']. " ".$price['label']."</h6>";
                                   }else{
                                       $fPrice="<h6>".$GLOBALS['RentMy']::currency($price['price']) ."</h6>";
                                   }

                                   $priceOptions='<label class="radio-container">';
                                   $priceOptions.= '<input type="radio" '. $checked .' data-label="' . $price['label'] . '" data-duration="' . $price['duration'] . '" data-price="' . $price['price'] . '" data-price_id="' . $price['id'] . '" class="'. $className .'" name="rental-price" value="'.$price['id'].'">';
                                   $priceOptions.= $GLOBALS['RentMy']::currency($price['price']) . ' /' . $price['duration'] . ' ' . $price['label'];
                                   $priceOptions.= '<span class="checkmark"></span></label>';
                                   $response['prices'][0][$key][$i]['price_options']= $priceOptions;
                                   $response['prices'][0][$key][$i]['html']= $fPrice;

                                   $counter++;
                               }
                           }
                        }
                    }
                }
                break;
            case 'get_config':
                $response=get_option('rentmy_config');
                break;
            case 'get_store_contents':
                $response= (new RentMy_Config())->store_contents();
                break;
            case 'add_to_cart':
                $response=$this->rentmy_add_to_cart($data['data']);
                break;
            case 'add_to_cart_package':
                $add_to_cart = new RentMy_Cart();
                $response = $add_to_cart->addPackageToCart($data['data']);
                break;
            case 'update_package_availability':
                $productObj=new RentMy_Products();
                $response=$productObj->check_package_availability($data['data']);
                break;
            case 'remove_from_cart':
                $response=$this->rentmy_remove_from_cart($data['data']);
                break;
            case 'update_cart':
                $response=$this->rentmy_update_cart($data['data']);
                break;
            case 'apply_coupon':
                $response=$this->rentmy_apply_coupon($data['data']);
                break;
            case 'update_cart_item':
                $response=$this->rentmy_update_cart_item($data['data']);
                break;
            case 'get_price_value':
                $response=$this->get_price_value($data['data']);
                break;
            case 'get_package_value':
                $response=$this->get_package_value($data['data']);
                break;
            case 'get_cart_availability':
                $response = $this->get_cart_availability($data['data']);
                break;
            case 'get_delivery_cost':
                $response=(new RentMy_Checkout())->getDeliveryCost($data['data']);
                break;

            case 'get_multi_store_cost':
                $response=(new RentMy_Checkout())->estimateMultiStoreDeliveryCharge($data['data']);
                break;

            case 'upload_media':
                $file['file'] = $_FILES['file'];
                $file['type'] = $data['type'];
                $response=(new RentMy_Checkout())->uploadMedia($file);
                break;
            case 'get_shipping_methods':
                parse_str($data['data'], $requestData);
                $response=(new RentMy_Checkout())->getShippingList($requestData);
                break;
            case 'get_shipping_methods_by_kn':
                $response=(new RentMy_Checkout())->getShippingListByKN($data['data']);
                break;
            case 'add_shipping_to_cart':
                $response=(new RentMy_Checkout())->addShippingToCarts($data['data']);
                break;
            case 'submit_order':
                $response=(new RentMy_Checkout())->doCheckout($data['data']);
                break;
            case 'submit_single_checkout':
                $response=(new RentMy_Checkout())->singleSubmitCheckout($data['data']);
                break;
            case 'free_shipping':
                $response=(new RentMy_Checkout())->checkFreeShipping();
                break;
            case 'get_exact_duration':
                $response=$this->get_exact_duration($data['data']);
                break;
            case 'get_dates_from_duration':
                $response=$this->get_dates_from_duration($data['data']);
                break;
            case 'get_dates_price_duration':
                $response=$this->get_dates_price_duration($data['data']);
                break;
            case 'get_delivery_settings':
                $response=(new RentMy_Config())->getDeliverySettings();
                break;
            case 'customer_login':
                $response= (new RentMy_Customer())->login($data);
            break;
            case 'customer_register':
                $response= (new RentMy_Customer())->register($data);
            break;
            case 'add_new_address':
                unset($data['action']);
                unset($data['action_type']);
                $response= (new RentMy_Customer())->new_address($data);
            break;
            case 'get_customer_profile':
                $response= (new RentMy_Customer())->profile();
            break;
            case 'get_customer_address':
                $response= (new RentMy_Customer())->address();
            break;
            case 'get_customer_orders':
                unset($data['action']);
                unset($data['action_type']);
                $response= (new RentMy_Customer())->orders($data);
            break;
            case 'get_customer_orders_details':
                unset($data['action']);
                unset($data['action_type']);
                $response= (new RentMy_Customer())->order_details($data);
            break;
            case 'update_customer_info':
                unset($data['action']);
                unset($data['action_type']);
                $response= (new RentMy_Customer())->updateProfile($data);
            break;
            case 'add_customer_address':
                unset($data['action']);
                unset($data['action_type']);
                $response= (new RentMy_Customer())->new_address($data);
            break;
            case 'edit_customer_address':
                unset($data['action']);
                unset($data['action_type']);
                $response= (new RentMy_Customer())->updated_address($data);
            break;
            case 'delete_customer_address':
                unset($data['action']);
                unset($data['action_type']);
                $response= (new RentMy_Customer())->delete_address($data);
            break;
            case 'cancel_order':
                unset($data['action']);
                unset($data['action_type']);
                $response= (new RentMy_Customer())->change_order_status($data);
            break;
            case 'change_customer_password':
                unset($data['action']);
                unset($data['action_type']);
                $response= (new RentMy_Customer())->change_password($data);
            break;
            case 'forgot_customer_password':
                unset($data['action']);
                unset($data['action_type']);
                $response= (new RentMy_Customer())->forgot_password($data);
            break;
            case 'reset_customer_password':
                unset($data['action']);
                unset($data['action_type']);
                $response= (new RentMy_Customer())->reset_password($data);
            break;
            case 'change_customer_avatar':
                $params['file'] = $_FILES['file'];
                $response= (new RentMy_Customer())->change_avatar($params);
            break;
            case 'get_order_status':
                unset($data['action']);
                unset($data['action_type']);
                $response= (new RentMy_Order())->status($params);
            break;
            
            case 'additional_charges':
                unset($data['action']);
                unset($data['action_type']);
                $cart_id = !empty($_SESSION['rentmy_cart_token'])?$_SESSION['rentmy_cart_token']:'';
                $response= (new RentMy_Config())->additional_charges($cart_id);
            break;
            case 'add_additional_charges_to_cart':
                unset($data['action']);
                unset($data['action_type']);
                $response= (new RentMy_Cart())->add_additional_charges($data);
            break;
            case 'cart_additional_charges':
                unset($data['action']);
                unset($data['action_type']);
                $cart_id = !empty($_SESSION['rentmy_cart_token'])?$_SESSION['rentmy_cart_token']:'';

                $response= (new RentMy_Config())->additional_charges($cart_id);
            break;
            case 'delete_order_additional_charges':
                unset($data['action']);
                unset($data['action_type']);
                $response= (new RentMy_Order())->deleteAdditionalCharge($data);
            break;
            case 'get_cart_data':
                unset($data['action']);
                unset($data['action_type']);
                $response= (new RentMy_Cart())->viewCart($data);
            break;
            case 'customer_login_check':
                $response = [
                    "is_login"=> is_user_logged_in()
                ];
            break;

            case 'get_payment_intent':
                unset($data['action']);
                unset($data['action_type']);
                $response= (new RentMy_Checkout())->getPaymentIntent($data);
                break;

            case 'send_message':
                unset($data['action']);
                unset($data['action_type']);
                $response= (new RentMy_Customer())->sendUserMessage($data);
                break;

            case 'transafe_iframe_attr':
                unset($data['action']);
                unset($data['action_type']);
                $response= (new RentMy_Checkout())->getTrasafeIframeAttr($data);
                break;
        }

        wp_send_json($response);
    }

    function rentmy_order_details(){
        $cart_token = null;
        if (empty($_SESSION['rentmy_cart_token'])):
            $response = null;
        else:
            $cart_token = $_SESSION['rentmy_cart_token'];
            $rentmy_cart = new RentMy_Cart();
            $response = $rentmy_cart->viewCart();
            $response = $response['data'];
        endif;
        $html = $this->rentmy_order_summary_ajax_template($response);
        die($html);
    }

    function rentmy_cart_topbar($data){
        $cart_token = null;
        if (empty($_SESSION['rentmy_cart_token'])):
            $response = null;
        else:
            $data = $_POST;

            $cart_token = $_SESSION['rentmy_cart_token'];
            $rentmy_cart = new RentMy_Cart();
            $response = $rentmy_cart->viewCart($data);
            $response = $response['data'];
        endif;
        wp_send_json($response);
    }

    function rentmy_order_summary_ajax_template($rent_my_cart_details){

        $html = '';

        if(empty($rent_my_cart_details)):
            $html .= '<div class="rentmy-plugin-manincontent">
    <div class="col-md-12 text-center">
        <h3 style="margin-top: 70px;margin-bottom: 70px !important;">Your cart is empty</h3>
        <div class="procces-contiue-checkout" style="margin-bottom: 70px;">
            <a style="background-color: #1786c5;padding: 20px; border-radius: 17px; color: #fff;   font-size: 16px;margin-bottom: 75px;" href="'.getRentMyParmalink('rentmy.page_url.products_list').'"> Continue
                    Shopping </a>
        </div>rentmy.page_url.products_list
    </div>
</div>';
            return $html;
        endif;

        if (!empty($rent_my_cart_details['cart_items'])):

            $html .='<form id="rentmy-cart-form-sidebar" action="" method="post">
                <table class="cart" cellspacing="0">
                    <tbody>';

                    foreach ($rent_my_cart_details['cart_items'] as $cart_items):
                        $html .='<tr class="rentmy-cart-form__cart-item" id="cart-row-'.$cart_items['id'].'">
                            <td width="20%">
                                <img class="rentmy-responsive-image" src="'.$GLOBALS['RentMy']::imageLink($cart_items['product_id'], $cart_items['product']['images'][0]['image_small'], 'small').'"
                                     alt="">
                            </td>
                            <td width="80%">
                                <h5>'.$cart_items['product']['name'].'</h5>
                                <h6>Price: '.$GLOBALS['RentMy']::currency($cart_items['price']).' Qty: '.$cart_items['quantity'].'</h6>
                            </td>
                        </tr>';
                    endforeach;

                    $html .='</tbody>
                </table>
                <div class="table-responsive">
                    <table class="table cart">
                        <tbody>
                        <tr>
                            <td> Subtotal</td>
                            <td>
                                <span class="cart_p"><b>'.$GLOBALS['RentMy']::currency($rent_my_cart_details['sub_total'], 'pre', 'rentmy-cart-sub_total', 'post').'</b></span>
                            </td>
                        </tr>
                        <tr>
                            <td> Shipping Charge</td>
                            <td>
                                <small class="cart_p"> Calculated in the next step</small>
                            </td>
                        </tr>
                        <tr>
                            <td> Discount</td>
                            <td>
                                <span class="cart_p">'.$GLOBALS['RentMy']::currency($rent_my_cart_details['total_discount'], 'pre', 'rentmy-cart-total_discount', 'post').'</span>
                            </td>
                        </tr>
                        <tr>
                            <td> Tax</td>
                            <td>
                                <span class="cart_p">'.$GLOBALS['RentMy']::currency($rent_my_cart_details['tax'], 'pre', 'rentmy-cart-tax', 'post').'</span>
                            </td>
                        </tr>
                        <tr>
                            <td> Delivery Tax</td>
                            <td>
                                <small class="cart_p"> Calculated in the next step</small>
                            </td>
                        </tr>
                        <tr>
                            <td> Deposit Amount</td>
                            <td>
                                <span class="cart_p">'.$GLOBALS['RentMy']::currency($rent_my_cart_details['deposit_amount'], 'pre', 'rentmy-cart-deposit_amount', 'post').'</span>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <h5>Total</h5></td>
                            <td>
                                <h5>
                                    <span class="cart_p">'.$GLOBALS['RentMy']::currency($rent_my_cart_details['total'], 'pre', 'rentmy-cart-total', 'post').'</span>
                                </h5></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </form>';
        else:
            $html .= '<div class="rentmy-plugin-manincontent">
    <div class="col-md-12 text-center">
        <h3 style="margin-top: 70px;margin-bottom: 70px !important;">Your cart is empty</h3>
        <div class="procces-contiue-checkout" style="margin-bottom: 70px;">
            <a style="background-color: #1786c5;padding: 20px; border-radius: 17px; color: #fff;   font-size: 16px;margin-bottom: 75px;" href="' . getRentMyParmalink("rentmy.page_url.products_list") . '"> Continue
                    Shopping </a>
        </div>
    </div>
</div>';
        endif;

        return $html;
    }
}
