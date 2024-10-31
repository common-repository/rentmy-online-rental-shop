<?php
//short code for complete catalog page
function rent_product_list_with_filter_shortcode($params)
{
    ob_start();    
    $default = [
        'hide_categories' => false,
        'hide_filters' => false,
        'hide_price' => false,
        'hide_type' => false,
    ];
    $parameters = shortcode_atts($default, $params);    
    ?>
    <div class="row">
        <div class="col-lg-3 col-md-3 col-sm-12">
            <?php 
            if($parameters["hide_categories"] === false || $parameters["hide_categories"] === 'false'){
                echo do_shortcode('[rentmy-categories-list]'); 
            }

            $paramText = "";
            $paramText .= strlen($parameters['hide_filters']) ? ' hide_filters=' . $parameters['hide_filters'] : '';
            $paramText .= strlen($parameters['hide_price']) ? ' hide_price=' . $parameters['hide_price'] : '';
            $paramText .= strlen($parameters['hide_type']) ? ' hide_type=' . $parameters['hide_type'] : '';

            echo do_shortcode('[rentmy-tags-list '. $paramText . ']');
            ?>          
        </div>
        <div class="col-lg-9 col-md-9 col-sm-12">
            <?php 
            echo do_shortcode('[rentmy-products-list]');
            ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

add_shortcode('rentmy-products-list-with-filter', 'rent_product_list_with_filter_shortcode');