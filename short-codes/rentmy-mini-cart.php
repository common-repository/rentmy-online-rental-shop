<?php
function rentmy_mini_cart_template()
{
?>
    <div class="rentmy-plugin-manincontent">
        <a class="cart-bar">
            <i class="fa fa-shopping-bag"></i>
            <span class="cart-item cart-item-total-count-topbar">0</span>
        </a>

        <div class="cart-body">
            <span class="top-arrow"><i class="fa fa-caret-up"></i></span>
            <div class="inner-cart-body inner-cart-body-topbar">
                <i class="fa fa-smile-o fa-5x mt-5" aria-hidden="true"></i>
                <p class="text-center">No Products in cart</p>
            </div>
            <div class="carthome-total" style="display:none">
                <h5> Cart Total <span class="cart-item-total-topbar"></span></h5>
                <a href="<?php echo getRentMyParmalink('rentmy.page_url.checkout') . '?step=info'; ?>" class="button lbtn-50 theme-btn lbtn-xs radius">Checkout</a>
                <a href="<?php echo getRentMyParmalink('rentmy.page_url.cart') ?>" class="button lbtn-50 theme-btn lbtn-xs radius">View
                    Cart</a>
            </div>
        </div>
    </div>

    <?php
}
add_shortcode('rentmy-mini-cart', 'rentmy_mini_cart_template');