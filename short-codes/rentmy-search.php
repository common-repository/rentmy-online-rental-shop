<?php
//list all tags of a store
function rent_my_search_shortcode($params = [])
{
    ob_start();
    $final_params = shortcode_atts(array(
        'width' => "",
        'height' => "",
        'styles' => "",
        'hide_button' => false,
        'hide_header' => false,
    ), $params);
    echo rentmy_search_template($final_params);
    return ob_get_clean();
}
add_shortcode('rentmy-search', 'rent_my_search_shortcode');

function rentmy_search_template($final_params)
{
?>
    <div class="rentmy-plugin-manincontent">
        <div class="rentmy-plugin-categorytagsearch-area" style="width: <?php echo $final_params["width"] ?>;">
            <?php if ($final_params['hide_header'] == false || $final_params['hide_header'] == "false") : ?>
                <h3>Search</h3>
            <?php endif; ?>
            <form action="<?php echo getRentMyParmalink('rentmy.page_url.products_list'); ?>" method="get" id="rentmy-search-form" accept-charset="ISO-8859-1">
                <div class="cart-checkout-area">
                    <div class="coupon-code">
                        <input type="text" class="coupon-text rentmy_coupon_text" placeholder="Search" name="search" value="<?php echo !empty($_GET['search']) ? $_GET['search'] : null; ?>" style="<?php echo esc_attr($final_params['styles']) ?>;width:<?php echo esc_attr($final_params['width']) ?>;height:<?php echo esc_attr($final_params['height']) ?>;">
                        <?php if ($final_params['hide_button'] === false || $final_params['hide_button'] === "false") : ?>
                            <button class="rentmy-button" type="submit" name="rentmy-submit-search" value="search-submit" style="height:<?php echo esc_attr($final_params['height']) ?>;">Search  </button>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>
    </div>
<?php
}
