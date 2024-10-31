<?php

class RentMy_WpFeatures extends RentMy{

    public function __construct()
    {
        add_action('template_redirect', array($this, 'redirectControl'));
        add_action('wp_nav_menu_items', array($this, 'displayMiniCartOnTheMenu'), 10, 2);
    }

    public function displayMiniCartOnTheMenu($items, $args)
    {
        if (!empty(get_nav_menu_locations())) {
            foreach (get_nav_menu_locations() as $menu_id => $index) {
                if ($args->theme_location == $menu_id) {
                    /**
                     * Finding rentmy-mini-cart shortcode
                     * support 0 to infinity params to grave by preg_match
                     */
                    preg_match("/\[rentmy-mini-cart([a-zA-Z=\d\-_\s;]+)?\]/", $items, $matched);
                    if(!empty($matched)){
                        ob_start(); 
                        $matched = $matched[0];
                        do_shortcode($matched);
                        $contents = ob_get_clean();
                        $items = str_replace($matched, $contents, $items);
                        return $items;
                    } 
                }
            }
        }
        return $items;
    }

    public function redirectControl(){
                
        $rntm_login_page_id = get_option('rentmy.page_url.customer_login');
        $rntm_profile_page_id = get_option('rentmy.page_url.profile');

        if ((!empty($_SESSION['customer_info']) || !empty($_COOKIE['rentmy_customer_info'])) && get_the_ID() == $rntm_login_page_id) {
            wp_redirect(getRentMyParmalink('rentmy.page_url.profile'));
            exit;
        }
        if ((empty($_SESSION['customer_info']) && empty($_COOKIE['rentmy_customer_info'])) && get_the_ID() == $rntm_profile_page_id) {
            wp_redirect(getRentMyParmalink('rentmy.page_url.customer_login'));
            exit;
        }        
    }
}