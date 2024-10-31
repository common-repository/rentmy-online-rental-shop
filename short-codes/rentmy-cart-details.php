<?php
//short code for product details of a product
function rent_my_cart_details_shortcode()
{

    ob_start();

    if( !isset($_GET['add-to-cart']) ) {
        $permalink = getRentMyParmalink("rentmy.page_url.cart");
        $separator = strpos( $permalink, '?' ) !== false ? '&' : '?';
        $tokenParam = ( isset($_GET['token']) ) ? '&token=' . $_GET['token'] : '';
        wp_redirect( $permalink . $separator . 'add-to-cart=true' . $tokenParam );
    }

    if (isset($_GET['token']) && !empty($_GET['token'])) {
        $response = (new RentMy_Cart())->viewCart(['token' => $_GET['token']]);
        if (!empty($response['data']['cart_items'])) {
            $_SESSION['rentmy_cart_token'] = $_GET['token'];
        }
    }

    $cart_token = null;
    if (empty($_SESSION['rentmy_cart_token'])) :
        echo '<div class="rentmy-plugin-manincontent">
    <div class="col-md-12 text-center">
        <h3 style="margin-top: 70px;margin-bottom: 70px !important;">Your cart is empty</h3>
        <div class="procces-contiue-checkout" style="margin-bottom: 70px;">
            <a style="background-color: #1786c5;padding: 20px; border-radius: 17px; color: #fff;   font-size: 16px;margin-bottom: 75px;" href="' . get_option("rentmy.page_url.products_list") . '"> Continue
                    Shopping </a>
        </div>
    </div>
</div>';
        return;
    else :
        $cart_token = $_SESSION['rentmy_cart_token'];
    endif;

    $rentmy_cart = new RentMy_Cart();
    $response = $rentmy_cart->viewCart(['token' => $cart_token]);
    $cart_reated_product = $rentmy_cart->get_related_products_cart($cart_token);
    $GLOBALS['cart_related_product'] = !empty($cart_reated_product['result']['data']) ? $cart_reated_product['result']['data'] : null;

    $rentmy_config = new RentMy_Config();
    $store_content = $rentmy_config->store_contents();
    $store_config = $rentmy_config->store_config();
    $GLOBALS['store_config'] = get_option('rentmy_config');
    if (!empty($store_content)) {
        $GLOBALS['store_text'] = $store_content[0]['contents'];
        $GLOBALS['cart_labels'] = $store_content[0]['contents']['cart'];
    }
    if (!empty($response['data'])) :
        $dataSet = $response['data'];
        $rent_my_cart_details = $dataSet;
        rentmy_cart_details_template($rent_my_cart_details);
        return ob_get_clean();
    else :
        echo !empty($response['message']) ? '<span class="rentmy-errore-msg">' . $response['message'] . '</span>' : '';
        echo !empty($response['error']) ? '<span class="rentmy-errore-msg">' . $response['error'] . '</span>' : '';
        return ob_get_clean();
    endif;
}

add_shortcode('rentmy-cart-details', 'rent_my_cart_details_shortcode');

