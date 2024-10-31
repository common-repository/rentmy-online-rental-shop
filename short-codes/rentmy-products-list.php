<?php
//list all products of a store
function rent_my_products_list_shortcode($params)
{

    ob_start();
    $attributes = [
        'location' => get_option('rentmy_locationId'),
        'page_no' => !empty($_GET['page_no']) ? $_GET['page_no'] : 1,
        'limit' => !empty($_GET['limit']) ? $_GET['limit'] : 12,
    ];

    // if uid is empty on get url we will check for the shortcode params for type and uid and if found
    // we will forcefully update the get params. else leave as it is.
    // will do this for all kind of other operations like tags and ids
    if (empty($_GET['uid'])) {
        if (!empty($params['type'])) {
            if ($params['type'] == 'category' && !empty($params['id'])) {
                $categoryObj = new RentMy_Category();

                $category=$categoryObj->getCategoryDetails($params['id']);
                $category_uid = trim($category['uuid']);
            }
        }
    }

    if (empty($_GET['tags'])) {
        if (!empty($params['type'])) {
            if ($params['type'] == 'tag' && !empty($params['id'])) {
                $productTags = trim($params['id']);
            }
        }
    }

    //    Products using product's ids
    if (!empty($params['type'])) {
        if ($params['type'] == 'products' && !empty($params['id'])) {
            $productIds = trim($params['id']);
            $attributes['products_id'] = $productIds;
        }
    }
    //   end Products using product's ids

    if (!empty($params['status'])) {
        $attributes['status'] = trim($params['status']);
    }

    if (!empty($params['sort-type'])) {
        $attributes['sort_type'] = $params['sort-type'];
    }

    if (!empty($params['sort'])) {
        $attributes['sort'] = $params['sort'];
    }


    if (!empty($params['location'])) {
        $attributes['location'] = $params['location'];
    }

    if (!empty($params['page_no'])) {
        $attributes['page_no'] = $params['page_no'];
    }

    if (!empty($params['limit'])) {
        $attributes['limit'] = $params['limit'];
    }

    if (!empty($_GET['tags'])) {
        $attributes['tag_id'] = trim($_GET['tags']);
    }elseif(!empty($productTags)){
        $attributes['tag_id'] = trim($productTags);
    }else {
        $attributes['tag_id'] = null;
    }


    if (!empty($_GET['purchase_type'])) {
        $attributes['purchase_type'] = trim($_GET['purchase_type']);
    }else {
        $attributes['purchase_type'] = null;
    }

    if (!empty($_GET['min_price'])) {
        $attributes['price_min'] = trim($_GET['min_price']);
    }else {
        $attributes['price_min'] = null;
    }

    if (!empty($_GET['max_price'])) {
        $attributes['price_max'] = trim($_GET['max_price']);
    }else {
        $attributes['price_max'] = null;
    };

    $productClass = '';

    if (!empty($params['cols'])) { 
        $allowedNumbers = [2, 3, 4, 5, 6];
        $productsPerRow = $params['cols'];
        if( in_array((int) $productsPerRow, $allowedNumbers)){
            $productClass = 'product-grid-' . $productsPerRow;
        }
    }

    if (empty($attributes)) {
        echo '<span class="rentmy-errore-msg">No Attributes or Parameter found for this Rent My API.</span>';
        return;
    };

    $rentmy_products_list = new RentMy_Products();

    if (!empty($_GET['search'])) {
        $attributes['search'] = trim($_GET['search']);
        $response = $rentmy_products_list->productSearch($attributes);
    } else if (!empty($_GET['uid'])) {
        $attributes['category_id'] = $_GET['uid'];
        $response = $rentmy_products_list->productListByCategory($attributes);
    }else if (!empty($category_uid)) {
        $attributes['category_id'] = $category_uid;
        $response = $rentmy_products_list->productListByCategory($attributes);
    } else {
        $response = $rentmy_products_list->productList($attributes);
    }
//     print_r("<pre>");print_r($response);print_r("</pre>");
    $rentmy_config = new RentMy_Config();
    $store_content = $rentmy_config->store_contents();
    
    if (!empty($store_content)) {
        $GLOBALS['store_text'] = $store_content[0]['contents'];
        $GLOBALS['store_config'] = $store_content[0]['contents']['confg'];
    }
    //$GLOBALS['RentMy']->pr( $GLOBALS['store_text']);

    if (!empty($response)):
        rentmy_product_list_template($response, $productClass);
        return ob_get_clean();
    else:
        echo !empty($response['message']) ? '<span class="rentmy-errore-msg">' . $response['message'] . '</span>' : '';
        return ob_get_clean();
    endif;
}

