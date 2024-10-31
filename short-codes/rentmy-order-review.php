<?php
function rentmy_order_review_template()
{
    ob_start();
    $rentmy_config = new RentMy_Config();
    $store_content = $rentmy_config->store_contents();


    if(!empty($store_content)){
        $GLOBALS['payment_labels'] = $store_content[0]['contents']['checkout_payment'];
        $GLOBALS['cart_labels'] = $store_content[0]['contents']['cart'];
    }
    $order_id = get_query_var('order_id');
    if( empty($order_id) && !empty( $_GET['id']) ){
        $order_id = $_GET['id'];
    }

    if( empty($order_id) ) {
        echo '<span class="rentmy-plugin-manincontent"><span class="rentmy-errore-msg">Missing order_id param.</span></span>';
        return ob_get_clean();
    }


    $order_details = new RentMy_Order();
    $checkoutObj= new RentMy_Checkout();
    $view_order = $order_details->reviewOrderDetials($order_id);
    $order_data = !empty($view_order['data']) ? $view_order['data'] : null;
    $order_items = !empty($order_data['order_items']) ? $order_data['order_items'] : null;
    if(!empty($GLOBALS['checkout_labels'])){
        $checkout_label = $GLOBALS['checkout_labels'];
    }
    if(!empty($GLOBALS['payment_labels'])){
        $payment_label = $GLOBALS['payment_labels'];
    }

    $order_charges = $order_details->orderAdditionalCharges($order_data['id']);


    ?>

<div class="rentmy-plugin-manincontent">
    <div class="checkout-complete">

        <?php if (!empty($order_items)): ?>
<!--        <div class="text-center">-->
<!--            <h1>Order Summary</h1>-->
<!--        </div>-->
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
                        </td>
                        <td><?php echo $GLOBALS['RentMy']::currency($order['sub_total']); ?></td>
                        <td><?php echo $order['quantity']; ?></td>
                        <!--                            <td>--><?php //echo $order['sales_tax']; ?>
                        <!--%</td>-->
                        <td><?php echo $GLOBALS['RentMy']::currency($order['total']); ?></td>
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
                    <tr>
                        <td> <?php echo $GLOBALS['cart_labels']['lbl_discount'] ?? 'Discount' ; ?></td>
                        <td class="text-right">
                            <?php echo $GLOBALS['RentMy']::currency($order_data['total_discount']); ?></td>
                    </tr>

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
return ob_get_clean();
}

add_shortcode('rentmy-order-review', 'rentmy_order_review_template');