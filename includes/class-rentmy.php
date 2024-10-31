<?php

/**
 * RentMy setup
 *
 * @package RentMy
 * @since   1.0.0
 */

defined('ABSPATH') || exit;

/**
 * Main RentMy Class.
 *
 * @class RentMy
 */
class RentMy
{
    /**
     * RentMy version.
     *
     * @var string
     */
    public $version = '1.1.0';

    /**
     * The single instance of the class.
     *
     * @var RentMy
     * @since 2.1
     */
    protected static $_instance = null;

    /**
     * Session instance.
     *
     * @var RentMy_Session|RentMy_Session_Handler
     */
    public $session = null;


    /**
     * Product factory instance.
     *
     * @var RentMy_Product_Factory
     */
    public $product_factory = null;

    /**
     * Countries instance.
     *
     * @var RentMy_Countries
     */
    public $countries = null;


    /**
     * Cart instance.
     *
     * @var RentMy_Cart
     */
    public $cart = null;

    /**
     * Customer instance.
     *
     * @var RentMy_Customer
     */
    public $customer = null;

    /**
     * Order factory instance.
     *
     * @var RentMy_Order_Factory
     */
    public $order_factory = null;

    /**
     * RentMy api access Token
     * @var null
     */
    public static $accessToken = null;

    /**
     * Main RentMy Instance.
     *
     * Ensures only one instance of rentmy is loaded or can be loaded.
     *
     * @return RentMy - Main instance.
     * @see RentMy()
     * @since 1.0.0
     * @static
     */
    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * RentMy Constructor.
     */
    public function __construct()
    {
        $this->define_constants();
        $this->includes();
        $this->getConfig();
        require_once(wp_normalize_path(ABSPATH) . 'wp-load.php');

        if (is_admin()) {
            //register plugin activation and deactivation hooks
            register_activation_hook(RENTMY_PLUGIN_FILE, array($this, 'rentmy_activate'));
            register_deactivation_hook(RENTMY_PLUGIN_FILE, array($this, 'rentmy_deactivate'));
        }

        // other basic hooks
        // for template choose and other stuffs

        if (!is_admin()) {

            add_action('init', array($this, 'setUserCookie'), 1);           
            
            add_action('admin_bar_menu', array($this, 'rentmy_admin_bar_menu_add'), 50);
            add_filter('body_class', array($this, 'rentmy_body_classes'));
            // add js and css stuffs here
            add_action('wp_enqueue_scripts', array($this, 'add_rentmy_scripts'));

            // add search bar of rentmy products to the primary menu
            //            add_filter('wp_nav_menu_items', array($this, 'rentmy_add_search_box_to_menu'), 10, 2);

            // this statement is for update location wise token update
            $locationWiseToken = get_option("rentMyLocationWiseToken");

            if (empty($locationWiseToken)){
                (new RentMy_Token())->getToken();
            }
            // end for location specific token update

        }

        // ajax calls initialiazed
        new RentMy_Ajax();
        flush_rewrite_rules();
        // add widgets hook here
        add_action('widgets_init', array($this, 'rentmy_default_widget_registers'));

        // add initial html on this. like loader pre values etc.
        add_action('wp_head', array($this, 'rentmy_constant_variables_load_head'));
        add_action('wp_footer', array($this, 'add_customer_login_footer'));
        add_action('init', array($this, 'myplugin_rewrite_rule'));
        add_filter('query_vars', array($this, 'foo_my_query_vars'));
        $storeConfig = !empty($_SESSION['rentmy_config']) ? $_SESSION['rentmy_config'] : '';
        if (!empty($storeConfig) && !empty($storeConfig['customer']['wp']['sso']) && $storeConfig['customer']['wp']['sso']) {
            add_action('wp_login', array($this, 'rentmy_login'), 10, 2);
            add_action('wp_authenticate', array($this, 'rentmy_pre_login'), 30, 2);
            add_action('after_setup_theme', array($this, 'wp_user_login'));
        }

    }



    /**
     * Define WC Constants.
     */
    private function define_constants()
    {
        $upload_dir = wp_upload_dir(null, false);

        $this->define('RENTMY_ABSPATH', dirname(RENTMY_PLUGIN_FILE) . DIRECTORY_SEPARATOR);
        $this->define('RENTMY_PLUGIN_BASENAME', plugin_basename(RENTMY_PLUGIN_FILE));
        $this->define('RENTMY_VERSION', $this->version);
        $this->define('RENTMY_LOG_DIR', $upload_dir['basedir'] . '/rentmy-logs/');
        $this->define('RENTMY_TEMPLATE_DEBUG_MODE', false);
        $this->define('RENTMY_NOTICE_MIN_PHP_VERSION', '5.6.20');
        $this->define('RENTMY_NOTICE_MIN_WP_VERSION', '4.9');

        $this->define("RENTMY_S3_URL", "https://s3.us-east-2.amazonaws.com/images.rentmy.co");
        // $this->define("RENTMY_BASE_URL", "http://rentmy.test");
        // $this->define("RENTMY_API_URL", "http://rentmy.test/api");
        // $this->define("RENTMY_BASE_URL", "http://client-api-stage.rentmy.leaperdev.rocks");
        // $this->define("RENTMY_API_URL", "http://client-api-stage.rentmy.leaperdev.rocks/api");
      $this->define("RENTMY_BASE_URL", "https://clientapi.rentmy.co");
      $this->define("RENTMY_API_URL", "https://clientapi.rentmy.co/api");
        //   $this->define("RENTMY_BASE_URL", "http://192.168.0.7/rentmy");
        //   $this->define("RENTMY_API_URL", "http://192.168.0.7/rentmy/api");
        $this->define("RENTMY_PLACEHOLDER_IMAGE", plugin_dir_url(plugin_dir_path(__FILE__) . '../' . DIRECTORY_SEPARATOR . 'assets/images/rent-my-sample.png') . 'rent-my-sample.png');
    }