function rentmy_cart_details_template($rent_my_cart_details)
{

$isCartContainRecurring = (new RentMy())->isCartEnduring($rent_my_cart_details);

?>
    <script>
        var isCartContainRecurring = <?php echo $isCartContainRecurring?1:0; ?>;
    </script>
    <?php if (!empty($rent_my_cart_details['cart_items'])) : ?>

        <?php $cart_related_product = $GLOBALS['cart_related_product']; ?>
        <?php if (!empty($cart_related_product)) : ?>
            <div class="rentmy-plugin-manincontent">
                <div class='rentmy-product-list cart-related-producst-list'>
                    <div class="related-product-title">
                        <h4>Add-on Products</h4>
                    </div>
                    <div class='products'>
                        <?php foreach ($cart_related_product as $related) : ?>
                            <div class="product-grid">
                                <div class="product-grid-inner text-center">
                                    <div class="product-grid-img">
                                        <img class="img-fluid" src="<?php echo $GLOBALS['RentMy']::imageLink($related['id'], $related['images'][0]['image_small'], 'list'); ?>">
                                        <?php $detailsLink = (isset($related['type']) && $related['type'] == 2)?getRentMyParmalink("rentmy.page_url.package_details"):getRentMyParmalink("rentmy.page_url.product_details");?>
                                        <a href="<?php echo $detailsLink . '?uid=' . $related['uuid']; ?>">
                                            <div class="product-overley">
                                            </div>
                                        </a>
                                    </div>
                                    <div class="product-grid-body">
                                        <div class="product-name">
                                            <a href="<?php echo $detailsLink . '?uid=' . $related['uuid']; ?>">
                                                <h4><?php echo $related['name']; ?></h4>
                                            </a>
                                        </div>
                                        <?php
                                        $priceTypes = getRentalTypes($related['prices']);
                                        $prices = getPrices($related['prices']);
                                        $generic_prices = empty($related['price']) ? $related['prices'] : $related['price']; ?>
                                        <?php if (in_array('rent', $priceTypes)) { ?>
                                            <span class="price">Starting at <?php echo $GLOBALS['RentMy']::currency($prices['rent'][0]['price'], 'pre', 'amount', 'post') . (!empty($prices['rent'][0]['duration']) ? ' for ' . $prices['rent'][0]['duration'] : '') . ' ' . (!empty($prices['rent'][0]['label']) ? $prices['rent'][0]['label'] : ''); ?></span>
                                        <?php } elseif (in_array('fixed', $priceTypes)) { ?>
                                            <span class="price">Starting at <?php echo $GLOBALS['RentMy']::currency($prices['rent'][0]['price'], 'pre', 'amount', 'post'); ?></span>
                                        <?php } else { ?>
                                            <span class="price">Buy now for <?php echo $GLOBALS['RentMy']::currency($prices['base']['price'], 'pre', 'amount', 'post'); ?></span>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="rentmy-plugin-manincontent">
            <form id="rentmy-cart-form" class="rentmy-cart-form" action="" method="post">

                <?php if (!empty($rent_my_cart_details['rent_start'])) : ?>
                    <p>
                        <!-- show this by default.. it is from the cart added product -->
                        <label class="date-range-selection-default"> <?php echo $GLOBALS['store_text']['cart']['rent_date'] ?? 'Rental Dates'; ?>
                            <span></span>
                            <i class="fa fa-edit edit-icon date-range-selection-change"></i>
                        </label>

                        <!-- on the edit click show below forms -->
                        <label class="date-range-selection-active" style="display: none;">
                            <input style="width: 440px;" autocomplete="off" class="daterange" id="rm-date" type="text" name="rm-date" data-min_date="<?php echo !empty($rent_my_cart_details['min_rent_start']) ? $rent_my_cart_details['min_rent_start'] : ''; ?>" data-start_date="<?php echo date('m-d-Y h:i A', strtotime($rent_my_cart_details['rent_start'])); ?>" data-end_date="<?php echo date('m-d-Y h:i A', strtotime($rent_my_cart_details['rent_end'])); ?>" value="<?php echo date('m-d-Y h:i a', strtotime($rent_my_cart_details['rent_start'])) . '-' . date('m-d-Y h:i a', strtotime($rent_my_cart_details['rent_end'])); ?>" />

                            <button onclick="return false;" class="button theme-btn cancel-btn date-range-selection-cancel">
                                <?php echo $GLOBALS['store_text']['cart']['btn_cancel'] ?? 'Cancel'; ?>
                            </button>
                        </label>
                    </p>
                <?php endif; ?>


                <table class="cart" cellspacing="0">
                    <thead>
                        <tr>
                            <th class="product-remove">&nbsp;</th>
                            <th class="product-thumbnail">
                                <?php esc_html_e(' ', 'rentmy'); ?>
                            </th>
                            <th class="product-name">
                                <?php echo $GLOBALS['cart_labels']['th_product']; ?>
                            </th>
                            <th class="product-price">
                                <?php echo $GLOBALS['cart_labels']['th_unit_price']; ?>
                            </th>
                            <th class="product-quantity">
                                <?php echo $GLOBALS['cart_labels']['th_quantity']; ?>
                            </th>
                            <th class="product-subtotal">
                                <?php echo $GLOBALS['cart_labels']['th_subtotal']; ?>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rent_my_cart_details['cart_items'] as $cart_items) : ?>
                            <tr class="rentmy-cart-form__cart-item" id="cart-row-<?php echo $cart_items['id']; ?>">
                                <td class="product-remove">
                                    <a href="javascript:void(0)" data-cart_item_id="<?php echo $cart_items['id']; ?>" data-product_id="<?php echo $cart_items['product_id']; ?>" class="remove remove_from_cart" aria-label="Remove this item">×</a>
                                </td>
                                <td class="product-thumbnail">
                                    <img src="<?php echo $GLOBALS['RentMy']::imageLink($cart_items['product_id'], $cart_items['product']['images'][0]['image_small'], 'small'); ?>" alt="">
                                </td>
                                <td class="product-name" data-title="<?php esc_attr_e('Product', 'rentmy'); ?>">

                                    <p><?php echo $cart_items['product']['name']; ?></p>
                                    <?php
                                    if (!empty($cart_items['product']['variant_chain']) && strpos($cart_items['product']['variant_chain'], 'Unassigned') === false ) {
                                        if (!empty($cart_items['product']['variant_chain'])) {
                                            echo "<small>{$cart_items['product']['variant_chain']}</small>";
                                        }
                                    } ?>

                                    <!--                    cusom fields-->
                                    <span class="rentmy-cart-product-option-<?php echo $cart_items['id']; ?>">
                                        <ul style="padding: 0">
                                            <?php if (!empty($cart_items['cart_product_options'])) {
                                                foreach ($cart_items['cart_product_options'] as $key => $fields) { ?>
                                                    <?php
                                                    $values = '';
                                                    //
                                                    if (!empty($fields['options'])) {
                                                        foreach ($fields['options'] as $key => $option) {
                                                            $com = (count($fields['options']) - 1 == $key) ? '' : ', ';
                                                            $values .= $option['label'] . ': ' . $option['value'] . $com;
                                                        }
                                                    }
                                                    ?>
                                                    <li style="list-style: none" id="rm-option-name-row-<?php echo $fields['id'] ?>">
                                                        <?php echo $values; ?><br>
                                                    </li>
                                            <?php }
                                            } ?>
                                        </ul>
                                    </span>
                                    <!--                    cusom fields end-->

                                </td>
                                <td align="right" class="product-price rentmy-cart-row-price-<?php echo $cart_items['id']; ?>" data-title="<?php esc_attr_e('Price', 'rentmy'); ?>">

                                    <?php if (!empty($cart_items['cart_product_options'])) { ?>
                                        <p></p>
                                        <?php foreach ($cart_items['cart_product_options'] as $key => $fields) { ?>
                                            <li style="list-style: none" id="rm-option-price-row-<?php echo $fields['id'] ?>">
                                                <?php echo $GLOBALS['RentMy']->currency(abs($fields['price'])); ?><br>
                                            </li>
                                        <?php }
                                    } else { ?>
                                        <p><?php echo $GLOBALS['RentMy']::currency($cart_items['price']); ?></p>
                                    <?php } ?>
                                </td>


                                <?php if ($cart_items['product_type'] == 1 || $cart_items['product_type'] == '1') { ?>

                                    <td align="right" class="product-quantity" data-title="<?php esc_attr_e('Quantity', 'rentmy'); ?>">
                                        <!--                                        <div class="quantity clearfix">-->
                                        <!--                                            <span data-cart_item_price="--><?php //echo $cart_items['price']; 
                                                                                                                        ?>
                                        <!--" data-increment="0" data-cart_item_id="--><?php //echo $cart_items['id']; 
                                                                                        ?>
                                        <!--" class="cart-minus btn btn-sm btn-dark no-m rentmy_item_quantity_update">-</span>-->
                                        <!--                                            <span class="cart-qunt btn btn-sm no-m rentmy-cart-row-quantity---><?php //echo $cart_items['id']; 
                                                                                                                                                            ?>
                                        <!--">--><?php //echo $cart_items['quantity']; 
                                                    ?>
                                        <!--</span>-->
                                        <!--                                            <span data-cart_item_price="--><?php //echo $cart_items['price']; 
                                                                                                                        ?>
                                        <!--" data-increment="1" data-cart_item_id="--><?php //echo $cart_items['id']; 
                                                                                        ?>
                                        <!--" class="cart-plus btn btn-sm btn-dark no-m rentmy_item_quantity_update">+</span>-->
                                        <!--                                        </div>-->

                                        <?php if (!empty($cart_items['cart_product_options'])) { ?>
                                            <div class="mt-2 "></div>
                                            <ul style="padding: 0">
                                                <?php foreach ($cart_items['cart_product_options'] as $key => $fields) { ?>
                                                    <li style="list-style: none" id="rm-option-quantity-row-<?php echo $fields['id'] ?>">

                                                        <div class="quantity clearfix ">
                                                            <span data-cart_item_price="<?php echo $cart_items['price']; ?>" data-option_id="<?php echo $fields['id']; ?>" data-increment="0" data-cart_item_id="<?php echo $cart_items['id']; ?>" class="cart-minus btn btn-sm btn-dark no-m rentmy_item_quantity_update">-</span>
                                                            <span class="cart-qunt btn btn-sm no-m rentmy-cart-row-quantity-<?php echo $fields['id']; ?>"><?php echo $fields['quantity']; ?></span>
                                                            <span data-cart_item_price="<?php echo $cart_items['price']; ?>" data-option_id="<?php echo $fields['id']; ?>" data-increment="1" data-cart_item_id="<?php echo $cart_items['id']; ?>" class="cart-plus btn btn-sm btn-dark no-m rentmy_item_quantity_update">+</span>
                                                        </div>

                                                    </li>
                                                <?php } ?>
                                            </ul> <?php } else { ?>

                                            <div class="quantity clearfix">
                                                <span data-cart_item_price="<?php echo $cart_items['price']; ?>" data-increment="0" data-cart_item_id="<?php echo $cart_items['id']; ?>" class="cart-minus btn btn-sm btn-dark no-m rentmy_item_quantity_update">-</span>
                                                <span class="cart-qunt btn btn-sm no-m rentmy-cart-row-quantity-<?php echo $cart_items['id']; ?>"><?php echo $cart_items['quantity']; ?></span>
                                                <span data-cart_item_price="<?php echo $cart_items['price']; ?>" data-increment="1" data-cart_item_id="<?php echo $cart_items['id']; ?>" class="cart-plus btn btn-sm btn-dark no-m rentmy_item_quantity_update">+</span>
                                            </div>

                                        <?php } ?>
                                    </td>

                                <?php } else { ?>

                                    <td align="right" class="product-quantity" data-title="<?php esc_attr_e('Quantity', 'rentmy'); ?>">
                                        <div class="quantity clearfix">
                                            <span class="cart-qunt btn btn-sm no-m"><?php echo $cart_items['quantity']; ?></span>
                                        </div>
                                    </td>

                                <?php } ?>

                                <td align="right" class="product-subtotal" data-title="<?php esc_attr_e('Subtotal', 'rentmy'); ?>">
                                    <?php
                                    $discount = $cart_items['discount'];
                                    //                                            if ((float)$discount['off_amount'] > 0 || (float)$discount['coupon_amount'] > 0){
                                    //                                    
                                    ?>
                                    <!--                                                <p>--><?php //echo $GLOBALS['RentMy']::currency($cart_items['substantive_price'], 'pre', 'rentmy-cart-row-sub_total-' . $cart_items['id'], 'post'); 
                                                                                                ?>
                                    <!--</p>-->
                                    <!--                                      --><?php
                                                                                    //                                            }else{
                                                                                    //                                            
                                                                                    ?>
                                    <!--                                                <p>--><?php //echo $GLOBALS['RentMy']::currency($cart_items['sub_total'], 'pre', 'rentmy-cart-row-sub_total-' . $cart_items['id'], 'post'); 
                                                                                                ?>
                                    <!--</p>-->
                                    <!--                                                --><?php //}
                                                                                            ?>

                                    <?php

                                    //
                                    //                                    if ((float)$discount['discount_sub_total'] > (float)$discount['coupon_sub_total']) {
                                    //                                        if ($discount['discount_sub_total'] > 0) { 
                                    ?>
                                    <!--                                            <p>--><?php //echo $GLOBALS['RentMy']::currency($cart_items['discount_sub_total'], 'pre', 'rentmy-cart-row-sub_total-' . $cart_items['id'], 'post'); 
                                                                                            ?>
                                    <!--                                            (--><?php //echo $GLOBALS['RentMy']::currency($cart_items['off_amount'], 'pre', 'rentmy-cart-row-sub_total-' . $cart_items['id'], 'post'); 
                                                                                        ?>
                                    <!-- Off)</p>-->
                                    <!--                                       --><?php //}
                                                                                    //                                        if ($discount['coupon_sub_total'] > 0) { 
                                                                                    ?>
                                    <!--                                            <p>--><?php //echo $GLOBALS['RentMy']::currency($cart_items['coupon_sub_total'], 'pre', 'rentmy-cart-row-sub_total-' . $cart_items['id'], 'post'); 
                                                                                            ?>
                                    <!--                                                (--><?php //echo $GLOBALS['RentMy']::currency($cart_items['coupon_amount'], 'pre', 'rentmy-cart-row-sub_total-' . $cart_items['id'], 'post'); 
                                                                                            ?>
                                    <!-- Coupon Applied)</p>-->
                                    <!--                                        --><?php //}
                                                                                    //                                    }
                                                                                    //                                    if ((float)$discount['coupon_sub_total'] > (float)$discount['discount_sub_total']) {
                                                                                    //                                        if ($discount['coupon_sub_total'] > 0) { 
                                                                                    ?>
                                    <!--                                            <p>--><?php //echo $GLOBALS['RentMy']::currency($cart_items['coupon_sub_total'], 'pre', 'rentmy-cart-row-sub_total-' . $cart_items['id'], 'post'); 
                                                                                            ?>
                                    <!--                                                (--><?php //echo $GLOBALS['RentMy']::currency($cart_items['coupon_amount'], 'pre', 'rentmy-cart-row-sub_total-' . $cart_items['id'], 'post'); 
                                                                                            ?>
                                    <!-- Coupon Applied)</p>-->
                                    <!--                                        --><?php //}
                                                                                    //                                        if ($discount['coupon_sub_total'] > 0) { 
                                                                                    ?>
                                    <!--                                            $--><?php //echo $GLOBALS['RentMy']::currency($cart_items['coupon_sub_total'], 'pre', 'rentmy-cart-row-sub_total-' . $cart_items['id'], 'post'); 
                                                                                        ?>
                                    <!--                                                (--><?php //echo $GLOBALS['RentMy']::currency($cart_items['coupon_amount'], 'pre', 'rentmy-cart-row-sub_total-' . $cart_items['id'], 'post'); 
                                                                                            ?>
                                    <!-- Coupon Applied)-->
                                    <!--                                        --><?php //}
                                                                                    //                                    }

                                                                                    ?>
                                    <?php
                                    $discount_text = '';
                                    if ($discount['coupon_sub_total'] > 0) {
                                        $discount_text = '<p class="rentmy-cart-row-sub_total-' . $cart_items['id'] . '">' . $GLOBALS['RentMy']::currency($discount['coupon_sub_total'], 'pre', 'rentmy-cart-row-coupon-' . $cart_items['id'], 'post') .
                                            ' (' . $GLOBALS['RentMy']::currency($discount['coupon_amount'], 'pre', 'rentmy-cart-row-coupon-' . $cart_items['id'], 'post') . ' Coupon Applied)' . '</p>';
                                    }

                                    ?>
                                    <?php if ($discount_text != '') { ?>
                                        <p><?php echo $discount_text; ?></p>
                                    <?php } else { ?>
                                        <p class="rentmy-cart-row-sub_total-<?php echo $cart_items['id']; ?>"><?php echo $GLOBALS['RentMy']::currency($cart_items['sub_total'], 'pre', 'rentmy-cart-row-sub-total-' . $cart_items['id'], 'post'); ?></p>
                                    <?php } ?>
                                </td>
                            </tr>

                            <!-- addon items  -->
                            <?php if (!empty($cart_items['products'])) : foreach ($cart_items['products'] as $addon_products) : ?>
                                    <tr class="rentmy-cart-form__cart-item cart-addon-row-<?php echo $cart_items['id']; ?>">
                                        <td class="product-remove">

                                        </td>
                                        <td class="product-thumbnail">
                                            <img src="<?php echo $GLOBALS['RentMy']::imageLink($addon_products['id'], $addon_products['image'], 'small'); ?>" alt="">
                                        </td>
                                        <td class="product-name">
                                            <p>
                                                <?php echo $addon_products['name']; ?> (<?php echo (int)($addon_products['quantity'] / $cart_items['quantity']); ?>/each)
                                                <?php
                                                if (strpos($addon_products['variant_chain'], 'Unassigned') !== false) {
                                                } else {
                                                    echo '<br><small>' . $addon_products['variant_chain'] . '</small>';
                                                }
                                                ?>
                                            </p>
                                        </td>
                                        <td align="right" class="product-price">

                                        </td>
                                        <td align="right" class="product-quantity">
                                            <div class="quantity clearfix">
                                                <?php /*       <span id="addon-item-quantity-<?php echo $addon_products['id']; ?>-parent-<?php echo $cart_items['id']; ?>" class="cart-qunt btn btn-sm no-m"><?php echo $addon_products['quantity']; ?></span> */ ?>
                                            </div>
                                        </td>
                                    </tr>
                            <?php endforeach;
                            endif; ?>
                            <!-- addon cart items ends here -->

                        <?php endforeach; ?>
                        <!--                        <tr class="rentmy-cart-form__cart-item">-->
                        <!--                            <td class="product-price">&nbsp;</td>-->
                        <!--                            <td class="product-price">&nbsp;</td>-->
                        <!--                            <td class="product-price">&nbsp;</td>-->
                        <!--                            <td class="product-price">&nbsp;</td>-->
                        <!--                            <td class="product-price">-->
                        <!--                                --><?php //echo $GLOBALS['cart_labels']['lbl_total']; 
                                                                ?>
                        <!--:-->
                        <!--                            </td>-->
                        <!--                            <td class="product-price" data-title="--><?php //esc_attr_e('Total', 'rentmy'); 
                                                                                                    ?>
                        <!--">-->
                        <!--                                <p>--><?php //echo $GLOBALS['RentMy']::currency($rent_my_cart_details['total'], 'pre', 'rentmy-cart-sub_total', 'post'); 
                                                                    ?>
                        <!--</p>-->
                        <!--                            </td>-->
                        <!--                        </tr>-->
                    </tbody>
                </table>
                <div class="cart-checkout-area">
                    <div class="coupon-code">
                        <input type="text" class="coupon-text rentmy_coupon_text" placeholder="<?php echo $GLOBALS['cart_labels']['txt_coupon']; ?>">
                        <a class="coupon-btn checkout-button button alt rm-forward rentmy_coupon_btn" href="javascript:void(0)"><?php echo $GLOBALS['cart_labels']['btn_coupon']; ?></a>
                    </div>
                    <div class="procces-contiue-checkout">
                        <a class="rentmy-checkout checkout-button button alt rm-forward" href="<?php echo getRentMyParmalink('rentmy.page_url.products_list'); ?>"> <?php echo $GLOBALS['store_text']['cart']['btn_continue'] ?? 'Continue Shopping'; ?></a>
                    </div>
                </div>
                <div class="cart-summery-area">
                    <h4 class="pb-2 cart-total-title"><?php echo $GLOBALS['cart_labels']['lbl_cart_total'] ?? 'Cart Totals'; ?></h4>
                    <div class="table-responsive">
                        <table class="table cart">
                            <tbody>

                                <tr class="rentmy-item-discount" style="display: <?php echo ($rent_my_cart_details['off_amount'] > 0) ? '' : 'none'; ?>">
                                    <td> <?php echo $GLOBALS['cart_labels']['lbl_off_amount'] ?? 'Item Discount'; ?></td>
                                    <td>
                                        <span class="cart_p"><?php echo $GLOBALS['RentMy']::currency($rent_my_cart_details['off_amount'], 'pre', 'rentmy-cart-off_amount', 'post'); ?></span>
                                    </td>
                                </tr>

                                <tr class="rentmy-coupon-discount" style="display: <?php echo ($rent_my_cart_details['coupon_amount'] > 0) ? '' : 'none'; ?>">
                                    <td> <?php echo $GLOBALS['cart_labels']['lbl_coupon_discount'] ?? 'Coupon discount'; ?></td>
                                    <td>
                                        <span class="cart_p"><?php echo $GLOBALS['RentMy']::currency($rent_my_cart_details['coupon_amount'], 'pre', 'rentmy-cart-coupon_discount', 'post'); ?></span>
                                    </td>
                                </tr>


                                <?php if ($GLOBALS['cart_labels']['th_subtotal'] != '') { ?>
                                    <tr>
                                        <td> <?php echo $GLOBALS['cart_labels']['th_subtotal'] ?? 'Subtotal'; ?>
                                        </td>
                                        <td>
                                            <span class="cart_p"><?php echo $GLOBALS['RentMy']::currency($rent_my_cart_details['sub_total'], 'pre', 'rentmy-cart-sub_total', 'post'); ?></span>
                                            <?php
                                            if (isset($GLOBALS['store_config']['tax']['price_with_tax'])) {
                                                echo $GLOBALS['store_config']['tax']['price_with_tax'] == 1 ? '(inc. tax)' : '';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                <?php } ?>

                                <?php if ($rent_my_cart_details['additional_charge'] > 0) { ?>
                                    <tr>
                                        <td> <?php echo $GLOBALS['cart_labels']['lbl_additional_charge'] ?? 'Optional Services'; ?></td>
                                        <td>
                                            <span class="cart_p"><?php echo $GLOBALS['RentMy']::currency($rent_my_cart_details['additional_charge'], 'pre', 'rentmy-cart-total_discount', 'post'); ?></span>
                                        </td>
                                    </tr>
                                <?php } ?>

                                <?php if ($GLOBALS['cart_labels']['lbl_total_deposite'] != '') { ?>
                                    <tr>
                                        <td> <?php echo $GLOBALS['cart_labels']['lbl_total_deposite']; ?></td>
                                        <td>
                                            <span class="cart_p"> <?php echo $GLOBALS['RentMy']::currency($rent_my_cart_details['deposit_amount'], 'pre', 'rentmy-cart-deposit_amount', 'post'); ?></span>
                                        </td>
                                    </tr>
                                <?php } ?>

                                <?php if ($GLOBALS['cart_labels']['lbl_tax'] != '') { ?>
                                    <tr>
                                        <td> <?php echo $GLOBALS['cart_labels']['lbl_tax']; ?></td>
                                        <td>
                                            <?php if (!empty($rent_my_cart_details['tax']['total']) && $rent_my_cart_details['tax']['total'] > 0) { ?>
                                                <span class="cart_p"><?php echo $GLOBALS['RentMy']::currency($rent_my_cart_details['tax']['total'], 'pre', 'rentmy-cart-tax-total', 'post'); ?></span>
                                            <?php } else { ?>
                                                <small class="cart_p"> <?php echo $GLOBALS['cart_labels']['lbl_next_step'] ?? 'Calculated in the next step'; ?></small>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                <?php } ?>

                                <?php if ($GLOBALS['cart_labels']['lbl_shipping'] != '') { ?>
                                    <tr>
                                        <td> <?php echo $GLOBALS['cart_labels']['lbl_shipping'] ?? 'Shipping Charge'; ?></td>
                                        <td>
                                            <?php if ($rent_my_cart_details['delivery_charge'] > 0) { ?>
                                                <span class="cart_p"><?php echo $GLOBALS['RentMy']::currency($rent_my_cart_details['delivery_charge'], 'pre', 'rentmy-cart-delivery_charge', 'post'); ?></span>
                                            <?php } else { ?>
                                                <small class="cart_p"> <?php echo $GLOBALS['cart_labels']['lbl_next_step'] ?? 'Calculated in the next step'; ?></small>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                <?php } ?>

                                <?php if ($GLOBALS['cart_labels']['lbl_delivery_tax'] != '') { ?>
                                    <tr>
                                        <td> <?php echo $GLOBALS['cart_labels']['lbl_delivery_tax'] ?? 'Delivery Tax'; ?> </td>
                                        <td>
                                            <?php if ($rent_my_cart_details['delivery_tax'] > 0) { ?>
                                                <span class="cart_p"><?php echo $GLOBALS['RentMy']::currency($rent_my_cart_details['delivery_tax'], 'pre', 'rentmy-cart-delivery_tax', 'post'); ?></span>
                                            <?php } else { ?>
                                                <small class="cart_p"> <?php echo $GLOBALS['cart_labels']['lbl_next_step'] ?? 'Calculated in the next step'; ?></small>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                <?php } ?>

                                <?php if ($GLOBALS['cart_labels']['lbl_total'] != '') { ?>
                                    <tr>
                                        <td>
                                            <h5><?php echo $GLOBALS['cart_labels']['lbl_total']; ?></h5>
                                        </td>
                                        <td>
                                            <h5>
                                                <span class="cart_p"><?php echo $GLOBALS['RentMy']::currency($rent_my_cart_details['total'], 'pre', 'rentmy-cart-total', 'post'); ?></span>
                                            </h5>
                                            <?php
                                            if (isset($GLOBALS['store_config']['tax']['price_with_tax'])) {
                                                if ($GLOBALS['store_config']['tax']['price_with_tax'] == 1 && !empty($rent_my_cart_details['tax']['regular'])) { ?>

                                                    (<?php foreach ($rent_my_cart_details['tax']['regular'] as $tax) { ?>
                                                    <small>includes <?= $GLOBALS['RentMy']::currency($tax['total']) ?>(<?= $tax['rate'] ?>%) <?= $tax['name'] ?>,</small>
                                                    <?php  } ?>)

                                            <?php }
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="cart-checkout-btn-area">
                        <?php $separator = strpos( getRentMyParmalink('rentmy.page_url.checkout'), '?' ) !== false ? '&' : '?';  ?>
                        <a class="continue-btn  rentmy-checkout checkout-button button alt rm-forward" id="rentmy-checkout" name="rentmy-checkout" href="<?php echo getRentMyParmalink('rentmy.page_url.checkout') . $separator . 'step=info'; ?>"><?php echo $GLOBALS['cart_labels']['btn_checkout']; ?></a>
                    </div>
                </div>
            </form>
        </div>
    <?php else : ?>
        <div class="rentmy-plugin-manincontent">
            <div class="col-md-12 text-center">
                <h3 style="margin-top: 70px;margin-bottom: 70px !important;"> <?php echo $GLOBALS['store_text']['cart']['lbl_empty_cart']; ?></h3>
                <div class="procces-contiue-checkout" style="margin-bottom: 70px;">
                    <a style="background-color: #1786c5;padding: 20px; border-radius: 17px; color: #fff;   font-size: 16px;margin-bottom: 75px;" href="<?php echo getRentMyParmalink('rentmy.page_url.products_list'); ?>"> <?php echo $GLOBALS['store_text']['cart']['btn_continue'] ?? 'Continue Shopping'; ?> </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
<?php


}
