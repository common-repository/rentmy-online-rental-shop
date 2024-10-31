<?php
//short code for product details of a product
function rentmy_customer_reset_password_shortcode()
{

    ob_start();
    $rentmy_config = new RentMy_Config();
    $store_content = $rentmy_config->store_contents();

    $GLOBALS['store_config'] = get_option('rentmy_config');
    $GLOBALS['store_text'] = [];
    if (!empty($store_content)) {
        $GLOBALS['store_text'] = $store_content[0]['contents'];
    }
    rentmy_customer_reset_password_template();
    return ob_get_clean();
}

add_shortcode('rentmy-customer-reset-password', 'rentmy_customer_reset_password_shortcode');

function rentmy_customer_reset_password_template()
{
?>

    <script>
        var source_url = "<?php echo home_url(''); ?>";
        var activation_key = "<?php echo isset($_GET['activation_key']) ? $_GET['activation_key'] : ''; ?>";
    </script>
    <div class="card mx-auto border-0 login-register-content rentmy-customerreset-change-content" id="rentmy-customer-password-reset">

        <div class="card-body pb-4" v-if="activation_key==''">

            <div class="userlogin-box">
                <div class="login-title">
                    <img src="<?php echo plugin_dir_url(__DIR__); ?>assets/forgot.png" alt="forgot password" class="img-fluid">
                    <h1><?php echo !empty($GLOBALS['store_text']['customer_portal']['title_reset_password'])?$GLOBALS['store_text']['customer_portal']['title_reset_password']:'Reset Password'; ?></h1>
                    <p><?php echo !empty($GLOBALS['store_text']['customer_portal']['subtitle_reset_password'])?$GLOBALS['store_text']['customer_portal']['subtitle_reset_password']:'Enter your email to reset your password'; ?></p>
                </div>
                <div class="userlogin-body">

                    <form class="loginform ng-untouched ng-pristine ng-valid">
                        <div class="col-md-12">
                            <span class="text-danger" v-if="error!=''">{{ error }}</span>
                            <span class="text-success" v-if="success!=''">{{ success }}</span>
                        </div>
                        <div class="form-group col-md-12">
                            <!-- <label>Enter your email to reset your password</label> -->
                            <input type="email" name="email" placeholder="<?php echo !empty($GLOBALS['store_text']['customer_portal']['lbl_email'])?$GLOBALS['store_text']['customer_portal']['lbl_email']:'Email*'; ?>" class="input-field form-control" v-model="email" required>
                        </div>
                        <div class="form-group col-md-12 text-center mb-0">
                            <button type="button" class="login button lbtn-50 theme-btn lbtn-xs" v-on:click="forgotPassword">
                                <?php echo !empty($GLOBALS['store_text']['customer_portal']['btn_submit'])?$GLOBALS['store_text']['customer_portal']['btn_submit']:'Submit'; ?>
                            </button>
                            <button type="button" class="login button lbtn-50 theme-btn lbtn-xs bg-danger" v-on:click="redirectToTarget">
                                <?php echo !empty($GLOBALS['store_text']['customer_portal']['btn_return'])?$GLOBALS['store_text']['customer_portal']['btn_return']:'Return'; ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>



        <div class="card-body pb-4" v-if="activation_key!=''">
            <div class="onlinestore-custom-alert custom-alert mt-3 mb-3">

            </div>
            <div class="userlogin-box">
                <div class="login-title">
                    <img src="<?php echo plugin_dir_url(__DIR__); ?>assets/forgot.png" alt="forgot password" class="img-fluid">
                    <h1><?php echo !empty($GLOBALS['store_text']['customer_portal']['title_change_password'])?$GLOBALS['store_text']['customer_portal']['title_change_password']:'Reset Password'; ?></h1>
                    <p><?php echo !empty($GLOBALS['store_text']['customer_portal']['subtitle_change_password'])?$GLOBALS['store_text']['customer_portal']['subtitle_change_password']:'Enter your New Password'; ?></p>
                </div>

                <div class="userlogin-body">
                    <div class="col-md-12">
                        <span class="text-danger" v-if="reset_msg.error!=''">{{ reset_msg.error }}</span>
                        <span class="text-success" v-if="reset_msg.success!=''">{{ reset_msg.success }}</span>
                    </div>
                    <form novalidate="" class="loginform ng-untouched ng-pristine ng-invalid">
                        <div class="form-group col-md-12">
                            <!-- <label>Enter your New Password</label> -->
                            <input type="password" placeholder="<?php echo !empty($GLOBALS['store_text']['customer_portal']['lbl_password'])?$GLOBALS['store_text']['customer_portal']['lbl_password']:'Password'?>" formcontrolname="password" class="input-field form-control" v-model="reset_data.new_password">
                        </div>
                        <div class="form-group col-md-12">
                            <!-- <label>Confirm Password</label> -->
                            <input type="password" placeholder="<?php echo !empty($GLOBALS['store_text']['customer_portal']['lbl_confirm_password'])?$GLOBALS['store_text']['customer_portal']['lbl_confirm_password']:'Confirm-Password'?>" formcontrolname="confirm_password" class="input-field form-control" v-model="reset_data.confirm_password">
                        </div>
                        <div class="form-group col-md-12 text-center">
                            <button type="button" class="login button lbtn-50 theme-btn lbtn-xs" v-on:click="resetPassword">
                                <?php echo !empty($GLOBALS['store_text']['customer_portal']['btn_submit'])?$GLOBALS['store_text']['customer_portal']['btn_submit']:'Submit'; ?>
                            </button>
                            <button type="button" class="login button lbtn-50 theme-btn lbtn-xs bg-danger" v-on:click="redirectToTarget">
                                <?php echo !empty($GLOBALS['store_text']['customer_portal']['btn_return'])?$GLOBALS['store_text']['customer_portal']['btn_return']:'Return'; ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>

    <!-- Reset password -->


    <script>
        var password_reset_link = "<?php echo getRentMyParmalink('rentmy.page_url.reset_password') ?>";
        var vapp = new Vue({
            el: '#rentmy-customer-password-reset',
            data: {
                email: '',
                error: '',
                success: '',
                activation_key: activation_key,
                reset_data: {
                    new_password: '',
                    confirm_password: ''
                },
                reset_msg: {
                    error: '',
                    success: ''
                }
            },
            methods: {
                forgotPassword: function() {
                    if (!this.email) return;
                    let vm = this;
                    vm.error = "";
                    vm.success = "";
                    let data = new FormData();
                    data.set('action', 'rentmy_options');
                    data.set('action_type', 'forgot_customer_password');
                    data.set("email", vm.email);
                    data.set("password_reset_link", password_reset_link);
                    data.set("source", source_url);
                    console.log(source_url);
                    axios.post(rentmy_ajax_object.ajaxurl, data).then(function(response) {
                        console.log(response.data);
                        if (response.data.status == 'NOK') {
                            vm.error = response.data.result.message;
                        } else {
                            vm.success = "An activation email sent successfully. Please check email.";
                            vm.email = "";
                        }
                    });
                },
                resetPassword: function() {
                    let vm = this;
                    let data = new FormData();
                    vm.reset_msg.error = '';
                    data.set('action', 'rentmy_options');
                    data.set('action_type', 'reset_customer_password');
                    data.set("password", vm.reset_data.new_password);
                    data.set("confirm_password", vm.reset_data.confirm_password);
                    data.set("activation_key", vm.activation_key);
                    if (vm.reset_data.new_password != vm.reset_data.confirm_password) {
                        vm.reset_msg.error = 'Password mismatch';
                        return;
                    } else if (vm.reset_data.new_password == '') {
                        vm.reset_msg.error = 'Invalid password';
                        return;
                    }

                    axios.post(rentmy_ajax_object.ajaxurl, data).then(function(response) {
                        console.log(response.data);
                        if (response.data.status == 'NOK') {
                            vm.reset_msg.error = response.data.result.message;
                        } else {
                            vm.reset_msg.success = response.data.result.message;
                            vm.reset_data.new_password = "";
                            vm.reset_data.confirm_password = "";
                            vm.redirectToTarget();

                        }
                    });
                },
                redirectToTarget: function() {
                    let url = "<?php echo getRentMyParmalink('rentmy.page_url.customer_login') ?>";
                    console.log(url);
                    window.location.href = url;
                }
            },
            created: function() {
                console.log(this.activation_key);
            }
        });
    </script>


<?php
}

?>