    function includes()
    {
        // add classes one by one
        if (!class_exists('RentMy_Category', false)) {
            include_once plugin_dir_path(__FILE__) . '../' . 'includes/class-rentmy-categories.php';
        }

        if (!class_exists('RentMy_Config', false)) {
            include_once plugin_dir_path(__FILE__) . '../' . 'includes/class-rentmy-config.php';
        }
        if (!class_exists('RentMy_Token', false)) {
            include_once plugin_dir_path(__FILE__) . '../' . 'includes/class-rentmy-token.php';
        }

        if (!class_exists('RentMy_Products', false)) {
            include_once plugin_dir_path(__FILE__) . '../' . 'includes/class-rentmy-products.php';
        }

        if (!class_exists('RentMy_Cart', false)) {
            include_once plugin_dir_path(__FILE__) . '../' . 'includes/class-rentmy-cart.php';
        }

        if (!class_exists('RentMy_Checkout', false)) {
            include_once plugin_dir_path(__FILE__) . '../' . 'includes/class-rentmy-checkout.php';
        }

        if (!class_exists('RentMy_Order', false)) {
            include_once plugin_dir_path(__FILE__) . '../' . 'includes/class-rentmy-order.php';
        }

        if (!class_exists('RentMy_Category_Widget', false)) {
            include_once plugin_dir_path(__FILE__) . '../' . 'includes/class-rentmy-category-widgets.php';
        }

        if (!class_exists('RentMy_Tags_Widget', false)) {
            include_once plugin_dir_path(__FILE__) . '../' . 'includes/class-rentmy-tags-widgets.php';
        }

        if (!class_exists('RentMy_Search_Widget', false)) {
            include_once plugin_dir_path(__FILE__) . '../' . 'includes/class-rentmy-search-widgets.php';
        }

        if (!class_exists('RentMy_MiniCart_Widget', false)) {
            include_once plugin_dir_path(__FILE__) . '../' . 'includes/class-rentmy-mini-cart-widget.php';
        }

        if (!class_exists('RentMy_OrderSummary_Widget', false)) {
            include_once plugin_dir_path(__FILE__) . '../' . 'includes/class-rentmy-order-summary-widgets.php';
        }

        if (!class_exists('RentMy_Ajax', false)) {
            include_once plugin_dir_path(__FILE__) . '../' . 'includes/class-rentmy-ajax.php';
        }

        if (!class_exists('RentMy_Customer', false)) {
            include_once plugin_dir_path(__FILE__) . '../' . 'includes/class-rentmy-customer.php';
        }

        if (!class_exists('RentMy_User', false)) {
            include_once plugin_dir_path(__FILE__) . '../' . 'includes/class-rentmy-user.php';
        }

        if (!class_exists('RentMy_WpFeatures', false)) {
            include_once plugin_dir_path(__FILE__) . '../' . 'includes/class-rentmy-wp-features.php';
            new RentMy_WpFeatures();
        }

        if (!is_admin()) {
            // add shortcodes and other files
            include_once plugin_dir_path(__FILE__) . '../' . 'short-codes/rentmy-categories-list.php';
            include_once plugin_dir_path(__FILE__) . '../' . 'short-codes/rentmy-tags-list.php';
            include_once plugin_dir_path(__FILE__) . '../' . 'short-codes/rentmy-search.php';
            include_once plugin_dir_path(__FILE__) . '../' . 'short-codes/rentmy-mini-cart.php';
            include_once plugin_dir_path(__FILE__) . '../' . 'short-codes/rentmy-order-summary.php';
            include_once plugin_dir_path(__FILE__) . '../' . 'short-codes/rentmy-products-list.php';

            if( !isset($this->store_contents) || empty($this->store_contents) ) {
                $rentmy_config = new RentMy_Config();
                $this->store_contents = $rentmy_config->store_contents();
            }

            if( isset($this->store_contents[0]['contents']['confg']['inventory']['display_mode']) && $this->store_contents[0]['contents']['confg']['inventory']['display_mode'] == 'modern' ) {
                include_once plugin_dir_path(__FILE__) . '../' . 'short-codes/rentmy-product-details-modern.php';
                include_once plugin_dir_path(__FILE__) . '../' . 'short-codes/rentmy-package-details-modern.php';
            } else {
                include_once plugin_dir_path(__FILE__) . '../' . 'short-codes/rentmy-product-details.php';
                include_once plugin_dir_path(__FILE__) . '../' . 'short-codes/rentmy-package-details.php';
            }
            include_once plugin_dir_path(__FILE__) . '../' . 'short-codes/rentmy-cart-details.php';
            include_once plugin_dir_path(__FILE__) . '../' . 'short-codes/rentmy-checkout.php';
            include_once plugin_dir_path(__FILE__) . '../' . 'short-codes/rentmy-checkout-step1.php';
            include_once plugin_dir_path(__FILE__) . '../' . 'short-codes/rentmy-checkout-step2.php';
            include_once plugin_dir_path(__FILE__) . '../' . 'short-codes/rentmy-checkout-step3.php';
            include_once plugin_dir_path(__FILE__) . '../' . 'short-codes/rentmy-checkout-step4.php';
            include_once plugin_dir_path(__FILE__) . '../' . 'short-codes/rentmy-single-page-checkout.php';
            include_once plugin_dir_path(__FILE__) . '../' . 'short-codes/rentmy-customer-login.php';
            include_once plugin_dir_path(__FILE__) . '../' . 'short-codes/rentmy-customer-login-modal.php';
            include_once plugin_dir_path(__FILE__) . '../' . 'short-codes/rentmy-customer-profile.php';
            include_once plugin_dir_path(__FILE__) . '../' . 'short-codes/rentmy-customer-reset-password.php';
            include_once plugin_dir_path(__FILE__) . '../' . 'short-codes/rentmy-user-registration.php';
            include_once plugin_dir_path(__FILE__) . '../' . 'short-codes/rentmy-order-review.php';
            include_once plugin_dir_path(__FILE__) . '../' . 'short-codes/rentmy-products-list-with-filter.php';
            include_once plugin_dir_path(__FILE__) . '../' . 'short-codes/rentmy-grid.php';
            include_once plugin_dir_path(__FILE__) . '../' . 'short-codes/rentmy-order-details.php';
        }
    }


