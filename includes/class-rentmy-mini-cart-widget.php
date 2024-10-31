<?php
/**
 * Super simple widget.
 */
class RentMy_MiniCart_Widget extends WP_Widget
{
    public function __construct()
    {                      // id_base        ,  visible name
        parent::__construct( 'rentmy_minicart_widget', 'RentMy Mini Cart' );
    }

    public $args = array(
        'before_title'  => '<h4 class="widgettitle">',
        'after_title'   => '</h4>',
        'before_widget' => '<div class="widget-wrap">',
        'after_widget'  => '</div></div>'
    );

    public function widget( $args, $instance )
    {
        echo do_shortcode('[rentmy-mini-cart]');
    }
}
