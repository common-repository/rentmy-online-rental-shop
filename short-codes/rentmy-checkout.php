<?php
//short code for checkout page
function rentmy_checkout_shortcode()
{
    ob_start();
    $checkout_step = 'info';
    if ( !empty($_GET['step']) ):
        $checkout_step = $_GET['step'];
    endif;

    $rentmy_checkout = new RentMy_Checkout();
    $rentmy_config = new RentMy_Config();
    $store_content = $rentmy_config->store_contents();

    $terms_condition = $rentmy_checkout->termsAndCondition();
    if(!empty($terms_condition)){
        $GLOBALS['terms_condition'] = $terms_condition['data'];
    }
    if(!empty($store_content)){
        $GLOBALS['labels'] = $store_content[0]['contents'];
        $GLOBALS['checkout_labels'] = $store_content[0]['contents']['checkout_info'];
        $GLOBALS['payment_labels'] = $store_content[0]['contents']['checkout_payment'];
        $GLOBALS['signature'] = isset($store_content[0]['contents']['confg']['signature']) ? $store_content[0]['contents']['confg']['signature'] : false;
        $GLOBALS['cart_labels'] = $store_content[0]['contents']['cart'];
        $GLOBALS['others'] = $store_content[0]['contents']['others'];
    }

    try {
       // $GLOBALS['rm_configs'] = (new RentMy_Config())->store_config();
        $GLOBALS['rm_configs'] = $store_content[0]['contents']['confg'];
        //print_r("<pre>");print_r(  $store_content[0]['contents']['confg']);print_r("</pre>");
        $GLOBALS['rm_cart'] = (new RentMy_Cart())->viewCart();
    } catch (\Exception $e) {

    }

    if($checkout_step != 'complete-order') { // get from get option and check also
        $checkout_step = 'single-page-checkout'; // determine from get option
    }

    if ($checkout_step == 'single-page-checkout'):

        $configObj = new RentMy_Config();
        $GLOBALS['rm_countries'] = $configObj->countries();
        $GLOBALS['rm_delivery_settings'] = $configObj->getDeliverySettings();
        $GLOBALS['rm_locations'] = $configObj->getLocationList();
        $GLOBALS['rm_custom_fields'] = $rentmy_checkout->getCustomFields();
        $GLOBALS['rm_payment_gateways'] = $configObj->getPaymentGateWays();
        rentmy_checkout_single_page_template();
        return ob_get_clean();

    elseif ($checkout_step == 'info'):
        $GLOBALS['rm_countries'] = (new RentMy_Config())->countries();
        $GLOBALS['rm_custom_fields'] = $rentmy_checkout->getCustomFields();
        rentmy_checkout_info_template();
        return ob_get_clean();
    elseif ($checkout_step == 'fulfillment'):
        $configObj=new RentMy_Config();
        $GLOBALS['rm_countries'] = $configObj->countries();
        $GLOBALS['rm_delivery_settings']=$configObj->getDeliverySettings();
        $GLOBALS['rm_locations']=$configObj->getLocationList();
        rentmy_checkout_fulfillment_template();
        return ob_get_clean();
    elseif ($checkout_step == 'payment'):
        $configObj=new RentMy_Config();
        $GLOBALS['rm_payment_gateways']=$configObj->getPaymentGateWays();
        rentmy_checkout_payment_template();
        return ob_get_clean();
    elseif ($checkout_step == 'complete-order'):
        rentmy_checkout_complete_template();
        return ob_get_clean();
    else:
        wp_redirect(getRentMyParmalink('rentmy.page_url.checkout'). '?step=info');
        return ob_get_clean();
    endif;
}

add_shortcode('rentmy-checkout', 'rentmy_checkout_shortcode');