add_shortcode('rentmy-products-list', 'rent_my_products_list_shortcode');

function rentmy_product_list_template($rent_my_product, $productClass)
{
    global $post;
    $post_slug = $post->post_name;
    ?>

    <?php if (!empty($rent_my_product['data'])) { ?>
    <div class="rentmy-plugin-manincontent">
        <div class='rentmy-product-list'>
            <div class='products'>
                <?php foreach ($rent_my_product['data'] as $product): ?>
                    <?php
                    $priceTypes = getRentalTypes($product['prices']);
                    $prices = getPrices($product['prices']);
                    $pricing = getPricingWithPromoInfo($priceTypes, $prices);

                    $generic_prices = empty($product['price']) ? $product['prices'] : $product['price'];
                    $start_at = $GLOBALS['store_text']['others']['product_list_starting_at'] ?? 'Starting at';
                    $buy_now=$GLOBALS['store_text']['others']['product_list_buy_now_for'] ?? 'Buy now for';
                    $for= !empty($GLOBALS['store_text']['others']['product_list_for']) ? ' '.$GLOBALS['store_text']['others']['product_list_for'].' ' : ' for ';
                    $per= !empty($GLOBALS['store_text']['others']['product_list_per']) ? ' '.$GLOBALS['store_text']['others']['product_list_per'].' ' : ' per ';
                    $rental_level=$GLOBALS['store_text']['others']['product_list_buy_now_for'] ?? 'Buy now for';
                    $rental_level=$GLOBALS['store_text']['others']['product_list_buy_now_for'] ?? 'Buy now for';
                    ?>
                    <div class="product-grid <?php echo esc_attr($productClass) ?>">
                        <div class="product-grid-inner text-center product_promotional">
                            <div class="product-grid-img">
                                <img class="img-fluid"
                                     src="<?php echo $GLOBALS['RentMy']::imageLink($product['id'], $product['images'][0]['image_small'], 'list'); ?>">
                                <?php if ($product['type'] == 2) { ?>
                                    <?php $separator = strpos( getRentMyParmalink('rentmy.page_url.package_details'), '?' ) !== false ? '&' : '?';  ?>
                                    <a href="<?php echo getRentMyParmalink('rentmy.page_url.package_details') . $separator . 'uid=' . $product['uuid']; ?>">
                                        <div class="product-overley"></div>
                                    </a>
                                <?php } else { ?>
                                    <?php $separator = strpos( getRentMyParmalink('rentmy.page_url.product_details'), '?' ) !== false ? '&' : '?';  ?>
                                    <a href="<?php echo getRentMyParmalink('rentmy.page_url.product_details') . $separator . 'uid=' . $product['uuid']; ?>">
                                        <div class="product-overley"></div>
                                    </a>
                                <?php } ?>
                            <?php if (isset($pricing['is_promotional']) && $pricing['is_promotional']){?>
                                <div class="reduced">
                                    <?php echo $GLOBALS['store_text']['product_details']['lbl_product_reduced'];?>
                                </div>
                            <?php } ?>
                            </div>

                            <div class="product-grid-body">
                                <div class="product-name">
                                    <?php if ($product['type'] == 2) { ?>
                                        <?php $separator = strpos( getRentMyParmalink('rentmy.page_url.package_details'), '?' ) !== false ? '&' : '?';  ?>
                                        <a href="<?php echo getRentMyParmalink('rentmy.page_url.package_details') . $separator . 'uid=' . $product['uuid']; ?>">
                                            <h4 class=""><?php echo $product['name']; ?></h4>
                                        </a>
                                    <?php } else { ?>
                                        <?php $separator = strpos( getRentMyParmalink('rentmy.page_url.product_details'), '?' ) !== false ? '&' : '?';  ?>
                                        <a href="<?php echo getRentMyParmalink('rentmy.page_url.product_details') . $separator . 'uid=' . $product['uuid']; ?>">
                                            <h4 class=""><?php echo $product['name']; ?></h4>
                                        </a>
                                    <?php } ?>
                                </div>
                                <span class="price">
                        <?php if (isset($pricing['is_promotional']) && $pricing['is_promotional']){?><del><?php } ?>
                                        <?php echo $pricing['pricing_text']  ?>
        <?php if (isset($pricing['is_promotional']) && $pricing['is_promotional']){?></del><?php } ?>
                                </span>

                                <?php if ($product['type'] == 2) { ?>
                                    <?php $separator = strpos( getRentMyParmalink('rentmy.page_url.package_details'), '?' ) !== false ? '&' : '?';  ?>
                                    <a class="button"
                                       href="<?php echo getRentMyParmalink('rentmy.page_url.package_details') . $separator . 'uid=' . $product['uuid']; ?>"><?php echo $GLOBALS['store_text']['product_details']['btn_view_details'] ?? 'View Details';?></a>
                                <?php } else { ?>
                                    <?php $separator = strpos( getRentMyParmalink('rentmy.page_url.product_details'), '?' ) !== false ? '&' : '?';  ?>
                                    <a class="button"
                                       href="<?php echo getRentMyParmalink('rentmy.page_url.product_details') . $separator . 'uid=' . $product['uuid']; ?>"><?php echo $GLOBALS['store_text']['product_details']['btn_view_details'] ?? 'View Details';?></a>
                                <?php } ?>

                                <?php if (in_array('base', $priceTypes) && ($product['type'] != 2)) { ?>
                                    <a data-variants_products_id="<?php echo $product['default_variant']['variants_products_id']; ?>"
                                       data-product_id="<?php echo $product['id']; ?>" href="javascript:void(0)"
                                       class="button add_to_cart_button_list">
                                        <?php echo $GLOBALS['store_text']['product_details']['add_to_cart'] ?? 'Add to cart';?>
                                    </a>
                                <?php } ?>
                                <?php if ($pricing['is_promotional']){?>
                                <div class="price_text">
                                    <h6 class="price_text_titleone"><?php echo str_replace('%percent%',$pricing['promotional_percent'], str_replace("%amount%",$pricing['promotional_price'],$GLOBALS['store_text']['product_details']['txt_reduce_amount']));?></h6>
                                    <h6 class="price_text_titletwo"><?php echo $GLOBALS['store_text']['product_details']['txt_reduce_valid_date'];?></h6>
                                </div>
                                <?php } ?>
                            </div>

                        </div>
                    </div>

                <?php endforeach; ?>
            </div>

            <?php

            $limit = $rent_my_product['limit'];
            $total = $rent_my_product['total'];
            $page = empty($_GET['page_no']) ? 1 : $_GET['page_no'];
            $cat_param = !empty($_GET['uid']) ? '&uid=' . $_GET['uid'] : '';
            $adjacents = 3;
            if (empty($cat_param)) {
                $targetpage =  '?limit='.$limit;
            } else {
                $targetpage =  '?' . $cat_param . '&limit=' . $limit;
            }

            $pagination = $GLOBALS['RentMy']::paginate($page, $total, $limit, $adjacents, $targetpage);
            echo $pagination;
            ?>
        </div>
    </div>
<?php } else {
    echo '<span class="rentmy-errore-msg">No Products Found</span>';
} ?>

    <?php
}



