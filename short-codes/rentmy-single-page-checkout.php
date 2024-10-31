<?php
function rentmy_checkout_single_page_template()
{
?>

    <?php
    $customer_info = [];
    $billing_address = [];
    if (!empty($_SESSION['customer_info']) || !empty($_COOKIE['rentmy_customer_info'])) {
        $customerObj = new RentMy_Customer();
        $customer_info = $customerObj->getCustomer();
        $checkout_info = $customerObj->profile()['data'];
        $billing_address = $customerObj->address()['data'];
    }

    $store_config = [];
    if (!empty($_SESSION['rentmy_config'])) {
        $store_config = $_SESSION['rentmy_config'];
    }
    $storeName = get_option('rentmy_storeName');
    $store_country = get_option('rentmy_storeCountry');
    if (empty($store_country)) {
        $store_country = 'US';
    }
    $storeId = get_option('rentmy_storeId');

    $storeID = get_option('rentmy_storeId');
    $baseUrl = plugin_dir_url(__DIR__);


    $rentmy_cart = new RentMy_Cart();
    $isCartContainRecurring = false;
    if (!empty( $_SESSION['rentmy_cart_token'])){
        $response = $rentmy_cart->viewCart(['token' => $_SESSION['rentmy_cart_token']]);

        $isCartContainRecurring = (new RentMy())->isCartEnduring($response['data']);
    }

    ?>
    <script>
        var rm_countries = <?php echo json_encode($GLOBALS['rm_countries'], true); ?>;
        var rm_delivery_settings = <?php echo json_encode($GLOBALS['rm_delivery_settings'], true); ?>;
        var rm_locations = <?php echo json_encode($GLOBALS['rm_locations'], true); ?>;
        var rm_custom_fields = <?php echo json_encode($GLOBALS['rm_custom_fields'], true); ?>;
        var rm_payment_gateways = <?php echo json_encode($GLOBALS['rm_payment_gateways'], true); ?>;
        var rm_payment_labels = <?php echo json_encode($GLOBALS['payment_labels'], true); ?>;
        var config_labels = <?php echo json_encode($GLOBALS['labels'], true); ?>;
        var terms = <?php echo json_encode($GLOBALS['terms_condition'], true); ?>;
        var customer_info = <?php echo json_encode($customer_info, true); ?>;
        var store_config = <?php echo json_encode($store_config, true); ?>;
        var billing_address = <?php echo json_encode($billing_address, true); ?>;
        var rm_storeName = "<?php echo $storeName; ?>";
        var rm_storeId = "<?php echo $storeId; ?>";
        var rm_storeCountry = "<?php echo $store_country; ?>";
        var rm_baseUrl = "<?php echo $baseUrl; ?>";
        var separator = "<?= strpos( getRentMyParmalink('rentmy.page_url.product_details'), '?' ) !== false ? '&' : '?';  ?>"
        var rm_order_checkout_url = "<?php echo getRentMyParmalink('rentmy.page_url.checkout'); ?>";
        var rentmy_plugin_base_url = "<?php echo plugins_url('', RENTMY_PLUGIN_FILE) ?>";
        var isCartContainRecurring = <?php echo $isCartContainRecurring?1:0; ?>;
    </script>

    <section class="rentmy-checkout-content rentmy-plugin-manincontent" id="rentmy-single-page-checkout-wrapper">
        <div class="container-fluid">
            <div class="row ">
                <div class="col-xl-7 col-lg-7 col-md-12 col-sm-12 mb-5 checkout-leftside">
                    <!-- <div class="row">
                        <div class="col-xl-12">
                            <div class="returning-customer">
                                <h5>Returning Customer?<a href="javascript:void(0)">Click here to login</a></h5>
                            </div>
                        </div>
                    </div> -->
                    <!-- <div class="billing-details-leftside"> -->
                    <billing-address ref="billing" @billing-validation="billingIsValid"></billing-address>
                    <fullfillment-shipping ref="fullfillment" @fullfillment-validation="fullfillmentIsValid" v-bind:billing_info="billing_info" @update-cart="updateCart" @update-cart-forced="updateCartForced"></fullfillment-shipping>
                    <!-- </div> -->
                </div>
                <div class="col-xl-5 col-lg-5 col-md-12 col-sm-12 mt-0 checkout-rightside">
                    <div class="checkout_right_wrapper custom_border">
                        <div class="order_review_wrapper">
                            <h2 id="order_review_heading">
                                <span v-html="config_labels?.checkout_info?.title_order_summary?? 'Your order'"></span>
                            </h2>
                            <order-list ref="orderlist"></order-list>
                            <additional-charge ref="additionalcharge"></additional-charge>
                                <!-- transafe and squere share same function. though the name is  squareTokenReceived-->
                            <payment-method ref="payment" @get_token_transafe="squareTokenReceived" @get_token_sq="squareTokenReceived" @payment-validation="paymentIsValid"></payment-method>

                            <div class="form-group" v-if="isCartContainRecurring">
                                <label for="isCustomerAccount" class="checkbox-container custom-control-label">
                                    <input class="custom-control-input customerAccount" id="isCustomerAccount" type="checkbox" v-model="is_customer_account" name="is_customer_account">
                                    <p v-html="config_labels?.checkout_payment?.lbl_create_account_on_order??'Create an account to make ordering faster in the future'">


                                    </p>
                                    <span class="checkmark"></span>
                                </label>

                                <label for="enduringAgreement" class="checkbox-container custom-control-label">
                                    <input required class="custom-control-input enduringAgreementProcess" id="enduringAgreement" type="checkbox" v-model="enduring_agreement" name="enduring_agreement" disabled>
                                    <p v-html="config_labels?.checkout_payment?.lbl_payment_agreement??'I agree to make recurring rental payments per the rental agreements'">

                                    </p>
                                    <span class="checkmark"></span>
                                </label>
                            </div>
                            <div class="form-group">
                                <label for="agreementCheckProceed" class="checkbox-container custom-control-label">
                                    <input required class="custom-control-input agreementCheckProceed" id="agreementCheckProceed" type="checkbox" v-model="tos" name="tos" value="agreed">
                                    <p>
                                        <span v-html="config_labels?.checkout_info?.terms_and_condition??'I have read and agree with the'"></span>

                                        <a class="rentmy-terms-and-condition" data-toggle="modal" data-target="#acceptTermsModal" href="javascript:void(0)">
                                            <span v-html="config_labels?.checkout_info?.terms_and_condition_link_label??'terms & conditions'"></span>
                                        </a>
                                    </p>
                                    <span class="checkmark"></span>
                                </label>
                            </div>
                            <div class="form-group" v-if="config.signature && config.signature.online">
                                <div class="rentmy-form-row signature-pad-wrapper" style="display: none">
                                    <canvas id="signature-pad" class="signature-pad" width="250" height="auto"></canvas>
                                    <p>
                                        <span class="clear-signature" v-on:click="clearSignature">Clear</span>
                                        <span class="">Signature</span>
                                    </p>
                                    <input type="hidden" readonly id="signature" v-model="signature" name="signature">
                                </div>
                            </div>

                            <br />
                            <ul class="rentmy-error-wrapper" v-if="errors.length">
                                <li v-for="(error,i) in errors" :key="i">
                                    <strong>{{ error }}</strong>
                                </li>
                            </ul>
                            <br />

                            <div class="form-group pt-3 mb-0">
                                <button type="button" class="btn btn-md backtocart-btn" v-on:click="backToCart">
                                    <span v-html="config_labels?.checkout_payment?.btn_back_to_cart??'Back to Cart'"></span>

                                </button>
                                <button type="button" class="btn btn-md placeorder-btn float-right" v-on:click="placeOrder" :disabled="loading">
                                    <i v-if="loading" class="fa fa-spinner fa-spin"></i>
                                    <span v-html="config_labels?.checkout_payment?.btn_place_order??'Place Order'"></span>

                                </button>
                            </div>

                        </div>
                        <!--.order_review_wrapper-->
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal -->
        <div class="modal fade" id="acceptTermsModal" tabindex="-1" role="dialog" aria-labelledby="acceptModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">
                        <span v-html="terms.contents.heading ? terms.contents.heading : terms.name"></span>
                        </h4>
                    </div>
                    <div class="modal-body">
                        <span v-html="terms.contents.content"></span>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        const serveBus = new Vue();

        var vapp = new Vue({
            el: '#rentmy-single-page-checkout-wrapper',
            data: {
                rentmy_cart_url: rentmy_cart_url,
                rentmy_home_url: rentmy_home_url,
                billing_info: {},
                fullfillment_info: {},
                payment_info: {},
                signature: '',
                tos: '',
                loading: false,
                errors: [],
                canvasSignature: null,
                signaturePad: null,
                config: rentmy_config_data_preloaded,
                delivery_data: {},
                config_labels: config_labels,
                terms: terms,
                customer_info: customer_info,
                orderResponse: {},
                store_id: rm_storeId,
                cart_token: '',
                payloadData: {},
                is_customer_account: false,
                enduring_agreement: true,
                isCartContainRecurring: isCartContainRecurring,

            },
            methods: {
                backToCart: function(event) {
                    this.cart_token = this.$refs.orderlist.cart.token;
                    window.location.replace(this.rentmy_cart_url + '?token=' + this.cart_token + '&add-to-cart=true');
                },
                updateCart: function(cart) {
                    this.$refs.orderlist.setCart(cart);
                    this.$refs.payment.setCart(cart);
                },


                updateCartForced: async function() {
                    let data = new FormData();
                    data.set('action', 'rentmy_cart_topbar');
                    let cartResponse = await axios({
                        method: 'post',
                        url: rentmy_ajax_object.ajaxurl,
                        data: data
                    });
                    this.$refs.orderlist.setCart(cartResponse.data);
                    this.$refs.payment.setCart(cartResponse.data);
                },
                validate: function() {
                    this.errors = [];

                    if (!this.$refs.billing.validate()) {
                        this.errors.push('Please fill billing details properly.');
                    }
                    if (!this.$refs.fullfillment.validate()) {
                        this.errors.push('Please fill fullfillment details properly.');
                    }
                    if (!this.$refs.payment.validate()) {
                        this.errors.push('Please fill payment details properly.');
                    }
                    if (!this.tos) {
                        this.errors.push('You must accept terms & conditions.');
                    }
                    if (!this.signature) {
                        //this.errors.push('You must enter your signature.');
                    }
                    if (this.isCartContainRecurring){
                        if (!this.enduring_agreement){
                            this.errors.push('You must accept recurring rental agreement.');
                        }

                    }

                    return !this.errors.length;
                },
                orderComplete: function(data) {
                    this.loading = false;
                    if (data.message != '') {
                        toastr.error("Order can't be created. Please try again!");
                    } else if (data.status != "OK") {
                        toastr.success(data.message);
                    } else {
                        toastr.success(data.message);
                    }


                    if (data.uid) {
                        // show other pdf and stuff
                        setTimeout(() => {
                            window.location.replace(rm_order_checkout_url + separator + 'step=complete-order&order_id=' + data.uid + '&add-to-cart=true');
                        }, 500);
                    }
                },
                squareTokenReceived: function (token){
                    this.loading = true;
                    if (token){
                        vpapp = this;
                        let payload = this.payloadData;
                        payload.data.card_no = token;
                        let data = new FormData();

                        data.set('action', 'rentmy_options');
                        data.set('action_type', 'submit_single_checkout');
                        data.set('data', JSON.stringify(payload.data, null, '\t'));
                        let amount = 0; //this.$refs.orderlist.cart.total;
                        axios.post(rentmy_ajax_object.ajaxurl, data)
                            .then(function(response) {
                                let orderResponse = response.data;

                                vpapp.loading = false;
                                if (orderResponse.message == '') {
                                    toastr.error("Order can't be created. Please try again!");
                                } else if (orderResponse.status != "OK") {
                                    toastr.error(orderResponse.message);
                                } else {
                                    toastr.success(orderResponse.message);
                                }


                                if (orderResponse.uid) {
                                    // show other pdf and stuff
                                    let affiliateParams = '';
                                    if (rm_storeId == '534') {
                                        affiliateParams = '&reference=' + orderResponse.id + '&amount=' + amount;
                                    }
                                    setTimeout(() => {
                                        window.location.replace(rm_order_checkout_url + separator +'step=complete-order&order_id=' + orderResponse.uid + '&add-to-cart=true' + affiliateParams);
                                    }, 500);
                                }

                                //this.orderComplete(orderResponse);
                            })
                            .catch(function(error) {
                                //   currentObj.output = error;
                            });
                    }
                },
                placeOrder: async function(event) {

                    // final validation check goes here
                    if (!this.validate()) {
                        return;
                    }


                    this.$emit('checkValidation', true);

                    if (this.signaturePad) {
                        if (this.signaturePad.isEmpty()){
                            this.errors.push('Signature is required.');
                            return;
                        }else{
                            let signatureToData = this.signaturePad.toDataURL('image/jpeg', 0.5);
                            this.signature = signatureToData;
                        }
                    }

                    this.loading = true;
                    var data = new FormData();

                    this.delivery_data = this.$refs.fullfillment.getOrderDeliveryData();

                    shipping_first_name = '';
                    shipping_last_name = '';
                    shipping_mobile = '';
                    shipping_email = '';
                    shipping_country = '';
                    shipping_address1 = '';
                    shipping_address2 = '';
                    shipping_city = '';
                    shipping_zipcode = '';
                    shipping_state = '';
                    pickup = '';
                    if (this.$refs.fullfillment.fftype == 'shipping') {
                        let sp_first_name = this.$refs.fullfillment.ship_to_name.split(' ')[0];
                        let sp_last_name = this.$refs.fullfillment.ship_to_name.split(' ').filter(function(item) {
                            return item != sp_first_name
                        });
                        shipping_first_name = sp_first_name;
                        shipping_last_name = sp_last_name.join(" ");
                        shipping_mobile = this.$refs.fullfillment.ship_to_phone;
                        shipping_email = this.$refs.fullfillment.ship_to_email;
                        shipping_country = this.fullfillment_info.shipping_country;
                        shipping_address1 = this.fullfillment_info.shipping_address1;
                        shipping_address2 = this.fullfillment_info.shipping_address2;
                        shipping_city = this.fullfillment_info.shipping_city;
                        shipping_zipcode = this.fullfillment_info.shipping_zipcode;
                        shipping_state = this.fullfillment_info.shipping_state;
                    } else if (this.$refs.fullfillment.fftype == 'delivery') {
                        let sp_first_name = this.$refs.fullfillment.delivery_to_name.split(' ')[0];
                        let sp_last_name = this.$refs.fullfillment.delivery_to_name.split(' ').filter(function(item) {
                            return item != sp_first_name
                        });
                        shipping_first_name = sp_first_name;
                        shipping_last_name = sp_last_name.join(" ");
                        shipping_mobile = this.$refs.fullfillment.delivery_to_phone;
                        shipping_email = this.$refs.fullfillment.delivery_to_email;
                        shipping_country = this.fullfillment_info.delivery_country;
                        shipping_address1 = this.fullfillment_info.delivery_address1;
                        shipping_address2 = this.fullfillment_info.delivery_address2;
                        shipping_city = this.fullfillment_info.delivery_city;
                        shipping_zipcode = this.fullfillment_info.delivery_zipcode;
                        shipping_state = this.fullfillment_info.delivery_state;
                    } else {
                        pickup = this.delivery_data.id ?? ''
                    }
                    delivery_multi_store = this.$refs.fullfillment.delivery_multi_store??''

                    var payload = {
                        action: 'rentmy_options',
                        action_type: 'submit_single_checkout',
                        data: {
                            first_name: this.billing_info.first_name,
                            last_name: this.billing_info.last_name,
                            mobile: this.billing_info.mobile,
                            email: this.billing_info.email,
                            address_line1: this.billing_info.address_line1,
                            address_line2: this.billing_info.address_line2,
                            city: this.billing_info.city,
                            state: this.billing_info.state,
                            country: this.billing_info.country,
                            zipcode: this.billing_info.zipcode,

                            pickup: pickup,
                            special_instructions: this.$refs.billing.special_instructions,
                            special_requests: this.$refs.billing.special_requests,
                            driving_license: this.$refs.billing.driving_license,

                            // custom_values: this.$refs.billing.custom_fields,
                            custom_values: this.$refs.billing.getCustomFields(),

                            rm_instore_loc: this.fullfillment_info.rm_instore_loc,

                            shipping_first_name: shipping_first_name,
                            shipping_last_name: shipping_last_name,
                            shipping_mobile: shipping_mobile,
                            shipping_email: shipping_email,

                            shipping_country: this.fullfillment_info.shipping_country,
                            shipping_address1: shipping_address1,
                            shipping_address2: shipping_address2,
                            shipping_city: shipping_city,
                            shipping_zipcode: shipping_zipcode,
                            shipping_state: shipping_state,


                            delivery_country: this.fullfillment_info.delivery_country,
                            delivery_address1: this.fullfillment_info.delivery_address1,
                            delivery_address2: this.fullfillment_info.delivery_address2,
                            delivery_city: this.fullfillment_info.delivery_city,
                            delivery_zipcode: this.fullfillment_info.delivery_zipcode,
                            delivery_state: this.fullfillment_info.delivery_state,

                            delivery: this.delivery_data,
                            shipping_method: this.delivery_data.method,

                            card_name: this.payment_info.card_name,
                            card_no: this.payment_info.card_no,
                            exp_month: this.payment_info.exp_month,
                            exp_year: this.payment_info.exp_year,
                            cvv: this.payment_info.cvv,

                            amount: this.$refs.payment.payment_amount,
                            payment_amount: this.$refs.payment.payment_amount,
                            currency: this.$refs.payment.currency.code,
                            note: this.payment_info.note,

                            gateway_id: this.payment_info.payment_gateway_id,
                            payment_gateway_id: this.payment_info.payment_gateway_id,
                            payment_gateway_type: this.payment_info.payment_gateway_type,
                            payment_gateway_name: this.payment_info.payment_gateway_name,

                            signature: this.signature,
                            delivery_multi_store: delivery_multi_store,
                            is_customer_account: this.is_customer_account
                        }
                    };

                    let vpapp = this

                    if ((this.payment_info.payment_gateway_name == 'Stripe') || (this.payment_info.payment_gateway_name == 'Square') || (this.payment_info.payment_gateway_name == 'Transafe')) {

                        if (this.payment_info.payment_gateway_name == 'Square'){
                            vpapp.payloadData = payload;
                           await serveBus.$emit('accessSquareToken', true);
                            return;

                        }
                        if (this.payment_info.payment_gateway_name == 'Transafe'){
                            vpapp.payloadData = payload;
                            await serveBus.$emit('accessTransafeToken', true);
                            return;
                        }

                        var payment_token = vpapp.$refs.payment.payment_token;

                        if (payment_token == '' && (this.payment_info.payment_gateway_name == 'Stripe')) {
                            payload.data.requires_action = false;
                            await this.payment_info.stripe.createPaymentMethod({
                                type : 'card',
                                card : this.payment_info.scard
                            }).then(function(res) {
                                if (res?.error){
                                    serveBus.$emit('stripePaymentError', res.error.message);
                                    vpapp.loading = false;
                                    return;
                                }
                                let PaymentIntentData = new FormData();

                                let recurringAmount = vpapp.$refs.orderlist?.cart?.enduring_amount??0;
                                let paymentAmount = payload.data.payment_amount - recurringAmount;
                                payload.data.amount = payload.data.payment_amount = paymentAmount;
                                PaymentIntentData.set('action', 'rentmy_options');
                                PaymentIntentData.set('action_type', 'get_payment_intent');
                                PaymentIntentData.set('payment_method_id', res.paymentMethod.id);
                                PaymentIntentData.set('customer', res.paymentMethod.customer);
                                PaymentIntentData.set('amount', payload.data.payment_amount);
                                if (paymentAmount <= 0){
                                    PaymentIntentData.set('for_enduring', true);
                                }
                                axios.post(rentmy_ajax_object.ajaxurl, PaymentIntentData).then(async function (resp) {
                                    let payment3dsPass = true;
                                    let result = resp.data.result;
                                    if (result?.data?.requires_action) {
                                        payload.data.requires_action = result?.data?.requires_action;
                                        await vpapp.payment_info.stripe.handleCardAction(
                                            result.data.payment_intent_client_secret
                                        ).then(function (result) {
                                            console.log(result)
                                            // Handle result.error or result.paymentIntent
                                            if (result.error) {
                                                vpapp.errors.push(result?.error?.message);
                                                payment3dsPass = false
                                            } else {
                                                payment3dsPass = true
                                            }
                                        });
                                    }
                                    if (!payment3dsPass) {
                                        vpapp.loading = false;
                                        return;
                                    }
                                    if (result.status == 'OK') {
                                        vpapp.loading = false;
                                        vpapp.errors.push('Unable to authorize your card.');
                                        return;
                                    } else {
                                        vpapp.card_no = result.data.id;
                                        payload.data.card_no = result.data.id;
                                        payload.data.customer = result.data.customer ?? '';
                                        if (result?.data?.for_enduring){
                                            payload.data.for_enduring = true;
                                            payload.data.payment_method_id = result.data.payment_method_id;
                                        }
                                        payload.data.payment_method = result.data.customer ?? '';
                                        data.set('action', 'rentmy_options');
                                        data.set('action_type', 'submit_single_checkout');
                                        data.set('data', JSON.stringify(payload.data, null, '\t'));
                                        let amount = 0; //this.$refs.orderlist.cart.total;
                                        axios.post(rentmy_ajax_object.ajaxurl, data)
                                            .then(function (response) {
                                                let orderResponse = response.data;

                                                vpapp.loading = false;
                                                if (orderResponse.message == '') {
                                                    toastr.error("Order can't be created. Please try again!");
                                                } else if (orderResponse.status != "OK") {
                                                    toastr.error(orderResponse.message);
                                                } else {
                                                    toastr.success(orderResponse.message);
                                                }
                                                if (orderResponse.uid) {
                                                    // show other pdf and stuff
                                                    let affiliateParams = '';
                                                    if (rm_storeId == '534') {
                                                        affiliateParams = '&reference=' + orderResponse.id + '&amount=' + amount;
                                                    }

                                                    setTimeout(() => {
                                                        window.location.replace(rm_order_checkout_url + separator + 'step=complete-order&order_id=' + orderResponse.uid + '&add-to-cart=true' + affiliateParams);
                                                    }, 500);
                                                }
                                                localStorage.removeItem('deliveryFlow');
                                                //this.orderComplete(orderResponse);
                                            })
                                            .catch(function (error) {
                                                //   currentObj.output = error;
                                            });

                                    }
                                });


                            });
                        } else {
                            vpapp.card_no = payment_token;
                            payload.data.card_no = payment_token;
                            data.set('action', 'rentmy_options');
                            data.set('action_type', 'submit_single_checkout');
                            data.set('data', JSON.stringify(payload.data, null, '\t'));
                            let amount = 0; //this.$refs.orderlist.cart.total;
                            axios.post(rentmy_ajax_object.ajaxurl, data)
                                .then(function(response) {
                                    let orderResponse = response.data;

                                    vpapp.loading = false;
                                    if (orderResponse.message == '') {
                                        toastr.error("Order can't be created. Please try again!");
                                    } else if (orderResponse.status != "OK") {
                                        toastr.error(orderResponse.message);
                                    } else {
                                        toastr.success(orderResponse.message);
                                    }


                                    if (orderResponse.uid) {
                                        // show other pdf and stuff
                                        let affiliateParams = '';
                                        if (rm_storeId == '534') {
                                            affiliateParams = '&reference=' + orderResponse.id + '&amount=' + amount;
                                        }
                                        setTimeout(() => {
                                            window.location.replace(rm_order_checkout_url + separator + 'step=complete-order&order_id=' + orderResponse.uid + '&add-to-cart=true' + affiliateParams);
                                        }, 500);
                                    }
                                    localStorage.removeItem('deliveryFlow');
                                    //this.orderComplete(orderResponse);
                                })
                                .catch(function(error) {
                                    //   currentObj.output = error;
                                });
                        }


                    } else {
                        data.set('action', 'rentmy_options');
                        data.set('action_type', 'submit_single_checkout');
                        data.set('data', JSON.stringify(payload.data, null, '\t'));

                        let amount = vpapp.$refs.orderlist.cart.total;

                        axios.post(rentmy_ajax_object.ajaxurl, data)
                            .then(function(response) {

                                let orderResponse = response.data;
                                vpapp.loading = false;
                                if (orderResponse.message == '') {
                                    toastr.error("Order can't be created. Please try again!");
                                } else if (orderResponse.status != "OK") {
                                    toastr.error(orderResponse.message);
                                } else {
                                    toastr.success(orderResponse.message);
                                }

                                if (orderResponse.uid) {
                                    setTimeout(() => {
                                        let affiliateParams = '';
                                        if (rm_storeId == '534') {
                                            affiliateParams = '&reference=' + orderResponse.id + '&amount=' + amount;
                                        }
                                        window.location.replace(rm_order_checkout_url + separator + 'step=complete-order&order_id=' + orderResponse.uid + '&add-to-cart=true' + affiliateParams);
                                    }, 500);
                                }

                                localStorage.removeItem('deliveryFlow');
                            })
                            .catch(function(error) {
                                //   currentObj.output = error;
                            });

                    }



                },
                billingIsValid: function(billingChecked) {
                    this.billing_info = billingChecked;
                },
                fullfillmentIsValid: function(fullfillmentChecked) {
                    this.fullfillment_info = fullfillmentChecked;
                },
                paymentIsValid: function(paymentChecked) {
                    this.payment_info = paymentChecked;
                },

                clearSignature: function() {
                    this.signaturePad.clear();
                    this.signature = '';
                }
            },
            components: {
                'billing-address': window.httpVueLoader('<?php echo plugins_url('assets/js/components/BillingAddress.vue', RENTMY_PLUGIN_FILE); ?>'),
                'order-list': window.httpVueLoader('<?php echo plugins_url('assets/js/components/OrderList.vue', RENTMY_PLUGIN_FILE); ?>'),
                'payment-method': window.httpVueLoader('<?php echo plugins_url('assets/js/components/PaymentMethod.vue', RENTMY_PLUGIN_FILE); ?>'),
                'fullfillment-shipping': window.httpVueLoader('<?php echo plugins_url('assets/js/components/FullfillmentShipping.vue', RENTMY_PLUGIN_FILE); ?>'),
                'additional-charge': window.httpVueLoader('<?php echo plugins_url('assets/js/components/AdditionalCharge.vue', RENTMY_PLUGIN_FILE); ?>')
            },

            mounted: function() {
                console.log("v-3.9.10");
                let ref = this;
                this.canvasSignature = document.querySelector('.signature-pad');
                if (this.canvasSignature) {
                    this.signaturePad = new SignaturePad(this.canvasSignature, {
                        // 'canvasWidth': 500,
                        // 'canvasHeight': 200,
                        'penColor': 'black',
                        'backgroundColor': 'white'
                        //backgroundColor: 'rgb(255, 255, 255)' // necessary for saving image as JPEG; can be removed is only saving as PNG or SVG
                    });
                }
                /* let ajaxdata = {
                    action: 'rentmy_options',
                    action_type: 'get_store_contents',
                };

                jQuery.post(rentmy_ajax_object.ajaxurl, ajaxdata, function (response) {
                    //console.log(response);
                }); */
                serveBus.$on('transafeError',function(loading){
                    ref.loading = !loading;
                });
            }
        })
    </script>

<?php
}