    /**
     * Define constant if not already set.
     *
     * @param string $name Constant name.
     * @param string|bool $value Constant value.
     */
    private function define($name, $value)
    {
        if (!defined($name)) {
            define($name, $value);
        }
    }

    //rent my generic api calls base function
    public function fetch($slashedPath = null, $token = null, $postFields = [], $queryParams = [])
    {
        // Create a new cURL resource
        $curl = curl_init();
        $post_fields_string = null;
        $get_fields_string = null;
        $html = null;
        $error = null;

        if (!$curl) {
            $error = "Couldn't initialize a cURL handle";
            return $error;
        }

        if (!$slashedPath) {
            $error = "API PATH is not specified properly";
            return $error;
        }

        //url-ify the data for the POST
        if (!empty($postFields)) {
            foreach ($postFields as $key => $value) {
                //$post_fields_string .= $key . '=' . $value . '&';
            }
            $post_fields_string = urldecode(http_build_query($postFields));
            rtrim($post_fields_string, '&');
            curl_setopt($curl, CURLOPT_POST, count($postFields));
            curl_setopt($curl, CURLOPT_POSTFIELDS, $post_fields_string);
        }
        //url-ify the data for the GET
        if (!empty($queryParams)) {
            foreach ($queryParams as $key => $value) {
                $get_fields_string .= $key . '=' . $value . '&';
            }
            rtrim($get_fields_string, '&');
            $get_fields_string = '?' . $get_fields_string;
        }

        if (!empty($token)) {
            $headers_array = [
                'Accept: application/json, text/plain, */*',
                'User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/77.0.3865.90 Safari/537.36'
            ];
            if (is_array($token)) {
                if (!empty($token['token'])) {
                    $authorization = "Authorization: Bearer " . $token['token'];
                    array_push($headers_array, $authorization);
                }
                if (!empty($token['location'])) {
                    $location = "Location: " . $token['location'];
                    array_push($headers_array, $location);
                }
            } else {
                $authorization = "Authorization: Bearer " . $token;
                array_push($headers_array, $authorization);
            }
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers_array);
        }

