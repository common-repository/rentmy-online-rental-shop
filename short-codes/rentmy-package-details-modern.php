<?php
//short code for product details of a product
function rentmy_package_details_modern_shortcode($params)
{

    wp_enqueue_style('rentmy-product-details-css', plugins_url('assets/css/rentmy-product-details.css', RENTMY_PLUGIN_FILE));

    wp_enqueue_script('rentmy-product-details-js', plugins_url('assets/js/rentmy-product-details.js', RENTMY_PLUGIN_FILE), array('jquery'), 'b1', true);

    ob_start();
    $product_id = '';

    $view_type = 'uid';
    if (!empty($_GET['uid'])):
        $product_id = trim($_GET['uid']);
    elseif (!empty($params['product_id'])):
        $product_id = $params['product_id'];
        $view_type='id';
    elseif (!empty($params['uid'])):
        $product_id = $params['uid'];
    else:
        $product_id = '';
    endif;

    if (empty($product_id)) :
        echo '<span class="rentmy-errore-msg">Invalid Package</span>';
        return ob_get_clean();
        return;
    endif;

    $check_cart = (new RentMy_Cart())->viewCart();
    $recurring_data = !empty($check_cart['data']['options']['recurring'])?$check_cart['data']['options']['recurring']:[];
    $rentmy_products = new RentMy_Products();

    $rentmy_config = new RentMy_Config();
    $store_content = $rentmy_config->store_contents();

    if (!empty($store_content)) {
        $GLOBALS['store_text'] = $store_content[0]['contents'];
        $GLOBALS['store_config'] = $store_content[0]['contents']['confg'];
    }
    $cart_params = [
        'token' => !empty($_SESSION['rentmy_cart_token']) ? $_SESSION['rentmy_cart_token'] : null,
        'start_date' => !empty($check_cart['data']['rent_start']) ? $check_cart['data']['rent_start'] : null,
        'end_date' => !empty($check_cart['data']['rent_end']) ? $check_cart['data']['rent_end'] : null,
        'view_type'=> $view_type
    ];

    $response = $rentmy_products->package_details($product_id, $cart_params);

    if (!empty($response['data'])) :
        $dataSet = $response['data'];
        $rent_my_product_details = $dataSet;

        $addon_products = $rentmy_products->get_addon_products($rent_my_product_details['id']);
        $related_products = $rentmy_products->get_related_products($rent_my_product_details['id']);
        $rent_my_product_details['addon'] = !empty($addon_products['data']) ? $addon_products : null;
        $rent_my_product_details['related_products'] = !empty($related_products['data']) ? $related_products['data'] : null;

        $rent_my_product_details['rent_dates'] = !empty($check_cart['data']) ? ['rent_start' => $check_cart['data']['rent_start'], 'rent_end' => $check_cart['data']['rent_end']] : [];
        $rent_my_product_details['delivery_settings'] = $rentmy_config->getDeliverySettings();
        $rent_my_product_details['multi_store_delivery_config'] = $rentmy_config->multiStoreDeliveryConfig();
        $rent_my_product_details['custom_fields'] = $rentmy_products->getCustomFields($rent_my_product_details['id']);
        $rent_my_product_details['recurring'] = $recurring_data;
        rentmy_package_details_modern_template($rent_my_product_details);
        return ob_get_clean();
    else :
        echo !empty($response['message']) ? '<span class="rentmy-errore-msg">' . $response['message'] . '</span>' : '';
        return ob_get_clean();
    endif;
}

add_shortcode('rentmy-package-details', 'rentmy_package_details_modern_shortcode');

