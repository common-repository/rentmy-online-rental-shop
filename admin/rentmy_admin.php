<?php
if (!defined('ABSPATH')) exit;
function rentmy_print_pages($title, $option_name)
{
    $pageId = get_option($option_name);
    if (empty($pageId)) {
        $page_slug = [
            'rentmy.page_url.products_list_with_filter' => 'rentmy-catalog',
            'rentmy.page_url.products_list' => 'rentmy-products-list',
            'rentmy.page_url.product_details' => 'rentmy-product-details',
            'rentmy.page_url.package_details' => 'rentmy-package-details',
            'rentmy.page_url.cart' => 'rentmy-cart',
            'rentmy.page_url.checkout' => 'rentmy-checkout',
            'rentmy.page_url.reset_password' => 'rentmy-reset-password',
            'rentmy.page_url.profile' => 'rentmy-customer-profile',
        ]; //default option slugs
        $page = get_page_by_path($page_slug[$option_name]);
        if ($page) {
            update_option($option_name, $page->ID);
            $pageId = $page->ID;
        }
    }
    $pages = get_pages(["post_type" => "page"]);
?>
    <td width="220">
        <label><?php echo $title ?></label>
    </td>
    <td>
        <select name="<?php echo $option_name ?>" id="<?php echo $option_name ?>">
            <?php foreach ($pages as $key) { ?>
                <?php if (strlen($key->post_title)) : ?>
                    <option value="<?php echo $key->ID ?>" <?php if ($pageId == $key->ID) {
                                                                echo "selected";
                                                            } ?>>
                        <?php echo _e($key->post_title) ?>
                    </option>
                <?php endif; ?>
            <?php } ?>
        </select>
    </td>
<?php
}

include_once dirname(RENTMY_PLUGIN_FILE) .  DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'class-rentmy.php';
include_once dirname(RENTMY_PLUGIN_FILE) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'class-rentmy-token.php';
include_once dirname(RENTMY_PLUGIN_FILE) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'class-rentmy-config.php';

(new Rentmy())->rentmy_activate(); //Generate page if not found any page

if (!empty($_POST['rentmy_hidden']) && (current_user_can('administrator'))) {

    $rentMynonce = $_POST['rentmy_hidden'];
    // $rentMynonce2 = $_POST['rentmy_hidden'];
    unset($_SESSION['rentmy_config']);

    if (wp_verify_nonce($rentMynonce, 'rentMy-nonce')) {

        if (isset($_POST['submit_config']) && $_POST['submit_config']) {
            //Form data sent
            $rentmy_store_id = sanitize_text_field($_POST['rentmy_store_id']);
            $rentmy_api_key = sanitize_text_field($_POST['rentmy_api_key']);
            $rentmy_secret_key = sanitize_text_field($_POST['rentmy_secret_key']);

            update_option('rentmy_storeUid', $rentmy_store_id);
            update_option('rentmy_store_id', $rentmy_store_id);
            update_option('rentmy_apiKey', $rentmy_api_key);
            update_option('rentmy_secretKey', $rentmy_secret_key);

            (new RentMy_Token())->getToken();
            (new RentMy_Config())->store_config();
        }

        /**
         * Section::Updating pages
         * @important Note
         * html input fileds name not working with any dot. If use dot in any filed or opton name,
         * it will replace automatically with underscore(_)
         * So, to acces $_POST or $_GET, must be use underscore(_)
         */
        if (isset($_POST['submit_pages'])) {
            update_option('rentmy.page_url.products_list', $_POST['rentmy_page_url_products_list']);
            update_option('rentmy.page_url.product_details', $_POST['rentmy_page_url_product_details']);
            update_option('rentmy.page_url.package_details', $_POST['rentmy_page_url_package_details']);
            update_option('rentmy.page_url.checkout', $_POST['rentmy_page_url_checkout']);
            update_option('rentmy.page_url.cart', $_POST['rentmy_page_url_cart']);
            update_option('rentmy.page_url.reset_password', $_POST['rentmy_page_url_reset_password']);
            update_option('rentmy.page_url.profile', $_POST['rentmy_page_url_profile']);
        }

        $updated = true;
    }
} else {

    if (isset($_GET['rentmy_store_slug']) && !empty($_GET['rentmy_store_slug']) && isset($_GET['page']) && $_GET['page'] == 'rentmy') {
        $slug = sanitize_text_field($_GET['rentmy_store_slug']);
        (new RentMy_Token())->getTokenFromStoreName($slug);
        (new RentMy_Config())->store_config();
        $apps = (new RentMy_Config())->apps();

        if (!empty($apps)) {
            $rentmy_store_id = $apps[0]['store_uid'];
            $rentmy_api_key = $apps[0]['api_key'];
            $rentmy_secret_key = $apps[0]['api_secret'];

            if (isset($_POST['submit_config'])) {
                update_option('rentmy_storeUid', $rentmy_store_id);
                update_option('rentmy_store_id', $rentmy_store_id);
                update_option('rentmy_apiKey', $rentmy_api_key);
                update_option('rentmy_secretKey', $rentmy_secret_key);
            }
        }
    }
}
$rentmy_store_id = get_option('rentmy_storeUid');
$rentmy_api_key = get_option('rentmy_apiKey');
$rentmy_secret_key = get_option('rentmy_secretKey');

