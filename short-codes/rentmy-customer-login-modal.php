<?php
//short code for product details of a product
function rentmy_customer_login_modal_shortcode()
{

    ob_start();
    $rentmy_config = new RentMy_Config();
    $store_content = $rentmy_config->store_contents();

    if (!empty($store_content)) {
        $GLOBALS['store_text'] = $store_content[0]['contents'];
        $GLOBALS['store_config'] = $store_content[0]['contents']['confg'];
    }
    rentmy_customer_login_modal_template();
}

add_shortcode('rentmy-customer-login-modal', 'rentmy_customer_login_modal_shortcode');

function rentmy_customer_login_modal_template()
{
    $GLOBALS['rm_countries'] = (new RentMy_Config())->countries();
    $store_country = 'US';
    if (!empty(get_option('rentmy_storeCountry')) && (get_option('rentmy_storeCountry')!='')){
        $store_country = get_option('rentmy_storeCountry');
    }
?>

<!--    <a class="rentmy-customer-login" data-toggle="modal" data-target="#rentmy-customer-login-modal">-->
<!--        <i class="fa fa-sign-in" aria-hidden="true"> Login</i>-->
<!--    </a>-->
    <script>
        var rm_store_config = <?php echo json_encode($GLOBALS['store_config'], true); ?>;

    </script>
    <div class="modal fade rentmy-customer-login-modal" id="rentmy-customer-login-modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="card mx-auto border-0 login-register-content">
                        <nav>
                            <div class="nav nav-tabs justify-content-center" id="nav-tab" role="tablist">
                                <a class="nav-item nav-link active" id="nav-home-tab" data-toggle="tab" href="#nav-home">Login</a>
                                <a class="nav-item nav-link" id="nav-profile-tab" data-toggle="tab" href="#nav-profile">Register</a>
                                <?php if(!empty($GLOBALS['store_config']) && isset($GLOBALS['store_config']['customer']['wp']['sso']) && !$GLOBALS['store_config']['customer']['wp']['sso']){?>
                                <a class="login-close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></a>
                                <?php } ?>
                            </div>
                        </nav>
                        <div class="tab-content" id="nav-tabContent">
                            <div class="tab-pane fade show active" id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab">
                                <div class="card-body pb-4 pt-2">
                                    <div class="userlogin-box">
                                        <div class="userlogin-body">
                                            <form class="loginform" id="rm_customer_login_form">
                                                <span class="rm-customer-login-error text-danger"></span>
                                                <div class="form-group mt-0 mb-4">
                                                    <input type="text" name="email" class="input-field" placeholder="Email Address" formControlName="email">
                                                </div>
                                                <div class="form-group mt-0 mb-4">
                                                    <input type="password" name="password" class="input-field" placeholder="Password" formControlName="password">
                                                </div>
                                                <div class="form-group text-center mt-0 mb-0 fa-3x" >
                                                    <?php if(!empty($GLOBALS['store_config']) && isset($GLOBALS['store_config']['customer']['wp']['sso']) && !$GLOBALS['store_config']['customer']['wp']['sso']){?>
                                                    <a href="<?php echo getRentMyParmalink('rentmy.page_url.reset_password'); ?>" class="float-right forgot-password">Forgot password?</a>
                                                    <?php } ?>
                                                    <button type="submit" id="rm_customer_login" class="login float-left">login <span></span></button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="nav-profile" role="tabpanel" aria-labelledby="nav-profile-tab">
                                <div class="card-body pb-0 pt-2">
                                    <div class="userlogin-box usersignup-box">
                                        <div class="userlogin-body">
                                            <form class="loginform" id="rm_customer_register_form">
                                                <span class="rm-customer-lregister-error text-danger"></span>
                                                <span class="rm-customer-lregister-success text-success"></span>
                                                <div class="row">
                                                    <div class="col-md-6 form-group mt-0 mb-4">
                                                        <input type="text" name="first_name" class="input-field" placeholder="First name" formControlName="first_name">
                                                    </div>
                                                    <div class="col-md-6 form-group mt-0 mb-4">
                                                        <input type="text" name="last_name" class="input-field" placeholder="Last name" formControlName="last_name">
                                                    </div>
                                                    <div class="col-md-6 form-group mt-0 mb-4">
                                                        <input type="text" name="email" class="input-field" placeholder="Email Address" formControlName="email">
                                                    </div>
                                                    <div class="col-md-6 form-group mt-0 mb-4">
                                                        <input type="text" name="username" class="input-field" placeholder="Username" formControlName="username">
                                                    </div>
                                                    <div class="col-md-6 form-group mt-0 mb-4">
                                                        <input type="text" name="company_name" class="input-field" placeholder="Company name" formControlName="company_name">
                                                    </div>
                                                    <div class="col-md-6 form-group mt-0 mb-4">
                                                        <input type="text" name="contact" class="input-field" placeholder="Phone number" formControlName="contact" autocomplete="on">
                                                    </div>
                                                    <div class="col-md-6 form-group mt-0 mb-4">
                                                        <input type="password" name="confirm_password" class="input-field" placeholder="Password" formControlName="password">
                                                    </div>
                                                    <div class="col-md-6 form-group mt-0 mb-4">
                                                        <input type="password" name="password" class="input-field" placeholder="Confirm-Password" formControlName="email" formControlName="confirm_password">
                                                    </div>
                                                    <div class="col-md-6 form-group mt-0 mb-4">
                                                        <input type="text" name="address_line1" class="input-field" placeholder="Address Line 1" formControlName="address_line1">
                                                    </div>
                                                    <div class="col-md-6 form-group mt-0 mb-4">
                                                        <input type="text" name="address_line2" class="input-field" placeholder="Address Line 2" formControlName="address_line2">
                                                    </div>

                                                    <div class="col-md-6 form-group mt-0 mb-4">
                                                        <select class="form-control m-input dropdown-cls" name="country">

                                                            <?php if(!empty($GLOBALS['rm_countries'])){?>
                                                            <?php  foreach ($GLOBALS['rm_countries'] as $country) {
                                                            ?>
                                                            <option value="<?php echo $country['code'];
                                                                            ?>" <?php echo $country['code']==$store_country?'selected':''; ?>>
                                                                <?php echo $country['name']; ?>
                                                            </option>
                                                            <?php } }  ?>
                                                        </select>
                                                    </div>

                                                    <div class="col-md-6 form-group mt-0 mb-4">
                                                        <input type="text" name="city" class="input-field" placeholder="City" formControlName="city">
                                                    </div>
                                                    <div class="col-md-6 form-group mt-0 mb-4">
                                                        <input type="text" name="state" id="state_online" class="input-field" placeholder="State" formControlName="state">
                                                    </div>
                                                    <div class="col-md-6 form-group mt-0 mb-4">
                                                        <input type="text" id="zipcode_online" name="zipcode" class="input-field" placeholder="Zip code" formControlName="zipcode">
                                                    </div>

                                                    <div class="col-md-12 form-group text-center mt-0 mb-0 fa-3x">
                                                        <button type="submit" class="login">Sign Up <span></span></button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php
}
?>