function rentmy_package_details_modern_template($rent_my_product_details)
{
    $store_config = [];
    if (!empty($_SESSION['rentmy_config'])) {
        $store_config = $_SESSION['rentmy_config'];
    }
    $isRecurringAdded = !empty($rent_my_product_details['recurring']);
    $isEnduring = (!empty($GLOBALS['store_config']['arb']['active']) && in_array($GLOBALS['store_config']['arb']['store_active'], ['before_rental', 'after_rental']) && !empty($rent_my_product_details['enduring_rental']));
    $cartWithoutRecurring = !($isRecurringAdded && $isEnduring);
?>
    <script>
        var config_labels = <?php echo json_encode($GLOBALS['store_text'], true); ?>;
        var rentmy_store_id = <?php echo get_option('rentmy_storeId'); ?>;
        var cart_token = "<?php echo $_SESSION['rentmy_cart_token']; ?>";
        var isEnduring = <?php echo $isEnduring?1:0 ?>;
    </script>
    <div class="rentmy-plugin-manincontent">
        <div class="rentmy-product-details">
            <div class="">
                <div class="images">
                    <?php if (!empty($rent_my_product_details['images'])):
                        array_multisort(array_column($rent_my_product_details['images'], 'status'), SORT_DESC, $rent_my_product_details['images']);
                        ?>

                        <div class="product-view-area">
                            <div class="image-list-area">
                                <ul class="image-list">
                                    <?php $rm_counter = 1;
                                    foreach ($rent_my_product_details['images'] as $thumb_image): ?>
                                        <li class="image-item">
                                            <a class="view-image <?php echo $rm_counter == 1 ? 'active-viewimg' : ''; ?>"
                                               href="javascript:void(0)">
                                                <img
                                                        data-targetsource="<?php echo $GLOBALS['RentMy']::imageLink($rent_my_product_details['id'], $thumb_image['image_large'], 'list'); ?>"
                                                        src="<?php echo $GLOBALS['RentMy']::imageLink($rent_my_product_details['id'], $thumb_image['image_small'], 'list'); ?>">
                                            </a>
                                        </li>
                                        <?php $rm_counter++; endforeach; ?>
                                </ul>
                            </div>
                            <div class="product-view-image">
                                <?php foreach ($rent_my_product_details['images'] as $thumb_image): ?>
                                    <img class="active"
                                         src="<?php echo $GLOBALS['RentMy']::imageLink($rent_my_product_details['id'], $thumb_image['image_large'], 'list'); ?>">
                                    <?php break; endforeach; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <img src="<?php echo $GLOBALS['RentMy']::imageLink($rent_my_product_details['id'], $rent_my_product_details['images'][0]['image_large'], 'list'); ?>">
                    <?php endif; ?>
                </div>
                <div class="details">
                    <h1 class="product_title"><?php echo $rent_my_product_details['name']; ?></h1>
                    <?php $priceTypes = getRentalTypes($rent_my_product_details['price']);
                    $prices = getPrices($rent_my_product_details['price']);

                    $isRecurrngLabel = false;
                    $pricingData = $prices['rent'][0];
                    if (!empty($rent_my_product_details['recurring'])){
                        foreach ($prices['rent'] as $price){
                            if ($price['duration_type'] == $rent_my_product_details['recurring']['duration_type']){
                                $pricingData = $price;
                                $isRecurrngLabel = true;
                                break;
                            }
                        }
                    }

                    ?>

                    <div class="price">
                        <div class="buy" style="display: none;">
                            <h6><?php echo !empty($prices['base']['price']) ? $GLOBALS['RentMy']::currency($prices['base']['price'], 'pre', 'amount', 'post') : ''; ?></h6>
                            <input readonly type="hidden" class="rentmy-base-buy-price" id="rentmy-base-buy-price" value="<?php echo !empty($prices['base']['price']) ? $prices['base']['price'] : 0; ?>">
                        </div>

<!--                        --><?php //if ($GLOBALS['store_config']['datetime']['exact_start_date'] == 1 || $GLOBALS['store_config']['datetime']['exact_start_date'] == '1') { ?>
                         <?php if (!empty($rent_my_product_details['exact_date'])){?>
                            <div class="rent" style="display: none;">
                                <?php if (!empty($rent_my_product_details['rental_price'])) : ?>
                                    <h6><?php echo $GLOBALS['RentMy']::currency($rent_my_product_details['rental_price'], 'pre', 'amount', 'post'); ?></h6>
                                    <input readonly type="hidden" class="rentmy-base-rent-price" id="rentmy-base-rent-price" value="<?php echo !empty($rent_my_product_details['rental_price']) ? $rent_my_product_details['rental_price'] : 0; ?>">
                                <?php endif; ?>
                            </div>

                        <?php } else { ?>

                            <div class="rent" style="display: none;">
                                <?php if (!empty($rent_my_product_details['rental_price'])) : ?>
                                    <h6><?php echo $GLOBALS['RentMy']::currency($rent_my_product_details['rental_price'], 'pre', 'amount', 'post'); ?></h6>
                                    <input readonly type="hidden" class="rentmy-base-rent-price" id="rentmy-base-rent-price" value="<?php echo !empty($rent_my_product_details['rental_price']) ? $rent_my_product_details['rental_price'] : 0; ?>">
                                <?php else : ?>

                                    <?php if (empty($rent_my_product_details['prices'][0]['fixed'])) : ?>
                                        <?php $for = !empty($GLOBALS['store_text']['others']['product_list_for']) ? ' ' . $GLOBALS['store_text']['others']['product_list_for'] . ' ' : ' for '; ?>
                                        <h6><?php echo $GLOBALS['RentMy']::currency($prices['rent'][0]['price'], 'pre', 'amount', 'post') . ' for ' . $prices['rent'][0]['duration'] . ' ' . $prices['rent'][0]['label']; ?></h6>
                                        <input readonly type="hidden" class="rentmy-base-rent-price" id="rentmy-base-rent-price" value="<?php echo !empty($prices['rent'][0]['price']) ? $prices['rent'][0]['price'] : 0; ?>">
                                    <?php else : ?>
                                        <h6><?php echo $GLOBALS['RentMy']::currency($prices['rent'][0]['price'], 'pre', 'amount', 'post'); ?></h6>
                                        <input readonly type="hidden" class="rentmy-base-rent-price" id="rentmy-base-rent-price" value="<?php echo !empty($prices['rent'][0]['price']) ? $prices['rent'][0]['price'] : 0; ?>">
                                    <?php endif; ?>

                                <?php endif; ?>
                            </div>

                        <?php } ?>

                    </div>

                    <?php
                    $bothRentalTypeExist = in_array('base', $priceTypes) && in_array('rent', $priceTypes);
                    ?>

                    <div class="rental-type" style="<?= !$bothRentalTypeExist?'visibility: hidden;height:0px':'' ?>">
                        <div class="">
                            <label class="radio-container buy_input"
                                for="rental_type_buy" <?php if (!in_array('base', $priceTypes)) { ?> style="display:none;" <?php } ?> >
                                <input type="radio" <?php if (in_array('base', $priceTypes)) { ?> checked="checked" <?php } ?>
                                    id="rental_type_buy" name="rental_type" value="buy">
                                <?php echo $GLOBALS['store_text']['product_details']['buy'] ?? 'Buy';?>
                                <span class="checkmark"></span>
                            </label>

                            <label class="switch">
                              <input type="checkbox" id="togBtn" checked>
                              <div class="slider round" id="toggleswitch">
                                <span class="on"></span>
                                <span class="off"></span>
                              </div>
                            </label>

                                <label class="radio-container rent_input active"
                                for="rental_type_rent" <?php if (!in_array('rent', $priceTypes)) { ?> style="display:none;" <?php } ?>>
                                <input type="radio" <?php if (in_array('rent', $priceTypes)) { ?> checked="checked" <?php } ?>
                                    id="rental_type_rent" name="rental_type" value="rent">
                                <?php echo $GLOBALS['store_text']['product_details']['rent'] ?? 'Rent';?>
                                <span class="checkmark"></span>
                            </label>
                        </div>

                    </div>




<!--                    --><?php //if ($GLOBALS['store_config']['datetime']['exact_start_date'] != 1 || $GLOBALS['store_config']['datetime']['exact_start_date'] != '1') { ?>
                    <?php if (empty($rent_my_product_details['exact_date'])){ ?>
                        <?php if (!empty($rent_my_product_details['extact_durations']) && empty($rent_my_product_details['rent_dates']['rent_start'])): $exact_duration = $rent_my_product_details['extact_durations']; ?>
                            <div class="exact-date-wrapper" style="display: none">
                                <div class="date-range pt-4">
                                    <input type="hidden" id="is_exact_time" value="<?php echo !empty($rent_my_product_details['exact_time']);?>">
                                    <label><?php echo $GLOBALS['store_text']['product_details']['lbl_rental_start_date'] ?? 'Select Start Date:';?></label>
                                    <input autocomplete="off" class="single-date-range form-control"
                                        id="single-date-range" type="text"
                                        name="single-date-range"
                                        value="<?php echo date('m-d-Y'); ?>"/>
                                </div>
                                <div class="quantity pt-4">
                                    <div class="form-group">
                                        <label>Select Duration:</label>
                                        <select class="form-control rentmy-duration-component" name="duration"
                                                id="duration">
                                            <option value=""><?php echo !empty($GLOBALS['store_text']['product_details']['exact_select_duration_placeholder']) ? $GLOBALS['store_text']['product_details']['exact_select_duration_placeholder'] . ':' : '-Select-'; ?></option>
                                            <?php foreach ($exact_duration['durations'] as $duration): ?>
                                                <option data-times='<?php echo !empty($duration['times']) ? implode(',', $duration['times']) : ''; ?>'
                                                        data-type="<?php echo $duration['type']; ?>"
                                                        data-label="<?php echo $duration['label']; ?>"
                                                        value="<?php echo $duration['value']; ?>"><?php echo $duration['label']; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <?php if ($GLOBALS['store_config']['show_start_time'] == 1 || $GLOBALS['store_config']['show_start_time'] == '1'): ?>
                                    <div class="quantity pt-3">
                                        <div class="form-group">
                                            <label><?php echo !empty($GLOBALS['store_text']['product_details']['exact_select_start_time']) ? $GLOBALS['store_text']['product_details']['exact_select_start_time'] . ':' : 'Select Start time:'; ?> </label>
                                            <select class="form-control rentmy-duration-component" name="exact_time"
                                                    id="exact_time">
                                                <option value=""><?php echo !empty($GLOBALS['store_text']['product_details']['exact_select_start_time_placeholder']) ? $GLOBALS['store_text']['product_details']['exact_select_start_time_placeholder'] . ':' : '-Select-'; ?></option>
                                            </select>
                                        </div>
                                    </div>
                                <?php endif; ?>

                            </div>
                        <?php else: ?>



                            <div class="rm-rental-daterange">
                                <!-- <label><?php echo !empty($GLOBALS['store_text']['product_details']['rent_date']) ? $GLOBALS['store_text']['product_details']['rent_date'] . ':' : 'Rental date range:'; ?></label> -->

                                <?php if (empty($rent_my_product_details['rent_dates']['rent_start'])): ?>

                    <div class="show_earliest_start_date_box">
                        <label class="radio-container rent_input" for="rental_type_rent">
                            <input type="radio" class="rental_type_rent" name="start_date_type" value="Today">
                            <?php echo !empty($GLOBALS['store_text']['product_details']['lbl_today']) ? $GLOBALS['store_text']['product_details']['lbl_today'] : 'Today'; ?>
                            <span class="checkmark"></span>
                        </label>

                        <label class="radio-container rent_input" for="rental_type_rent">
                            <input type="radio" class="rental_type_rent" name="start_date_type" value="Tomorrow">
                            <?php echo !empty($GLOBALS['store_text']['product_details']['lbl_tomorrow']) ? $GLOBALS['store_text']['product_details']['lbl_tomorrow'] : 'Tomorrow'; ?>
                            <span class="checkmark"></span>
                        </label>
                    </div>

                    <div class="show_start_date_box">
                        <label class="radio-container rent_input" for="rental_type_rent">
                            <input type="radio" class="rental_type_rent" name="start_date_type" value="Custom">
                            <?php echo !empty($GLOBALS['store_text']['product_details']['start_date']) ? $GLOBALS['store_text']['product_details']['start_date'] : 'Pick Start Date:'; ?>
                            <span class="checkmark"></span>
                        </label>
                    </div>


<!--
                                    <input autocomplete="off" class="daterange" id="rm-date" type="text"
                                        name="rm-date"
                                        data-min_date="<?php echo !empty($prices['rent'][0]['min_date']) ? $prices['rent'][0]['min_date'] : ''; ?>"
                                        data-start_date="<?php echo !empty($prices['rent'][0]['rent_start']) ? date('m-d-Y h:i a', strtotime($prices['rent'][0]['rent_start'])) : ''; ?>"
                                        value="<?php echo date('m-d-Y h:i a', strtotime($prices['rent'][0]['rent_start'])) . '-' . date('m-d-Y h:i a', strtotime($prices['rent'][0]['rent_end'])); ?>"/>
-->

                    <?php
                    $value = 'Today';
                    if( !empty($prices['rent'][0]['rent_start']) ) $value = $prices['rent'][0]['rent_start'];
                    $value =  date('m-d-Y h:i a', strtotime($value) );

                    $dataStartDate = '';
                    if( !empty($prices['rent'][0]['rent_start']) ) $dataStartDate = date('m-d-Y h:i a', strtotime($prices['rent'][0]['rent_start']));

                    $dataEndDate = '';
                    if( !empty($prices['rent'][0]['rent_end']) ) $dataEndDate = date('m-d-Y h:i a', strtotime($prices['rent'][0]['rent_end']));

                    $dataMinDate = '';
                    if( !empty($prices['rent'][0]['min_date']) ) $dataMinDate = date('m-d-Y h:i a', strtotime($prices['rent'][0]['min_date']));
                    ?>

                    <input autocomplete="off" style="visibility: hidden" class="daterange" id="rm-date" type="text"
                        name="rm-date"
                        data-rent-start="<?php echo $dataStartDate; ?>"
                        data-rent-end="<?php echo $dataEndDate; ?>"
                        value="<?php echo $value; ?>"/>


                    <div class="rm-date-start-box">
                        <i class="fa fa-calendar"></i>
                        <input
                            readonly
                            autocomplete="off"
                            class="daterangeStart rm-date-start"
                            id="rm-date-start"
                            type="text"
                            name="rm-date"
                            data-min_date="<?php echo $dataMinDate ?>"
                            data-start_date="<?php echo $dataStartDate ?>"
                            value="<?php echo $value; ?>"
                        />
                        <span class="date-overlay"></span>
                    </div>


                    <!--<div><i class="fa fa-calendar"></i> <span id="rent-start-date-text"></span></div>-->


                    <br />


                                <?php else: $fixed_rent_dates = $rent_my_product_details['rent_dates']; ?>
                                    <?php $fixed_date_values = ($GLOBALS['store_config']['show_start_time']) ? date('m-d-Y h:i a', strtotime($fixed_rent_dates['rent_start'])) . '-' . date('m-d-Y h:i a', strtotime($fixed_rent_dates['rent_end'])) : date('m-d-Y', strtotime($fixed_rent_dates['rent_start'])) . '-' . date('m-d-Y', strtotime($fixed_rent_dates['rent_end'])) ?>
                                    <?php $fixed_rent_start_values = ($GLOBALS['store_config']['show_start_time']) ? date('m-d-Y h:i a', strtotime($fixed_rent_dates['rent_start'])) : date('m-d-Y', strtotime($fixed_rent_dates['rent_start'])); ?>
                                    <?php $fixed_rent_end_values = ($GLOBALS['store_config']['show_start_time']) ? date('m-d-Y h:i a', strtotime($fixed_rent_dates['rent_end'])) : date('m-d-Y', strtotime($fixed_rent_dates['rent_end'])) ?>

                                    <?php if ($cartWithoutRecurring){?>
                                        <input type="hidden" value="1" id="cartWithoutRecurring"
                                               data-rent-start="<?php echo $fixed_rent_start_values; ?>"
                                               data-rent-end="<?php echo $fixed_rent_end_values; ?>">
                                        <input autocomplete="off" style="visibility: hidden" class="daterange" id="rm-date" type="text"
                                               name="rm-date"
                                               data-rent-start="<?php echo $fixed_rent_start_values; ?>"
                                               data-rent-end="<?php echo $fixed_rent_end_values; ?>"
                                               value="<?php echo $fixed_date_values; ?>"/>
                                        <input autocomplete="off" disabled="true" type="text"
                                               name="rm-date"
                                               data-rent-start="<?php echo $fixed_rent_start_values; ?>"
                                               data-rent-end="<?php echo $fixed_rent_end_values; ?>"
                                               value="<?php echo $fixed_date_values; ?>"/>
                                    <?php }else{?>
                                        <input autocomplete="off" disabled="true" class="daterange" id="rm-date" type="text"
                                               name="rm-date"
                                               data-rent-start="<?php echo $fixed_rent_start_values; ?>"
                                               data-rent-end="<?php echo $fixed_rent_end_values; ?>"
                                               value="<?php echo $fixed_date_values; ?>"/>
                                    <?php } ?>

                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <?php } else { ?>

                        <input style="width: 440px;" autocomplete="off" id="exact-rent-start" type="hidden"
                            name="exact-rent-start"
                            value="<?php echo trim($rent_my_product_details['rent_start']); ?>"/>

                        <input style="width: 440px;" autocomplete="off" id="exact-rent-end" type="hidden"
                            name="exact-rent-end"
                            value="<?php echo trim($rent_my_product_details['rent_end']); ?>"/>

                        <?php } ?>








                    <?php /** Start Pricing Options  */ ?>

<!--                    --><?php //if ($GLOBALS['store_config']['datetime']['exact_start_date'] != 1 || $GLOBALS['store_config']['datetime']['exact_start_date'] != '1') { ?>
                    <?php if (empty($rent_my_product_details['exact_date'])){ ?>
                        <?php if (empty($rent_my_product_details['exact_time'])): ?>
                        <?php if (empty($rent_my_product_details['rent_dates']['rent_start']) || ($isEnduring && $cartWithoutRecurring)) : ?>

                        <div class="price-options" style="display: none;">
                            <?php
                            if (!$isEnduring){
                            ?>
                            <?php $counter = 0;
                            foreach ($prices['rent'] as $i => $rents) { ?>
                                <label class="radio-container <?php if ($i == 0) { ?> active <?php } ?>">
                                    <input type="radio" data-price="<?php echo $rents['price']; ?>" class="<?php echo $counter == 0 ? 'first-element-selection' : ''; ?>" data-duration="<?php echo $rents['duration']; ?>"
                                           data-label="<?php echo $GLOBALS['RentMy']->format_price_label($rents['duration'],  $rents['label']); ?>" name="rental-price" data-package="true" data-price_id="<?php echo $rents['id']; ?>" data-start_date="<?php echo date('m-d-Y h:i A', strtotime($rents['rent_start'])); ?>" data-end_date="<?php echo date('m-d-Y h:i A', strtotime($rents['rent_end'])); ?>" value="<?php echo $rents['id']; ?>" <?php if ($i == 0) { ?> checked <?php } ?>>
                                    <?php echo "1 " . $GLOBALS['RentMy']->format_price_label($rents['duration'],  $rents['label']) . "<br />" . $GLOBALS['RentMy']::currency($rents['price']) ; ?>
                                    <span class="checkmark"></span>
                                </label>
                            <?php $counter++;
                            } ?>
                            <?php } else{

                            // Enduring rentals
                            ?>
                                <?php $counter = 0; foreach ($rent_my_product_details['recurring_prices'] as $i => $rents) {

                                    ?>
                                    <label class="radio-container">
                                        <input type="radio" name="rental-price" class="<?php echo $counter == 0 ? 'first-element-selection' : ''; ?>"
                                               data-price_id="<?php echo $rents['id']; ?>"
                                               data-price="<?php echo $rents['price']; ?>"
                                               data-duration="<?php echo $rents['duration']??''; ?>"
                                               data-label="<?php echo $GLOBALS['RentMy']->format_price_label($rents['duration']??'',  $rents['label']??''); ?>"
                                               data-start_date="<?php echo date('m-d-Y h:i A', strtotime($rents['rent_start'])); ?>"
                                               data-end_date="<?php echo date('m-d-Y h:i A', strtotime($rents['rent_end'])); ?>"
                                               value="<?php echo $rents['id']; ?>" <?php if ($i == 0) { ?> checked <?php } ?> >

                                        <?php
                                        $perText = !empty($GLOBALS['store_text']['others']['product_list_per'])?$GLOBALS['store_text']['others']['product_list_per']:'per';
                                        $forText = !empty($GLOBALS['store_text']['others']['product_list_for'])?$GLOBALS['store_text']['others']['product_list_for']:'for';
                                        $label_for_per = $rents['duration']<=1?$perText: $forText . ' '.$rents['duration'];
                                        $duration_label = !empty($rents['duration'])?(!empty($GLOBALS['store_text']['product_details']['lbl_billing_at_a_rate_of'])?$GLOBALS['store_text']['product_details']['lbl_billing_at_a_rate_of']:'').'  ' . $GLOBALS['RentMy']::currency($rents['price']) .' '.$label_for_per .' '. $GLOBALS['RentMy']->format_price_label($rents['duration'],  $rents['label']):''; ?>

                                        <?php echo $duration_label; ?>
                                        <span class="checkmark"></span>
                                    </label>
                                    <?php $counter++; } ?>
                            <?php } ?>
                        </div>

                    <?php else : $fixed_rent_dates = $rent_my_product_details['rent_dates']; ?>

                        <div class="price-options" style="display: none;">
                            <?php

                            if (!$isEnduring){
                            ?>
                            <?php foreach ($prices['rent'] as $i => $rents) { ?>
                                <label class="radio-container">
                                    <i class="fa fa-arrow-right"></i>
                                    <?php echo $GLOBALS['RentMy']::currency($rents['price']) . '/' . $rents['duration'] . ' ' . $GLOBALS['RentMy']->format_price_label($rents['duration'],  $rents['label']); ?>
                                    <br>
                                </label>
                            <?php } ?>
                            <?php } else{?>
                                <?php foreach ($rent_my_product_details['recurring_prices'] as $i => $rents) { ?>
                                    <label class="radio-container">
                                        <i class="fa fa-arrow-right"></i>
                                        <?php
                                        $perText = !empty($GLOBALS['store_text']['others']['product_list_per'])?$GLOBALS['store_text']['others']['product_list_per']:'per';
                                        $forText = !empty($GLOBALS['store_text']['others']['product_list_for'])?$GLOBALS['store_text']['others']['product_list_for']:'for';
                                        $label_for_per = $rents['duration']<=1?$perText: $forText . ' '.$rents['duration'];
                                        $duration_label = !empty($rents['duration'])?(!empty($GLOBALS['store_text']['product_details']['lbl_billing_at_a_rate_of'])?$GLOBALS['store_text']['product_details']['lbl_billing_at_a_rate_of']:'').'  ' . $GLOBALS['RentMy']::currency($rents['price']) .' '.$label_for_per .' '. $GLOBALS['RentMy']->format_price_label($rents['duration'],  $rents['label']):''; ?>

                                        <?php echo $duration_label; ?>
                                        <br>
                                    </label>
                                <?php }  ?>

                                <?php if ($isRecurringAdded && !$isRecurrngLabel){?>
                                    <p class="text-danger">This product is not available for selected rental Payment.</p>
                                <?php }else{ ?>
                                    <input type="hidden" id="pricingOptionRecurring" value=<?= str_replace(" ", "%rentmy%", json_encode($pricingData))?>>
                                <?php } ?>

                            <?php } ?>
                        </div>

                    <?php endif; ?>
                        <?php endif; ?>
                    <?php } else { ?>

                    <div class="price-options" style="display: none;">
                        <h4>
                            Due
                            Date <?php echo date('m/d/Y', strtotime(trim($rent_my_product_details['rent_end']))); ?>
                        </h4>
                    </div>

                    <?php } ?>

                    <?php /** Custom Fields */ ?>
                    <div class="rentmy-custom-fields">
                        <?php if (!empty($rent_my_product_details['custom_fields'])){?>
                            <?php foreach ($rent_my_product_details['custom_fields'] as $fields){?>
                                <?php if ($fields['type'] == 'select' && !empty($fields['product_field_value'])){ ?>
                                    <div class="form-group">
                                        <label><?php echo $fields['label']; ?></label>
                                        <select name="customFields[]" class="form-control">
                                            <?php foreach ($fields['product_field_value'] as $field){?>
                                                <option value=<?php echo str_replace( ' ', '%rentmy%', json_encode($field, true))?>><?php echo $field['value'] . formatProductOptionPrice($field);?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                <?php } ?>

                            <?php } ?>

                        <?php } ?>
                    </div>

                    <?php /** Variant Set && Variants select  */ ?>
                    <div class="variants">
                        <?php
                        if (!empty($rent_my_product_details['variant_set_list']))
                            foreach ($rent_my_product_details['variant_set_list'] as $i => $variantSets) { ?>
                            <div class="form-group variantSets">
                                <label><?php echo $variantSets['name']; ?></label>
                                <select name="variantSets[]" data-index="<?php echo $i + 1; ?>" data-total="<?php echo count($rent_my_product_details['variant_set_list']); ?>" data-id="<?php echo $variantSets['id']; ?>" data-next-id="<?php echo $rent_my_product_details['variant_set_list'][$i + 1]['id']; ?>" data-prev-id="<?php echo $rent_my_product_details['variant_set_list'][$i - 1]['id']; ?>" id="variantSet_<?php echo $variantSets['id']; ?>">
                                    <?php foreach ($rent_my_product_details['variant_list'] as $variants) { ?>
                                        <?php if ($variants['variant_set_id'] == $variantSets['id']) { ?>
                                            <option value="<?php echo $variants['id']; ?>" <?php if ($variants['selected'] == 1) { ?> selected <?php }; ?>><?php echo $variants['name']; ?></option>
                                        <?php } ?>
                                    <?php } ?>
                                </select>
                            </div>
                        <?php } ?>
                    </div>
                    <?php /********* End Variant set  and variant selection */ ?>



                    <?php if (!empty($store_config['delivery']['multi_distance']['active']) && !empty($rent_my_product_details['multi_store_delivery_config']) && empty($rent_my_product_details['delivery_settings']['delivery_settings']['charge_by_zone'])){?>
                        <div class="rm-delivery-option">
                            <h6 class="mb-2 mt-0"><?php echo !empty($GLOBALS['store_text']['product_details']['lbl_delivery_option']) ? $GLOBALS['store_text']['product_details']['lbl_delivery_option'] . ':' : 'Select Delivery Option'; ?></h6>
                            <ul>
                                <?php foreach ($rent_my_product_details['multi_store_delivery_config'] as $config){?>
                                    <li class="" data-delivery_flow=<?= str_replace(' ', '%rentmy%', json_encode($config)); ?>><?= $config['name'] ?></li>
                                <?php } ?>
                            </ul>
                        </div>
                    <?php } ?>




                    <?php if( in_array('rent', $priceTypes) ): ?>

                        <div class="end-date-box">

                            <a class="pick-end-date" href="javascript:void(0)"><?php echo !empty($GLOBALS['store_text']['product_details']['rent_date']) ? $GLOBALS['store_text']['product_details']['end_date'] . ':' : 'Pick End Date:'; ?></a>
                            <br />

                            <?php
                            $value = 'Today';
                            if( !empty($prices['rent'][0]['rent_end']) ) $value = $prices['rent'][0]['rent_end'];
                            $value =  date('m-d-Y h:i a', strtotime($value) );

                            $dataStartDate = '';
                            if( !empty($prices['rent'][0]['rent_end']) ) $dataStartDate = date('m-d-Y h:i a', strtotime($prices['rent'][0]['rent_end']));

                            $dataMinDate = '';
                            if( !empty($prices['rent'][0]['min_date']) ) $dataMinDate = date('m-d-Y h:i a', strtotime($prices['rent'][0]['min_date']));
                            ?>

                            <div class="rm-date-end-box">
                                <i class="fa fa-calendar"></i>
                                <input
                                    readonly
                                    autocomplete="off"
                                    class="daterangeEnd rm-date-end"
                                    id="rm-date-end"
                                    type="text"
                                    name="rm-date-end"
                                    data-min_date="<?php echo $dataMinDate ?>"
                                    data-start_date="<?php echo $dataStartDate ?>"
                                    value="<?php echo $value; ?>"
                                    style="margin-top: 20px;"
                                />
                                <span class="date-overlay"></span>
                            </div>

                            <br />

                        </div>
                    <?php endif ?>




                    <div class="quantity">
                        <label><?php echo $GLOBALS['store_text']['product_details']['quantity'] ?? 'Quantity'; ?>
                            :</label>
                        <button type="button" class="decrease">-</button>
                        <input type="text" value="1" name="quantity" id="rm_quantity">
                        <button type="button" class="increase">+</button>
                    </div>
                    <div class="availability">
                        <span><?php echo $GLOBALS['store_text']['product_details']['available'] ?? 'Available'; ?> :
                            <span class="availability-count">
                                <?php echo $rent_my_product_details['term']; ?>
                            </span>
                        </span>
                    </div>
                    <span class="rentmy-unavailability-msg text-danger"></span>




                    <div class="package-items">
                        <h5><?php echo $GLOBALS['store_text']['product_details']['title_package_includes'] ?? 'Package Includes'; ?></h5>
                        <div style="padding: 10px;">
                            <div><?php echo $rent_my_product_details['package_content']; ?></div>
                            <ul>
                                <?php foreach ($rent_my_product_details['products'] as $packageItems) { ?>
                                    <li>
                                        <h6 data-id="<?php echo $packageItems['id']; ?>" data-quantity="<?php echo $packageItems['quantity']; ?>"><?php echo $packageItems['name'] . ' (' . $packageItems['quantity'] . ')'; ?></h6>

                                        <div <?php if (!empty($packageItems['variants'][0]['variant_chain']) && ($packageItems['variants'][0]['variant_chain'] == 'Unassigned: Unassigned')) { ?> style="display: none;" <?php } ?>>
                                            <select class="package_variant">
                                                <?php foreach ($packageItems['variants'] as $i => $pvariants) { ?>
                                                    <option value="<?php echo $pvariants['id']; ?>" <?php if ($i == 0) { ?> selected <?php } ?>><?php echo $pvariants['variant_chain']; ?> </option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </li>
                                <?php } ?>
                            </ul>
                        </div>
                    </div>




                   <div class="mt-2">
                       <button
                           <?php echo ($isRecurringAdded && !$isRecurrngLabel) ? 'disabled' : ''; ?>
                               type="button" class="add_to_cart_package_button button alt" name="rentmy-rent-item" value="ADD TO CART"> <?php echo $GLOBALS['store_text']['product_details']['add_to_cart'] ?? 'ADD TO CART'; ?><i style="display: none" class="loading fa fa-spinner fa-spin"></i>
                       </button>
                   </div>



                    <p class="description"><?php echo $rent_my_product_details['description']; ?></p>
                    <div class="hidden_variables">
                        <input type="hidden" id="rm_pd_product_id" value="<?php echo $rent_my_product_details['id']; ?>" />
                        <input type="hidden" id="rm_pd_product_uid" value="<?php echo $rent_my_product_details['uid']; ?>" />
                        <input type="hidden" id="rm_v_products_id" value="<?php echo $rent_my_product_details['variants_products_id']; ?>" />
                        <input type="hidden" id="rm_v_products_type" value="2" />
                        <input type="hidden" id="rentmy_deposit_amount"
                               value="<?php echo $rent_my_product_details['deposit_amount']; ?>"/>

                        <input type="hidden" id="rentmy_available_for_sale"
                               value="<?php echo (!empty($rent_my_product_details['available_for_sale']) && ($rent_my_product_details['available_for_sale'] > 0)?$rent_my_product_details['available_for_sale']:0); ?>"/>
                        <input type="hidden" id="rentmy_available"
                               value="<?php echo (!empty($rent_my_product_details['available']) && ($rent_my_product_details['available'] > 0)?$rent_my_product_details['available']:0); ?>"/>
                    </div>
                    <?php if($rent_my_product_details['deposit_amount']){?>
                    <div class="rentmy-deposit-amount mt-1">
                        <p><?php echo $GLOBALS['store_text']['cart']['lbl_total_deposite']??'Deposit amount'; ?>: <?php echo $GLOBALS['RentMy']::currency($rent_my_product_details['deposit_amount']);?></p>
                    </div>
                    <?php } ?>

                </div>
            </div>





            <!-- related product block starts -->
            <?php if (!empty($rent_my_product_details['related_products'])): ?>
                <?php $related_products = $rent_my_product_details['related_products']; ?>
                <div class='rentmy-product-list related-producst-list'>
                    <div class="related-product-title">
                        <h4><?php echo !empty($GLOBALS['store_text']['product_details']['related_title']) ? $GLOBALS['store_text']['product_details']['related_title'] . ':' : 'Related Product'; ?></h4>
                    </div>
                    <div class='products'>
                        <?php foreach ($related_products as $related): ?>
                            <div class="product-grid">
                                <div class="product-grid-inner text-center">
                                    <div class="product-grid-img">
                                        <img class="img-fluid"
                                             src="<?php echo $GLOBALS['RentMy']::imageLink($related['id'], $related['images'][0]['image_small'], 'list'); ?>">
                                        <a href="javascript:void(0)">
                                            <div class="product-overley">

                                            </div>
                                        </a>
                                    </div>

                                    <div class="product-grid-body">
                                        <div class="product-name">
                                            <?php
                                            if (isset($related['type']) && $related['type'] == 2) { ?>
                                                <a href="<?php echo getRentMyParmalink('rentmy.page_url.package_details') . '?uid=' . $related['uuid']; ?>">
                                                    <h4 class=""><?php echo $related['name']; ?></h4>
                                                </a>
                                            <?php } else { ?>
                                                <a href="<?php echo getRentMyParmalink('rentmy.page_url.product_details') . '?uid=' . $related['uuid']; ?>">
                                                    <h4 class=""><?php echo $related['name']; ?></h4>
                                                </a>
                                            <?php } ?>
                                        </div>
                                        <?php
                                        $priceTypes = getRentalTypes($related['prices']);
                                        $prices = getPrices($related['prices']);
                                        $generic_prices = empty($related['price']) ? $related['prices'] : $related['price'];
                                        $start_at = $GLOBALS['store_text']['others']['product_list_starting_at'] ?? 'Starting at';
                                        $buy_now=$GLOBALS['store_text']['others']['product_list_buy_now_for'] ?? 'Buy now for';
                                        $for= !empty($GLOBALS['store_text']['others']['product_list_for']) ? ' '.$GLOBALS['store_text']['others']['product_list_for'].' ' : ' for ';
                                        $per= !empty($GLOBALS['store_text']['others']['product_list_per']) ? ' '.$GLOBALS['store_text']['others']['product_list_per'].' ' : ' per ';
                                        $rental_level=$GLOBALS['store_text']['others']['product_list_buy_now_for'] ?? 'Buy now for';
                                        $rental_level=$GLOBALS['store_text']['others']['product_list_buy_now_for'] ?? 'Buy now for';
                                        ?>
                                        <?php if (in_array('rent', $priceTypes)) { ;?>
                                            <?php if($prices['rent'][0]['duration']> 1){ ?>
                                                <span class="price"><?php echo $start_at.' ';?> <?php echo $GLOBALS['RentMy']::currency($prices['rent'][0]['price'], 'pre', 'amount', 'post') . (!empty($prices['rent'][0]['duration']) ? $for . $prices['rent'][0]['duration'] : '') . ' ' . (!empty($prices['rent'][0]['label']) ? $GLOBALS['RentMy']->contents_rental_duration_labels($prices['rent'][0]['label'],$GLOBALS['store_text']) : ''); ?></span>
                                            <?php }else{ ?>
                                                <span class="price"><?php echo $start_at.' ';?> <?php echo $GLOBALS['RentMy']::currency($prices['rent'][0]['price'], 'pre', 'amount', 'post') . (!empty($prices['rent'][0]['duration']) ? $per  : '') . ' ' . (!empty($prices['rent'][0]['label']) ?  $GLOBALS['RentMy']->contents_rental_duration_labels($prices['rent'][0]['label'],$GLOBALS['store_text']) : ''); ?></span>
                                            <?php } ?>

                                        <?php } elseif (in_array('fixed', $priceTypes)) { ?>
                                            <span class="price"><?php echo $start_at.' ';?> <?php echo $GLOBALS['RentMy']::currency($prices['rent'][0]['price'], 'pre', 'amount', 'post'); ?></span>
                                        <?php } else { ?>
                                            <span class="price"><?php echo $buy_now.' ';?> <?php echo $GLOBALS['RentMy']::currency($prices['base']['price'], 'pre', 'amount', 'post'); ?></span>
                                        <?php } ?>
                                        <?php if (isset($related['type']) && $related['type'] == 2) { ?>
                                            <a class="button"
                                               href="<?php echo getRentMyParmalink('rentmy.page_url.package_details') . '?uid=' . $related['uuid']; ?>"><?php echo $GLOBALS['store_text']['product_details']['btn_view_details'] ?? 'View Details';?></a>
                                        <?php } else { ?>
                                            <a class="button"
                                               href="<?php echo getRentMyParmalink('rentmy.page_url.product_details') . '?uid=' . $related['uuid']; ?>"><?php echo $GLOBALS['store_text']['product_details']['btn_view_details'] ?? 'View Details';?></a>
                                        <?php } ?>

                                        <?php if (in_array('base', $priceTypes) && (isset($related['type']) && $related['type'] != 2)) { ?>
                                            <a data-variants_products_id="<?php echo $related['default_variant']['variants_products_id']; ?>"
                                               data-product_id="<?php echo $related['id']; ?>" href="javascript:void(0)"
                                               class="button add_to_cart_button_list">
                                                <?php echo $GLOBALS['store_text']['product_details']['add_to_cart'] ?? 'Add to cart';?><i style="display: none;" class="loading fa fa-spinner fa-spin"></i>
                                            </a>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            <!-- related product block ends -->







        </div>
    </div>
<?php

}

//function getRentalTypes($prices)
//{
//    if (empty($prices)) {
//        return false;
//    }
//    $types = [];
//    foreach ($prices as $price) {
//        foreach ($price as $k => $p) {
//            if ($k == 'rent' || $k == 'hourly' || $k == 'daily' || $k == 'weekly' || $k == 'monthly') {
//                $types[] = 'rent';
//            } else {
//                if (!empty($p['price'])) {
//                    $types[] = $k;
//                }
//            }
//
//        }
//    }
//    return array_unique($types);
//}
//
//function getPrices($prices)
//{
//    $formatPrice = [];
//    $formatPrice['rent'] = [];
//    foreach ($prices as $price) {
//        foreach ($price as $k => $p) {
//            if ($k == 'base') {
//                $formatPrice['base'] = $p;
//            } else {
//                foreach ($p as $i => $j) {
//                    $formatPrice['rent'][] = $j;
//                }
//            }
//        }
//    }
//    return $formatPrice;
//}