function getPricingWithPromoInfo($priceTypes, $prices){

    $start_at = $GLOBALS['store_text']['others']['product_list_starting_at'] ?? 'Starting at';
    $buy_now=$GLOBALS['store_text']['others']['product_list_buy_now_for'] ?? 'Buy now for';
    $for= !empty($GLOBALS['store_text']['others']['product_list_for']) ? ' '.$GLOBALS['store_text']['others']['product_list_for'].' ' : ' for ';
    $per= !empty($GLOBALS['store_text']['others']['product_list_per']) ? ' '.$GLOBALS['store_text']['others']['product_list_per'].' ' : ' per ';
    $response = [];
    if (in_array('rent', $priceTypes)) {
        if ($prices['rent'][0]['price'] < $prices['rent'][0]['regular_price'] ){
            $response['is_promotional'] = true;
            $response['promotional_price'] = $GLOBALS['RentMy']::currency($prices['rent'][0]['price'], 'pre', 'amount', 'post');
            $response['promotional_percent'] = calculatePromotionalPercent($prices['rent'][0]['price'], $prices['rent'][0]['regular_price']);
        }else{
            $response['is_promotional'] = false;
            $response['promotional_price'] = '';
            $response['promotional_percent'] = '';
        }

        $price = !empty($prices['rent'][0]['regular_price'])?$prices['rent'][0]['regular_price']:$prices['rent'][0]['price'];
        if(isset($prices['rent'][0]['duration']) && $prices['rent'][0]['duration']> 1){
            $response['pricing_text']  =  $start_at. ' ' . $GLOBALS['RentMy']::currency($price, 'pre', 'amount', 'post') . (isset($prices['rent'][0]['duration']) && !empty($prices['rent'][0]['duration']) ? $for . $prices['rent'][0]['duration'] : '') . ' ' . (!empty($prices['rent'][0]['label']) ? $GLOBALS['RentMy']->contents_rental_duration_labels($prices['rent'][0]['label'],$GLOBALS['store_text']) : '');
        }else{
            $response['pricing_text'] =  $start_at.' ' . $GLOBALS['RentMy']::currency($price, 'pre', 'amount', 'post') . (isset($prices['rent'][0]['duration']) && !empty($prices['rent'][0]['duration']) ? $per  : '') . ' ' . (!empty($prices['rent'][0]['label']) ?  $GLOBALS['RentMy']->contents_rental_duration_labels($prices['rent'][0]['label'],$GLOBALS['store_text']) : '');
        }

    }elseif (in_array('fixed', $priceTypes)) {
        if ($prices['rent'][0]['price'] < $prices['rent'][0]['regular_price'] ){
            $response['is_promotional'] = true;
            $response['promotional_price'] = $GLOBALS['RentMy']::currency($prices['rent'][0]['price'], 'pre', 'amount', 'post');
            $response['promotional_percent'] = calculatePromotionalPercent($prices['rent'][0]['price'], $prices['rent'][0]['regular_price']);
        }else{
            $response['is_promotional'] = false;
            $response['promotional_price'] = '';
            $response['promotional_percent'] = '';
        }
        $price = !empty($prices['rent'][0]['regular_price'])?$prices['rent'][0]['regular_price']:$prices['rent'][0]['price'];
        $response['pricing_text'] = $start_at.' ' . $GLOBALS['RentMy']::currency($price, 'pre', 'amount', 'post');
    }else {

        if ($prices['base'] != ''){
            if ($prices['base']['price'] < $prices['base']['regular_price'] ){
                $response['is_promotional'] = true;
                $response['promotional_price'] = $GLOBALS['RentMy']::currency($prices['base']['price'], 'pre', 'amount', 'post');
                $response['promotional_percent'] = calculatePromotionalPercent($prices['base']['price'], $prices['base']['regular_price']);
            }else{
                $response['is_promotional'] = false;
                $response['promotional_price'] = '';
                $response['promotional_percent'] = '';
            }
            $price = !empty($prices['base']['regular_price'])?$prices['base']['regular_price']:$prices['base']['price'];
            $response['pricing_text'] =  $buy_now.' ' .  $GLOBALS['RentMy']::currency($price, 'pre', 'amount', 'post');
        }
    }

    return $response;
}



function calculatePromotionalPercent($price, $regular_price){
    $percent = 0;
    $reduced = $regular_price - $price;
    $percent = ceil(($reduced / $regular_price) * 100);
    return $percent . '%';
}



