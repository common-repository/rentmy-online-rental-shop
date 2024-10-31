<?php
/**
 * @wordpress-plugin
 * Plugin Name: RentMy Real-Time Rental Management Plugin
 * Description: Enables your customers to make rental reservations from your website by Connecting Wordpress to the RentMy Reservation Software.
 * Author: RentMy Rental Software
 * Version: 3.16.24
 * Author URI: https://rentmy.co
 * Copyright: 2022 RentMy
 */

//// If this file is called directly, abort.
if (!defined('ABSPATH')) exit;

if (!defined('RENTMY_PLUGIN_FILE')) {
    define('RENTMY_PLUGIN_FILE', __FILE__);
}

if(session_id() == ''){
     session_start();
}

//helper function get Parmalink by option key
function getRentMyParmalink($option_key): string{

    $pageId = get_option($option_key);
    return get_permalink( $pageId );

}

//// Include admin settings page
function rentmy_admin()
{
    include('admin/rentmy_admin.php');
}

function rentmy_admin_actions()
{
    add_options_page("RentMy", "RentMy", 'administrator', "rentmy", "rentmy_admin");
    add_menu_page("RentMy", "RentMy", 'administrator', "rentmy", "rentmy_admin" , plugin_dir_url(__FILE__) . "/assets/icon.png");
}

function rentmy_admin_resources($hook)
{
    if ($hook != 'settings_page_rentmy') {
        return;
    }
    wp_enqueue_style('rentmy-admin', plugins_url('assets/admin.css', __FILE__));
}

function rentmy_admin_settings_link($links)
{
    $settings_link = '<a href="options-general.php?page=rentmy">' . __('Settings') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
}

/**
 * @deprecated
 */
function add_rentmy_configuration_js()
{
    ?>
    <script>var rentMyStoreConfig = {};</script>
    <?php
    $store_id = get_option('rentmy_store_id');
    if (empty($store_id)) return;
    ?>
    <script>rentMyStoreConfig = {store: '<?php echo $store_id; ?>'};</script>
    <?php
}

/**
 * @deprecated
 * @param $params
 * @return string
 */
function rentmy_product_list($params)
{
    $options = shortcode_atts(array(
        'data-products' => NULL,
        'data-type' => 'featured',
        'data-limit' => 12,
        'data-sort' => 'name',
        'data-sort-type' => 'ASC',
        'data-style' => NULL,
        'data-button'=> false,
        'data-button-text' => 'Add to cart'
    ), $params);

    return '<div id="rentmy-products"' . rentmy_shortcode_options_to_data($options) . '></div>';
}

function rentmy_shortcode_options_to_data($options)
{
    $data = implode(' ', array_map(
        function ($v, $k) {
            if (!empty($v)) {
                return sprintf("%s=\"%s\"", $k, $v);
            }
        },
        $options,
        array_keys($options)
    ));

    return $data;
}

// add classes one by one
if (!class_exists('RentMy', false)) {
    include_once dirname(__FILE__) . '/includes/class-rentmy.php';
}

add_action('admin_menu', 'rentmy_admin_actions');
add_action('admin_enqueue_scripts', 'rentmy_admin_resources');
$plugin = plugin_basename(__FILE__);
add_filter("plugin_action_links_$plugin", 'rentmy_admin_settings_link');
//add_action('wp_head', 'add_rentmy_configuration_js');

//add_shortcode('rentmy-products', 'rentmy_product_list');

$GLOBALS['RentMy'] = $rentmy = new RentMy();