$nonce = wp_create_nonce('rentMy-nonce');
// $nonce2 = wp_create_nonce('rentMy-nonce2');
$get_pages = get_pages();
?>
<style>
    .update-nag,
    .updated,
    .error,
    .is-dismissible {
        display: none !important;
    }

    .card {
        max-width: 100%;
    }
</style>
<div class="wrap rentmy-admin-wrap">
    <div class="card rentmy-admin-header">
        <div class="rentmy-admin-pull-left">
            <img src="<?php echo esc_url(plugins_url('/assets/logo.png', __FILE__)); ?>" alt="RentMy" width="200" />
        </div>
    </div>

    <form name="rentmy_form" method="post" action="<?php echo str_replace('%7E', '~', $_SERVER['REQUEST_URI']); ?>">
        <div class="card">
            <h2><?php _e("1. Connect your RentMy account"); ?></h2>
            <?php if (!empty($updated)) : ?>
                <div class="updated" style="display: block !important;">
                    <p>
                        <strong>
                            <?php
                            if (isset($_POST['submit_pages'])) {
                                _e('Pages configuration saved successfully.');
                            } else {
                                _e('API keys and token saved successfully.');
                            }
                            ?>
                        </strong>
                    </p>
                </div>
            <?php endif; ?>
            <p class="rentmy-admin-subtitle"><?php _e("Don't have a RentMy account? <a href=\"https://client.rentmy.co/auth/login\" target=\"_blank\" rel=\"noopener\">Get started for free</a>."); ?></p>
            <hr />

            <input type="hidden" name="rentmy_hidden" value="<?php echo $nonce; ?>">
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row">
                            <label for="rentmy_store_id"><?php _e("Your RentMy Store UID"); ?></label>
                        </th>
                    </tr>
                    <tr>
                        <td>
                            <input required type="text" name="rentmy_store_id" class="regular-text" value="<?php echo $rentmy_store_id; ?>" />
                            <!--                        <p class="description">--><?php //_e("You can find your <b>Store ID</b> under <b>Settings >Widget Section</b> in your RentMy account."); 
                                                                                    ?>
                            <!--</p>-->
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="rentmy_api_key"><?php _e("Your RentMy API Key"); ?></label>
                        </th>
                    </tr>
                    <tr>
                        <td>
                            <input required type="text" name="rentmy_api_key" class="regular-text" value="<?php echo $rentmy_api_key; ?>" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="rentmy_secret_key"><?php _e("Your RentMy Secret Key"); ?></label>
                        </th>
                    </tr>
                    <tr>
                        <td>
                            <input required type="text" name="rentmy_secret_key" class="regular-text" value="<?php echo $rentmy_secret_key; ?>" />
                        </td>
                    </tr>

                </tbody>
            </table>
            <p class="submit">
                <input type="submit" name="submit_config" value="<?php _e('Update Options', 'rentmy_trdom') ?>" class="button button-primary" />

                <?php if (get_option('rentmy_accessToken') != '') { ?>
                    <!--            <input type="submit" name="update-configuration" value="--><?php //_e('Update Configuration', 'rentmy_trdom') 
                                                                                                ?>
                    <!--"-->
                    <!--                   class="button button-primary"/>-->
                <?php } ?>
            </p>

        </div>
    </form>

    <!-- =================== -->
    <!-- Configuring pages -->
    <!-- =================== -->
    <form name="rentmy_form" method="post" action="<?php echo str_replace('%7E', '~', $_SERVER['REQUEST_URI']); ?>">
        <div class="card">

            <h2><?php _e("2. Page configuration"); ?></h2>
            <hr />
            <input type="hidden" name="rentmy_hidden" value="<?php echo $nonce; ?>">
            <table class="form-table">
                <tbody>
                    <tr>
                        <?php rentmy_print_pages("Rentmy products list page", "rentmy.page_url.products_list"); ?>
                    </tr>
                    <tr>
                        <?php rentmy_print_pages("Rentmy product details page", "rentmy.page_url.product_details"); ?>
                    </tr>
                    <tr>
                        <?php rentmy_print_pages("Rentmy package details page", "rentmy.page_url.package_details"); ?>
                    </tr>
                    <tr>
                        <?php rentmy_print_pages("Rentmy checkout page", "rentmy.page_url.checkout"); ?>
                    </tr>
                    <tr>
                        <?php rentmy_print_pages("Rentmy cart page", "rentmy.page_url.cart"); ?>
                    </tr>
                    <tr>
                        <?php rentmy_print_pages("Rentmy reset password", "rentmy.page_url.reset_password"); ?>
                    </tr>
                    <tr>
                        <?php rentmy_print_pages("Rentmy customer profile", "rentmy.page_url.profile"); ?>
                    </tr>
                </tbody>
            </table>

            <p class="submit">
                <input type="submit" name="submit_pages" value="<?php _e('Update Pages', 'rentmy_trdom') ?>" class="button button-primary" />
            </p>
        </div>
    </form>



    <div class="card">
        <h2><?php _e("3. RentMy Useful Shortcodes"); ?></h2>
        <p class="bq-admin-subtitle"><?php _e("To get started, simply <b>copy</b> and <b>paste</b> this product list short code to any <b>Page</b> or <b>Post</b>."); ?></p>
        <hr />
        <table class="form-table">
            <tbody>

                <tr>
                    <th scope="row">
                        <label for="embed_code"><?php _e("All Products"); ?></label>
                    </th>
                </tr>
                <tr>
                    <td>
                        <code>[rentmy-products-list]</code>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="embed_code"><?php _e("Products List With Filter"); ?></label>
                    </th>
                </tr>
                <tr>
                    <td>
                        <code>[rentmy-products-list-with-filter]</code>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="embed_code"><?php _e("Products List With Dynamic Filter"); ?></label>
                    </th>
                </tr>
                <tr>
                    <td>
                        <code>[rentmy-products-list-with-filter hide_categories=false hide_filters=false hide_price=false hide_type=false]</code>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="embed_code"><?php _e("Category products"); ?></label>
                    </th>
                </tr>
                <tr>
                    <td>
                        <code>[rentmy-products-list type=category id=comma seperated category_id]</code>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="embed_code"><?php _e("Tagged products"); ?></label>
                    </th>
                </tr>
                <tr>
                    <td>
                        <code>[rentmy-products-list type=tag id=comma seperated tag ids]</code>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="embed_code"><?php _e("Set products number of each row (cols=2 to 6)"); ?></label>
                    </th>
                </tr>
                <tr>
                    <td>
                        <code>[rentmy-products-list cols=4]</code>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="embed_code"><?php _e("Sorted product list"); ?></label>
                    </th>
                </tr>
                <tr>
                    <td>
                        <code>[rentmy-products-list type=category id=category_id sort_type=ASC sort=buy_price]</code><br />
                        <code>[rentmy-products-list type=category id=1815 sort_type=DESC sort=rent_price]</code><br />
                        <code>[rentmy-products-list type=category id=1815 sort_type=DESC sort=created]</code><br />
                        <code>[rentmy-products-list type=category id=1815 sort_type=ASC sort=name]</code><br />
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="embed_code"><?php _e("Product details"); ?></label>
                    </th>
                </tr>
                <tr>
                    <td>
                        <code>[rentmy-products-details uid=product_uid]</code><br />
                        <code>[rentmy-products-details product_id=product_id]</code><br />
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="embed_code"><?php _e("Package details"); ?></label>
                    </th>
                </tr>
                <tr>
                    <td>
                        <code>[rentmy-package-details uid=package_uid]</code><br />
                        <code>[rentmy-package-details product_id=package_id]</code><br />
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="embed_code"><?php _e("Grids"); ?></label>
                    </th>
                </tr>
                <tr>
                    <td>
                        <code>[rentmy-grid]</code>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="embed_code"><?php _e("Search"); ?></label>
                    </th>
                </tr>
                <tr>
                    <td>
                        <code>[rentmy-search]</code>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="embed_code"><?php _e("Search With More Customization"); ?></label>
                    </th>
                </tr>
                <tr>
                    <td>
                        <code>[rentmy-search width=700px height=55px styles=background-color:#28a74563; hide_header=false hide_button=false]</code>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="embed_code"><?php _e("Order Details"); ?></label>
                    </th>
                </tr>
                <tr>
                    <td>
                        <code>[rentmy-order-details order_uid=uid]</code>
                        <p>OR</p>
                        <code>[rentmy-order-details order_id=id]</code>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="embed_code"><?php _e("Customer Login"); ?></label>
                    </th>
                </tr>
                <tr>
                    <td>
                        <code>[rentmy-customer-login]</code>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="embed_code"><?php _e("Customer Login Modal"); ?></label>
                    </th>
                </tr>
                <tr>
                    <td>
                        <code>[rentmy-customer-login-modal]</code>
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><u><small>For details call us at <a href="tel:+4087288556"> Phone: + (408) 728-8556</a> or email at <a href="mailto: Hello.RentMy.co">Hello.RentMy.co</a></small></u></td>
                </tr>
            </tbody>
        </table>
    </div>

</div>