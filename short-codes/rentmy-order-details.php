<?php
function rentmy_order_details_shortcode($parameter)
{
    
    $rentmy_config = new RentMy_Config();
    $store_content = $rentmy_config->store_contents();
    if(!empty($store_content)){
        $GLOBALS['payment_labels'] = $store_content[0]['contents']['checkout_payment'];
        $GLOBALS['cart_labels'] = $store_content[0]['contents']['cart'];
    }
    $order_details = new RentMy_Order();
    $checkoutObj= new RentMy_Checkout();

    // --- get param from shortcode
    $UID_or_ID = shortcode_atts([
        'order_uid' => null,
        'order_id' => null
    ], $parameter);   
       
    if($UID_or_ID['order_uid'] != null && strlen($UID_or_ID['order_uid'])){
        $view_order = $order_details->viewOrderDetails($UID_or_ID['order_uid']); // with order UID EX. e0d0ec08723011ec96ec02caec14a78c
    } elseif ($UID_or_ID['order_id'] != null && strlen($UID_or_ID['order_id'])) {
        $view_order = $order_details->reviewOrderDetials($UID_or_ID['order_id']); // with order ID EX. 24807
    }else{
        echo '<h3 style="color:red;">Order ID or UID not found!</h3><br>';
    }

    $order_data = !empty($view_order['data']) ? $view_order['data'] : null;
    $order_items = !empty($order_data['order_items']) ? $order_data['order_items'] : null;
    if(!empty($GLOBALS['checkout_labels'])){
        $checkout_label = $GLOBALS['checkout_labels'];
    }
    if(!empty($GLOBALS['payment_labels'])){
        $payment_label = $GLOBALS['payment_labels'];
    }

    $order_charges = $order_details->orderAdditionalCharges($order_data['id']);
    $reference = '';
    if (isset($_GET['reference'])){
        $reference = $_GET['reference'];
    }
    $storeID = get_option('rentmy_storeId');

    if (($reference != '') && ((int)$storeID == 534)){
        $amount = isset($_GET['amount'])?$_GET['amount']:$order_data['total'];
        echo do_shortcode('[affiliate_conversion_script amount="'.$amount.'" reference="'.$reference.'" description="Self Serve Rental" context="Rentmy" status="pending" type="sale"]');
 }

    ?>

<div class="rentmy-plugin-manincontent">
    <div class="checkout-complete">
        <?php /* <ul class="rentmy-progressbar">
                <li>
                    <a class="btn btn-circle">1</a><br>
                    <?php echo $checkout_label['step_one']; ?>
        </li>
        <li>
            <a class="btn btn-circle">2</a><br>
            <?php echo $checkout_label['step_two']; ?>
        </li>
        <li>
            <a class="btn btn-circle">3</a><br>
            <?php echo $checkout_label['step_three']; ?>
        </li>
        <li class="active">
            <a class="btn btn-circle">4</a><br>
            <?php echo $checkout_label['step_four']; ?>
        </li>
        </ul>
        */?>
        <?php if (!empty($order_data)): ?>
        <span
            class="rentmy-success-msg"><?php echo ($order_data['type']===1) ? $payment_label['lbl_thanks_confirm_order'] : $payment_label['lbl_thanks_confirm_quote'];?></span>
        <!-- <span class="rentmy-errore-msg">Errore Message</span> -->
        <div class="rentmy-table rentmy-cart-form">
            <table width="100%">

                <tr>
                    <th></th>
                    <th><?php echo $GLOBALS['cart_labels']['th_product']; ?></th>
                    <th><?php echo $GLOBALS['cart_labels']['th_unit_price']; ?></th>
                    <th> <?php echo $GLOBALS['cart_labels']['th_quantity']; ?></th>
                    <th><?php echo $GLOBALS['cart_labels']['th_subtotal']; ?></th>
                </tr>
                <tbody>
                    <?php foreach ($order_items as $order): ?>
                    <tr>
                        <td>
                            <?php if (!empty($order['product']['images'][0]['image_small'])): ?>
                            <img width="150"
                                src="<?php echo $GLOBALS['RentMy']::imageLink($order['product']['id'], $order['product']['images'][0]['image_small'], 'list'); ?>"
                                alt="">
                            <?php endif; ?>
                        </td>
                        <td>
                            <p><?php echo $order['product']['name']; ?></p>

                            <!--                    cusom fields-->
                            <span class="rentmy-cart-product-option-<?php echo $order['id'];?>">
                                         <ul style="padding: 0">
                                        <?php if (!empty($order['order_product_options'])){
                                            foreach($order['order_product_options'] as $key=>$fields){?>
                                                <?php
                                                $values = '';
//
                                                if (!empty($fields['options'])) {
                                                    foreach ($fields['options'] as $key=>$option) {
                                                        $com =(count($fields['options'])-1 == $key)?'':', ';
                                                        $values .= $option['label'] . ': '. $option['value'] .$com;
                                                    }
                                                }
                                                ?>
                                                <li style="list-style: none" id="rm-option-name-row-<?php echo $fields['id']?>">
                                            <?php echo $values; ?><br>
                                            </li>
                                            <?php } } ?>
                                         </ul>
                                    </span>
                        </td>

                        <td>
                            <?php if (!empty($order['order_product_options'])){ ?>
                                <p></p>
                                <?php foreach($order['order_product_options'] as $key=>$fields){?>
                                    <li style="list-style: none" id="rm-option-price-row-<?php echo $fields['id']?>">
                                        <?php echo $GLOBALS['RentMy']->currency(abs($fields['price'])); ?><br>
                                    </li>
                                <?php } }else{ ?>
                                <p><?php echo $GLOBALS['RentMy']::currency($order['price']); ?></p>
                            <?php } ?>

                        </td>
                        <td>
                            <?php if (!empty($order['order_product_options'])){ ?>
                                <div class="mt-2 "></div>
                                <ul style="padding: 0">
                                    <?php foreach($order['order_product_options'] as $key=>$fields){?>
                                        <li style="list-style: none" id="rm-option-quantity-row-<?php echo $fields['id']?>">
                                            <span><?php echo $fields['quantity']; ?></span>
                                        </li>
                                    <?php }?></ul> <?php }else{ ?>

                                <div class="quantity clearfix">
                                    <li style="list-style: none">
                                    <span><?php echo $order['quantity']; ?></span>
                                    </li>
                            <?php } ?>
                        </td>
                        <!--                            <td>--><?php //echo $order['sales_tax']; ?>
                        <!--%</td>-->
<!--                        <td>--><?php //echo $GLOBALS['RentMy']::currency($order['sub_total']); ?><!--</td>-->
                        <td>
                        <?php
                        $discount = $order['discount'];
                        $discount_text = '';
                        if ($discount['coupon_sub_total'] > 0) {
                            $discount_text = '<p class="rentmy-cart-row-sub_total-'.$order['id'].'">' . $GLOBALS['RentMy']::currency($discount['coupon_sub_total'], 'pre', 'rentmy-cart-row-coupon-' . $order['id'], 'post') .
                                ' ('.$GLOBALS['RentMy']::currency($discount['coupon_amount'], 'pre', 'rentmy-cart-row-coupon-' . $order['id'], 'post') . ' Coupon Applied)' . '</p>';
                        }

                        ?>
                        <?php if ($discount_text != ''){?>
                            <p><?php echo $discount_text; ?></p>
                        <?php }else{ ?>
                            <p class="rentmy-cart-row-sub_total-<?php echo $order['id'];?>"><?php echo $GLOBALS['RentMy']::currency($order['sub_total'], 'pre', 'rentmy-cart-row-sub-total-' . $order['id'], 'post'); ?></p>
                        <?php }?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div>
            <?php if(count($order_charges['data']) > 0){?>
            <table class="table">
                <thead>
                    <tr >
                        <th  colspan="2"><?php echo $GLOBALS['cart_labels']['lbl_additional_charge']  ; ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($order_charges['data'] as $charge){?>
                    <tr>
                        <td style="font-weight: normal; "><?php echo $charge['note'];?></td>
                        <td style="font-weight: normal; "><?php echo $GLOBALS['RentMy']::currency($charge['amount']); ?></td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
                    <?php } ?>
             </div>
            <div class="cart-summery-area">
                <h5 class="cart-total-title"><?php echo $GLOBALS['cart_labels']['lbl_total']; ?></h5>
                <table width="100%">
                    <tbody>
                    <?php if ($order_data['coupon_amount'] > 0){?>
                    <tr class="rentmy-coupon-discount">
                        <td> <?php echo $GLOBALS['cart_labels']['lbl_coupon_discount'] ?? 'Coupon discount'; ?></td>
                        <td  class="text-right">
                           <?php echo $GLOBALS['RentMy']::currency($order_data['coupon_amount'], 'pre', 'rentmy-cart-coupon_discount', 'post'); ?>
                        </td>
                    </tr>
                    <?php } ?>
                    <tr>
                        <td><?php echo $GLOBALS['cart_labels']['th_subtotal'] ?? 'Subtotal' ; ?></td>
                        <td class="text-right">
                            <?php echo $GLOBALS['RentMy']::currency($order_data['sub_total']); ?>
                        </td>
                    </tr>

                        <tr>
                            <td><?php echo $GLOBALS['cart_labels']['lbl_additional_charge'] ?? 'Additional Charges' ; ?>
                            </td>
                            <td class="text-right">
                                <?php echo $GLOBALS['RentMy']::currency($order_data['additional_charge']); ?>
                            </td>
                        </tr>
<!--                    <tr>-->
<!--                        <td> --><?php //echo $GLOBALS['cart_labels']['lbl_discount'] ?? 'Discount' ; ?><!--</td>-->
<!--                        <td class="text-right">-->
<!--                            --><?php //echo $GLOBALS['RentMy']::currency($order_data['total_discount']); ?><!--</td>-->
<!--                    </tr>-->

                    <tr>
                        <td> <?php echo !empty($GLOBALS['cart_labels']['lbl_total_deposite'])?$GLOBALS['cart_labels']['lbl_total_deposite']:'Deposit'; ?></td>
                        <td class="text-right">
                            <?php echo $GLOBALS['RentMy']::currency($order_data['total_deposit']); ?></td>
                    </tr>



                    <?php if (empty($order_data['tax']['regular'])){?>
                        <tr>
                            <td> <?php echo $GLOBALS['cart_labels']['lbl_tax']; ?></td>
                            <td class="text-right"><?php echo $GLOBALS['RentMy']::currency($order_data['tax']['total']??0); ?></td>
                        </tr>
                        <?php } ?>

                        <?php if (!empty($order_data['tax']['regular'])){?>
                        <?php foreach ($order_data['tax']['regular'] as $rate){?>
                        <tr>
                            <td> <?php echo $rate['name']; ?></td>
                            <td class="text-right"><?php echo $GLOBALS['RentMy']::currency($rate['total']??0); ?></td>
                        </tr>
                        <?php } } ?>
                    <tr>
                        <td> <?php echo $GLOBALS['cart_labels']['lbl_shipping'] ?? 'Shipping Charge' ; ?></td>
                        <td class="text-right">
                            <?php echo !empty($order_data['delivery_charge']) ? $GLOBALS['RentMy']::currency($order_data['delivery_charge']) : $GLOBALS['RentMy']::currency(0); ?>
                        </td>
                    </tr>
                    <tr>
                        <td> <?php echo $GLOBALS['cart_labels']['lbl_delivery_tax'] ?? 'Delivery tax' ; ?></td>
                        <td class="text-right">
                            <?php echo !empty($order_data['delivery_tax']) ? $GLOBALS['RentMy']::currency($order_data['delivery_tax']) : $GLOBALS['RentMy']::currency(0); ?>
                        </td>
                    </tr>
                        <tr>
                            <td>
                                <h4><?php echo $GLOBALS['cart_labels']['lbl_total']; ?></h4>
                            </td>
                            <td class="text-right">
                                <h4><?php echo $GLOBALS['RentMy']::currency($order_data['total']); ?></h4>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div class="download-receipt">
                    <a
                        href="<?php echo RENTMY_API_URL.'/pages/pdf?order_id='. $order_data['id']; ?>"><?php echo $GLOBALS['others']['btn_download'] ?? 'Download Receipt';?></a>
                </div>
            </div>
        </div>
    </div>

    <?php else: ?>
    <span class="rentmy-errore-msg">No order found yet</span>
    <?php endif; ?>
</div>
<?php
}

add_shortcode('rentmy-order-details', 'rentmy_order_details_shortcode');