        $api_url = RENTMY_API_URL . $slashedPath . $get_fields_string;
        // Set the file URL to fetch through cURL
        curl_setopt($curl, CURLOPT_URL, $api_url);
        // Follow redirects, if any
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        // Fail the cURL request if response code = 400 (like 404 errors)
        curl_setopt($curl, CURLOPT_FAILONERROR, true);
        // Return the actual result of the curl result instead of success code
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        // Wait for 10 seconds to connect, set 0 to wait indefinitely
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
        // Execute the cURL request for a maximum of 50 seconds
        curl_setopt($curl, CURLOPT_TIMEOUT, 50);
        // Do not check the SSL certificates
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        // Fetch the URL and save the content in $html variable
        $html = curl_exec($curl);
        // Check if any error has occurred
        if (curl_errno($curl)) {
            $error = 'cURL error: ' . curl_error($curl);
        } else {
            // cURL executed successfully
            $error = curl_getinfo($curl);
        }
        // close cURL resource to free up system resources
        curl_close($curl);
        return !empty($html) ? json_decode($html, true)['result'] : $error;
    }

    //rent my generic api calls base function
    public function rentmy_fetch($slashedPath = null, $token = null, $postFields = [], $queryParams = [])
    {
        // Create a new cURL resource
        $curl = curl_init();
        $post_fields_string = null;
        $get_fields_string = null;
        $html = null;
        $error = null;

        if (!$curl) {
            $error = "Couldn't initialize a cURL handle";
            return $error;
        }

        if (!$slashedPath) {
            $error = "API PATH is not specified properly";
            return $error;
        }

        //url-ify the data for the POST
        if (!empty($postFields)) {
            foreach ($postFields as $key => $value) {
                //$post_fields_string .= $key . '=' . $value . '&';
            }
            $post_fields_string = urldecode(http_build_query($postFields));
            rtrim($post_fields_string, '&');
            curl_setopt($curl, CURLOPT_POST, count($postFields));
            curl_setopt($curl, CURLOPT_POSTFIELDS, $post_fields_string);
        }
        //url-ify the data for the GET
        if (!empty($queryParams)) {
            foreach ($queryParams as $key => $value) {
                $get_fields_string .= $key . '=' . $value . '&';
            }
            rtrim($get_fields_string, '&');
            $get_fields_string = '?' . $get_fields_string;
        }

        if (!empty($token)) {
            $headers_array = [
                'Accept: application/json, text/plain, */*',
                'User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/77.0.3865.90 Safari/537.36'
            ];
            if (is_array($token)) {
                if (!empty($token['token'])) {
                    $authorization = "Authorization: Bearer " . $token['token'];
                    array_push($headers_array, $authorization);
                }
                if (!empty($token['location'])) {
                    $location = "Location: " . $token['location'];
                    array_push($headers_array, $location);
                }
            } else {
                $authorization = "Authorization: Bearer " . $token;
                array_push($headers_array, $authorization);
            }
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers_array);
        }

        $api_url = RENTMY_API_URL . $slashedPath . $get_fields_string;
        // Set the file URL to fetch through cURL
        curl_setopt($curl, CURLOPT_URL, $api_url);
        // Follow redirects, if any
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        // Fail the cURL request if response code = 400 (like 404 errors)
        curl_setopt($curl, CURLOPT_FAILONERROR, true);
        // Return the actual result of the curl result instead of success code
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        // Wait for 10 seconds to connect, set 0 to wait indefinitely
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
        // Execute the cURL request for a maximum of 50 seconds
        curl_setopt($curl, CURLOPT_TIMEOUT, 50);
        // Do not check the SSL certificates
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        // Fetch the URL and save the content in $html variable
        $html = curl_exec($curl);
        // Check if any error has occurred
        if (curl_errno($curl)) {
            $error = 'cURL error: ' . curl_error($curl);
        } else {
            // cURL executed successfully
            $error = curl_getinfo($curl);
        }
        // close cURL resource to free up system resources
        curl_close($curl);
        return !empty($html) ? json_decode($html, true) : $error;
    }

    function http_delete($api_endpoint = null, $token = null)
    {

        $curl = curl_init();
        $html = null;
        $error = null;

        if (!$curl) {
            $error = "Couldn't initialize a cURL handle";
            return $error;
        }

        if (!$api_endpoint) {
            $error = "API PATH is not specified properly";
            return $error;
        }
        if (!empty($token)) {
            $headers_array = [
                'Accept: application/json, text/plain, */*',
                'User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/77.0.3865.90 Safari/537.36'
            ];
            if (is_array($token)) {
                if (!empty($token['token'])) {
                    $authorization = "Authorization: Bearer " . $token['token'];
                    array_push($headers_array, $authorization);
                }
                if (!empty($token['location'])) {
                    $location = "Location: " . $token['location'];
                    array_push($headers_array, $location);
                }
            } else {
                $authorization = "Authorization: Bearer " . $token;
                array_push($headers_array, $authorization);
            }
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers_array);
        }
        $url = $api_url = RENTMY_API_URL . $api_endpoint;

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
        $html = curl_exec($curl);
        // Check if any error has occurred
        if (curl_errno($curl)) {
            $error = 'cURL error: ' . curl_error($curl);
        } else {
            // cURL executed successfully
            $error = curl_getinfo($curl);
        }
        // close cURL resource to free up system resources
        curl_close($curl);
        return !empty($html) ? json_decode($html, true) : $error;

        return $result;
    }


    function rentmy_activate()
    {
        $pages_to_create = [
            (object)[
                'page_title' => 'RentMy Products List With Filter',
                'page_content' => '[rentmy-products-list-with-filter]',
                'post_status'=>'publish',
                'post_type'=>'page',
                'comment_status'=>'closed',
                'ping_status'=>'closed',
                'post_name'=>'rentmy-catalog',
            ],
            (object)[
                'page_title' => 'RentMy Product Details',
                'page_content' => '[rentmy-products-details]',
                'post_status'=>'publish',
                'post_type'=>'page',
                'comment_status'=>'closed',
                'ping_status'=>'closed',
                'post_name'=>'rentmy-product-details',
            ],
            (object)[
                'page_title' => 'RentMy Package Details',
                'page_content' => '[rentmy-package-details]',
                'post_status'=>'publish',
                'post_type'=>'page',
                'comment_status'=>'closed',
                'ping_status'=>'closed',
                'post_name'=>'rentmy-package-details'
            ],
            (object)[
                'page_title' => 'RentMy Products List',
                'page_content' => '[rentmy-products-list]',
                'post_status'=>'publish',
                'post_type'=>'page',
                'comment_status'=>'closed',
                'ping_status'=>'closed',
                'post_name'=>'rentmy-products-list',
            ],
            (object)[
                'page_title' => 'RentMy Cart',
                'page_content' => '[rentmy-cart-details]',
                'post_status'=>'publish',
                'post_type'=>'page',
                'comment_status'=>'closed',
                'ping_status'=>'closed',
                'post_name'=>'rentmy-cart',
            ],
            (object)[
                'page_title' => 'RentMy Checkout',
                'page_content' => '[rentmy-checkout]',
                'post_status'=>'publish',
                'post_type'=>'page',
                'comment_status'=>'closed',
                'ping_status'=>'closed',
                'post_name'=>'rentmy-checkout',

            ],
            (object)[
                'page_title' => 'Customer Login',
                'page_content' => '[rentmy-customer-login]',
                'post_status'=>'publish',
                'post_type'=>'page',
                'comment_status'=>'closed',
                'ping_status'=>'closed',
                'post_name'=>'rentmy-customer-login',
            ],
            (object)[
                'page_title' => 'Customer Profile',
                'page_content' => '[rentmy-customer-profile]',
                'post_status'=>'publish',
                'post_type'=>'page',
                'comment_status'=>'closed',
                'ping_status'=>'closed',
                'post_name'=>'rentmy-customer-profile',
            ],
            (object)[
                'page_title' => 'Customer Reset Password',
                'page_content' => '[rentmy-customer-reset-password]',
                'post_status'=>'publish',
                'post_type'=>'page',
                'comment_status'=>'closed',
                'ping_status'=>'closed',
                'post_name'=>'rentmy-reset-password',
            ],
            (object)[
                'page_title' => 'RentMy User registration',
                'page_content' => '[rentmy-user-registration]',
                'post_status'=>'publish',
                'post_type'=>'page',
                'comment_status'=>'closed',
                'ping_status'=>'closed',
                'post_name'=>'rentmy-user-registration',
            ],
            (object)[
                'page_title' => 'Customer Orders',
                'page_content' => '[rentmy-order-review]',
                'post_status'=>'publish',
                'post_type'=>'page',
                'comment_status'=>'closed',
                'ping_status'=>'closed',
                'post_name'=>'rentmy-orders',
            ]
        ];

        $optionArray = [
                'rentmy-catalog' => 'rentmy.page_url.products_list_with_filter',
                'rentmy-products-list' => 'rentmy.page_url.products_list',
                'rentmy-product-details' => 'rentmy.page_url.product_details',
                'rentmy-package-details' => 'rentmy.page_url.package_details',
                'rentmy-cart' => 'rentmy.page_url.cart',
                'rentmy-checkout' => 'rentmy.page_url.checkout',
                'rentmy-customer-login' => 'rentmy.page_url.customer_login',
                'rentmy-customer-profile' => 'rentmy.page_url.profile',
                'rentmy-reset-password' => 'rentmy.page_url.reset_password',
                'rentmy-user-registration' => 'rentmy.page_url.registration',
                'rentmy-orders' => 'rentmy.order'
        ];

        foreach ($pages_to_create as $keys => $pages) {
            $pageId = get_option($optionArray[$pages->post_name]);
            $the_page = get_post($pageId);

            if (empty($the_page)) {
                $_p = array();
                $_p['post_title'] = $pages->page_title;
                $_p['post_content'] = $pages->page_content;
                $_p['post_status'] = $pages->post_status;
                $_p['post_type'] = $pages->post_type;
                $_p['comment_status'] = $pages->comment_status;
                $_p['ping_status'] = $pages->ping_status;
                $_p['post_name'] = $pages->post_name;

                $pageId = wp_insert_post($_p);
            } else {
                $the_page->post_status = 'publish';
                $pageId = wp_update_post($the_page);
            }
            update_option($optionArray[$pages->post_name], $pageId);
        }

        $roles = [
            (object)[
                'role' => 'customer',
                'display_name' => 'Customer'
            ],
            (object)[
                'role' => 'cashier',
                'display_name' => 'Cashier'
            ],
            (object)[
                'role' => 'rentmy-user',
                'display_name' => 'Rentmy User'
            ],
            (object)[
                'role' => 'admin',
                'display_name' => 'Admin'
            ]
        ];

        foreach ($roles as $role) {
            wp_roles()->add_role($role->role, $role->display_name, $capabilities = '');
        }

    }

    function rentmy_deactivate()
    {
        add_action('admin_bar_menu', function ($wp_admin_bar) {
            $wp_admin_bar->remove_node('rent-my-products-list');
        }, 55);
        return;
    }

    function add_customer_login_footer()
    {
        do_shortcode("[rentmy-customer-login-modal]", false);
    }

    function rentmy_admin_bar_menu_add($wp_admin_bar)
    {
        $params = [
            'id' => 'rent-my-products-list',
            'title' => 'Rent My Product List Page',
            'href' => getRentMyParmalink("rentmy.page_url.products_list"),
            'meta' => [
                'class' => 'custom-node-class'
            ]
        ];
        $wp_admin_bar->add_node($params);

        $params = [
            'id' => 'rent-my-cart',
            'title' => 'Rent My Cart Details Page',
            'href' => getRentMyParmalink("rentmy.page_url.cart"),
            'meta' => [
                'class' => 'custom-node-class'
            ]
        ];
        $wp_admin_bar->add_node($params);
    }

    function rentmy_body_classes($classes)
    {
        $classes[] = 'has-sidebar rentmy-page';
        return $classes;
    }

    function rentmy_default_widget_registers()
    {
        // Register our own widget.
        register_widget('RentMy_Category_Widget');
        register_widget('RentMy_Tags_Widget');
        register_widget('RentMy_Search_Widget');
        register_widget('RentMy_MiniCart_Widget');
        register_widget('RentMy_OrderSummary_Widget');
    }

    function rentmy_constant_variables_load_head()
    {
        (new RentMy_Config())->store_config();
        $rentmy_config = get_option('rentmy_config');
?>
        <script>
            var rentmy_base_file_url = "<?php echo RENTMY_S3_URL . '/'; ?>";
            var rentmy_asset_url = "<?php echo plugin_dir_url(__DIR__) . 'assets'; ?>";
            var rentmy_home_url = "<?php echo home_url(); ?>";
            var rentmy_cart_url = "<?php echo getRentMyParmalink('rentmy.page_url.cart'); ?>";
            var rentmy_config_data_preloaded = <?php echo json_encode($rentmy_config); ?>;
            var rentmy_store_id = <?php echo get_option('rentmy_storeId');?>;
        </script>
<?php
    }

    function rentmy_add_search_box_to_menu($items, $params)
    {
        if ($params->theme_location == 'primary') {
            $search_value = !empty($_GET['search']) ? $_GET['search'] : null;
            return $items . "
            <li><a href=" . get_option("rentmy.page_url.products_list") . ">Product List</a></li>
            <li><a href=" . get_option("rentmy.page_url.cart") . ">Cart</a></li>
            <li><a href=" . getRentMyParmalink('rentmy.page_url.checkout') . '?step=info' . ">Checkout</a></li>
            <li class='rentmy-menu-header-search'>
                <form action='" . get_option("rentmy.page_url.products_list") . "' method='get' class='rentmy-search-form' accept-charset='ISO-8859-1'>
                    <input type='text' name='search' placeholder='Search' value='" . $search_value . "'>
                </form>
            </li>";
        }
        return $items;
    }

    //render image link along from the product itself
    public static function imageLink($product_id, $image, $type = 'list')
    {
        if ($type == 'list') {
            if (empty($image)) {
                return esc_url(plugins_url('../assets/images/product-image-placeholder.jpg', __FILE__));
            } else {
                return RENTMY_S3_URL . '/products/' . get_option('rentmy_storeId') . '/' . $product_id . '/' . $image;
            }
        } elseif ($type == 'small') {
            if (empty($image)) {
                return esc_url(plugins_url('../assets/images/product-image-placeholder.jpg', __FILE__));
            } else {
                return RENTMY_S3_URL . '/products/' . get_option('rentmy_storeId') . '/' . $product_id . '/' . $image;
            }
        }
    }

    function add_rentmy_scripts()
    {

        if( !isset($this->store_contents) || empty($this->store_contents) ) {
            $rentmy_config = new RentMy_Config();
            $this->store_contents = $rentmy_config->store_contents();
        }

        wp_enqueue_style('rentmy-styles', plugins_url('assets/css/rentmy-styles.css', RENTMY_PLUGIN_FILE));
        wp_enqueue_style('rentmy-grid', plugins_url('assets/css/rentmy-grid.css', RENTMY_PLUGIN_FILE));
        wp_enqueue_style('rentmy-font-awesome-icons', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css');
        wp_enqueue_style('rentmy-bootstrap', 'https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css');
        wp_enqueue_style('rentmy-vendor-styles', plugins_url('assets/css/vendor.css', RENTMY_PLUGIN_FILE));

        // custom single page checkout with vuejs
        wp_enqueue_style('rentmy-single-page-custom', plugins_url('assets/css/rentmy-single-page-checkout-custom.css', RENTMY_PLUGIN_FILE));
        wp_enqueue_style('rentmy-roboto-font-load', 'https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900');
        wp_enqueue_script('rentmy-axios-library', 'https://unpkg.com/axios@0.19.2/dist/axios.min.js');
        wp_enqueue_script('rentmy-vue', 'https://cdn.jsdelivr.net/npm/vue@2.6.14');
        wp_enqueue_script('rentmy-vue-loader', 'https://unpkg.com/http-vue-loader');
        wp_enqueue_script('rentmy-vue-paginate', 'https://unpkg.com/vuejs-paginate@0.9.0');
        wp_enqueue_script('rentmy-botostrap-js', 'https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js', array('jquery'));
        // ends


        wp_enqueue_script('rentmy-vendor-js', plugins_url('assets/js/vendor.js', RENTMY_PLUGIN_FILE), array('jquery'), null, false);
        wp_enqueue_script('rentmy-cookie-manager-js', plugins_url('assets/js/rentmy-cookieJar.js', RENTMY_PLUGIN_FILE));
        wp_enqueue_script('algolia-js', 'https://cdn.jsdelivr.net/npm/places.js@1.16.6');
        wp_enqueue_script('checkout-js', plugins_url('assets/js/checkout.js', RENTMY_PLUGIN_FILE), array('jquery'));
        wp_enqueue_script('customer-js', plugins_url('assets/js/customer.js', RENTMY_PLUGIN_FILE), array('jquery'));
        if( isset($this->store_contents[0]['contents']['confg']['inventory']['display_mode']) && $this->store_contents[0]['contents']['confg']['inventory']['display_mode'] == 'modern' ) {
            wp_enqueue_script('rentmy-products-js', plugins_url('assets/js/products-modern.js', RENTMY_PLUGIN_FILE), array('jquery'), '3.16');
        } else {
            wp_enqueue_script('rentmy-products-js', plugins_url('assets/js/products.js', RENTMY_PLUGIN_FILE), array('jquery'), '3.16');
        }

        wp_enqueue_script('stripe-js-v3', 'https://js.stripe.com/v3/');

        if (isset($_GET['rm_payment']) && $_GET['rm_payment']=='test'){
            wp_enqueue_script('square-js-v2', 'https://sandbox.web.squarecdn.com/v1/square.js'); //test link
        }else{
            wp_enqueue_script('square-js-v2', 'https://web.squarecdn.com/v1/square.js'); //live link
        }


        wp_localize_script('checkout-js', 'rentmy_ajax_object', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
        ));

        wp_localize_script('customer-js', 'rentmy_ajax_object', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
        ));

        wp_localize_script('rentmy-products-js', 'rentmy_ajax_object', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
        ));
    }

    /**
     * Load configs on load
     */
    public function getConfig()
    {
        if (!is_admin()) {
            $this->storeConfig();
        }
    }

    public static function pr($var)
    {
        print_r("<pre>");
        print_r($var);
        print_r("</pre>");
    }

    /**
     * @param int $amount
     * @param string $pre_class
     * @param string $amount_class
     * @param string $post_class
     * @return string
     */
    public static function currency($amount = 0, $pre_class = 'pre', $amount_class = 'amount', $post_class = 'post')
    {
        $config = $_SESSION['rentmy_config'];
        $currency = !empty($config['currency_format']) ? $config['currency_format'] : '';
        if (empty($amount)) {
            $amount = 0;
        }
        // print_r($currency);
        $local = isset($currency['locale']) ? $currency['locale'] : 'en-US';
        if (class_exists('NumberFormatter')){
            $formatter = new NumberFormatter($local, NumberFormatter::CURRENCY);
            $amount =  $formatter->formatCurrency($amount, 'USD');
            $amount = str_replace('US', '', $amount);
            $amount = str_replace('$', '', $amount);
        }else{
            $amount = number_format($amount, 2);
        }


        if (!empty($currency)) {
            $html = '';
            if ($currency['pre']) {
                $html .= "<span class='" . $pre_class . "'>" . $currency['symbol'] . "</span>";
            }
            $html .= "<span class='" . $amount_class . "'>" . $amount . "</span>";
            if ($currency['post']) {
                $html .= "<span class='" . $post_class . "'>" . $currency['code'] . " </span>";
            }
        } else {
            $html = "<span class='" . $pre_class . "'>$</span><span class='" . $amount_class . "'>" . $amount . "</span><span class='" . $post_class . "'> USD</span>";
        }
        return $html;
    }

    /** Get store Config */
    public function storeConfig()
    {
        try {
            if (!session_id()) {
                session_start();
            }
            if (empty($_SESSION['rentmy_config'])) {
                $_SESSION['rentmy_config'] = get_option('rentmy_config');
            }
        } catch (Exception $e) {
        }
    }

    /**
     * Pagination
     * @param int $page
     * @param $total
     * @param int $limit
     * @param int $adjacents
     * @param $targetpage
     * @return string
     */
    public static function paginate($page = 1, $total = 1, $limit = 12, $adjacents = 3, $targetpage = 1)
    {
        $lastpage = ceil($total / $limit);
        $prev = $page - 1;                            //previous page is page - 1
        $next = $page + 1;
        $lpm1 = $lastpage - 1;
        $pagination = "";

        if ($lastpage > 1) {
            $pagination .= "<div class=\"rentmy-pagination\">";
            //previous button
            if ($page > 1)
                $pagination .= "<a href=\"$targetpage&page_no=$prev\">Previous</a>";
            else
                $pagination .= "<span class=\"disabled\">Previous</span>";

            //pages
            if ($lastpage < 7 + ($adjacents * 2))    //not enough pages to bother breaking it up
            {
                for ($counter = 1; $counter <= $lastpage; $counter++) {
                    if ($counter == $page)
                        $pagination .= "<span class=\"current\">$counter</span>";
                    else
                        $pagination .= "<a href=\"$targetpage&page_no=$counter\">$counter</a>";
                }
            } elseif ($lastpage > 5 + ($adjacents * 2))    //enough pages to hide some
            {
                //close to beginning; only hide later pages
                if ($page < 1 + ($adjacents * 2)) {
                    for ($counter = 1; $counter < 4 + ($adjacents * 2); $counter++) {
                        if ($counter == $page)
                            $pagination .= "<span class=\"current\">$counter</span>";
                        else
                            $pagination .= "<a href=\"$targetpage&page_no=$counter\">$counter</a>";
                    }
                    $pagination .= "...";
                    $pagination .= "<a href=\"$targetpage&page_no=$lpm1\">$lpm1</a>";
                    $pagination .= "<a href=\"$targetpage&page_no=$lastpage\">$lastpage</a>";
                } //in middle; hide some front and some back
                elseif ($lastpage - ($adjacents * 2) > $page && $page > ($adjacents * 2)) {
                    $pagination .= "<a href=\"$targetpage&page_no=1\">1</a>";
                    $pagination .= "<a href=\"$targetpage&page_no=2\">2</a>";
                    $pagination .= "...";
                    for ($counter = $page - $adjacents; $counter <= $page + $adjacents; $counter++) {
                        if ($counter == $page)
                            $pagination .= "<span class=\"current\">$counter</span>";
                        else
                            $pagination .= "<a href=\"$targetpage&page_no=$counter\">$counter</a>";
                    }
                    $pagination .= "...";
                    $pagination .= "<a href=\"$targetpage&page_no=$lpm1\">$lpm1</a>";
                    $pagination .= "<a href=\"$targetpage&page_no=$lastpage\">$lastpage</a>";
                } //close to end; only hide early pages
                else {
                    $pagination .= "<a href=\"$targetpage&page_no=1\">1</a>";
                    $pagination .= "<a href=\"$targetpage&page_no=2\">2</a>";
                    $pagination .= "...";
                    for ($counter = $lastpage - (2 + ($adjacents * 2)); $counter <= $lastpage; $counter++) {
                        if ($counter == $page)
                            $pagination .= "<span class=\"current\">$counter</span>";
                        else
                            $pagination .= "<a href=\"$targetpage&page_no=$counter\">$counter</a>";
                    }
                }
            }

            //next button
            if ($page < $counter - 1)
                $pagination .= "<a href=\"$targetpage&page_no=$next\">next</a>";
            else
                $pagination .= "<span class=\"disabled\">next</span>";
            $pagination .= "</div>\n";
        }
        return $pagination;
    }

    /**
     * @deprecated
     * @param $duration_name
     * @param $config
     */
    function contents_rental_duration_labels($duration_name, $config)
    {
        if ($duration_name == 'hour') {
            $label = $config['others']['lbl_hour'];
        } elseif ($duration_name == 'hours') {
            $label = $config['others']['lbl_hours'];
        } elseif ($duration_name == 'day') {
            $label = $config['others']['lbl_day'];
        } elseif ($duration_name == 'days') {
            $label = $config['others']['lbl_days'];
        } elseif ($duration_name == 'week') {
            $label = $config['others']['lbl_week'];
        } elseif ($duration_name == 'weeks') {
            $label = $config['others']['lbl_weeks'];
        } elseif ($duration_name == 'month') {
            $label = $config['others']['lbl_month'];
        } elseif ($duration_name == 'months') {
            $label = $config['others']['lbl_months'];
        } else {
            $label = '';
        }
        return $label;
    }

    public function rentmy_login($user_name, $user)
    {
        $userObj = new RentMy_User();

        $params = [
            'email' => $user->data->user_email,
            'password' => $_SESSION['RentMy']['user']['password']
        ];
        $userObj->login($params);
    }

    public function rentmy_pre_login($user_name, $password)
    {
        $_SESSION['RentMy']['user']['username'] = $user_name;
        $_SESSION['RentMy']['user']['password'] = $password;
    }

    public function wp_user_login($credentials)
    {

        if (!empty($credentials)) {
            $user = wp_signon($credentials, false);
            return is_wp_error($user);
        }
    }

    /**
     * format labels
     * @param $duration
     * @param $label
     * @return string
     */
    public function format_price_label($duration, $label)
    {
        $format_label = '';
        if (in_array($label, ['hour', 'day', 'week', 'month'])) {
            if ($label == 'hour') {
                $format_label = $duration > 1 ? $GLOBALS['store_text']['others']['lbl_hours'] : $GLOBALS['store_text']['others']['lbl_hour'];
            } elseif ($label == 'day') {
                $format_label = $duration > 1 ? $GLOBALS['store_text']['others']['lbl_days'] : $GLOBALS['store_text']['others']['lbl_day'];
            } elseif ($label == 'week') {
                $format_label = $duration > 1 ? $GLOBALS['store_text']['others']['lbl_weeks'] : $GLOBALS['store_text']['others']['lbl_week'];
            } elseif ($label == 'month') {
                $format_label = $duration > 1 ? $GLOBALS['store_text']['others']['lbl_months'] : $GLOBALS['store_text']['others']['lbl_month'];
            }
        }
        return $format_label;
    }

    function myplugin_rewrite_rule()
    {
        add_rewrite_rule(
            '^orders/([^/]+)([/]?)(.*)',
            'index.php?pagename=orders&order_id=$matches[1]',
            'top'
        );
    }
    function foo_my_query_vars($vars)
    {
        $vars[] = 'pagename';
        $vars[] = 'order_id';
        return $vars;
    }

    function setCookie($data, $time = 1440)
    {
        setcookie('rentmy_customer_info', json_encode($data, true), time() + ($time)); // 1440 = 24min
    }

    function setUserCookie()
    {
        if(!empty($_SESSION['customer_info'])){
            setcookie('rentmy_customer_info', json_encode($_SESSION['customer_info'], true), time() + (1440)); // 1440 = 24min
        }
    }

    /**
     * @param $cart
     * @return bool
     */
    public function isCartEnduring($cart){
        if (empty($cart) && empty($cart['cart_items']))
            return false;

        foreach ($cart['cart_items'] as $item){

            $options =  $item['options'];
            if (!empty($options) && !is_array($options))
                $options = json_decode($options, true);


            if (!empty($options['recurring']['duration_type']))
                return true;

        }
        return false;
    }

    public function engToFrenchDate($str){
//        2277 is JETT Mobility store id
        $storeId = get_option('rentmy_storeId');
        if ($storeId != 2277)
            return $str;
            
        $months = [
            "Jan"=>"Jan",
            "Feb"=>"Fév",
            "March"=>"Mars",
            "April"=>"Avril",
            "May"=>"Peut",
            "June"=>"Juin",
            "July"=>"Juillet",
            "Aug"=>"Août",
            "Sept"=>"Septembre",
            "Oct"=>"Oct",
            "Nov"=>"Nov",
            "Dec"=>"Déc",
        ];
        $searchArr = [];
        $replaceArr = [];
        foreach ($months as $eng=>$franch){
            $searchArr[] = $eng;
            $replaceArr[] = $franch;
        }
        $str = str_replace($searchArr, $replaceArr, $str);
        return $str;
    }
}
