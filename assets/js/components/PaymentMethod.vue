<template>
    <div class="accordion pt-3" id="accordionExamplePayment">

        <ul class="rentmy-error-wrapper" v-if="errors.length">
            <li v-for="(error,i) in errors" :key="i">
                <strong>{{ error }}</strong>
            </li>
        </ul>
        <br>

        <div class="card" v-for="gateway in rentmy_payment_gateways" :key="gateway.id">
            <div class="card-header" id="headingOne0" v-if="gateway.type == 'online' && gateway.name == 'Stripe'">
                <h2 class="mb-0">
                    <div class="custom-control custom-radio" data-toggle="collapse" :data-target="'#collapseTwo'+gateway.id" aria-expanded="true" aria-controls="collapseTwo">
                        <input type="radio" :id="'customRadio'+gateway.id" name="payment_gateway" v-model="payment_gateway_id" :value="gateway.id" class="custom-control-input"/>
                        <label class="custom-control-label mb-0" :for="'customRadio'+gateway.id">{{rentmy_payment_labels.title_credit_card}}</label>
                    </div>
                </h2>
            </div>

            <div :id="'collapseTwo'+gateway.id" :class="'collapse '+ ((rentmy_payment_gateways.length==1)?'show':'')" aria-labelledby="headingTwo" data-parent="#accordionExamplePayment" v-if="gateway.type == 'online' && gateway.name == 'Stripe'">
                <div class="card-body">
                  <div v-if="gateway.config && gateway.config.instructions">
                    <label v-html="gateway.config.instructions"></label>
                  </div>
                  <partial-payment v-if="isPartialRequired(gateway)" @get_payment_amount="setPaymentAmount"></partial-payment>
                  <!--                  Apple pay-->

                    <div class="row">
                      <div class="col-md-12">
                        <div id="payment-request-button">

                        </div>
                        <h4 id="or-text">OR</h4>
                        <span id="success-text"></span>
                      </div>
                    </div>
<!--                  extra-->
                    <div class="row" id="payment-request-token" style="display: none;">
                      <div class="col-md-12">
                        <div style="font-size: 12px; padding: 38px 0px;">
                          <h5>Payment Token </h5>
                          <div id="payment-request-token-json"></div>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-6">
                        <div id="amount_to_pay" style="display: none;">
                          <div class="form-group">
                            <label>Amount to pay:</label>
                            <input type="text" value="0.00" name="amount" class="form-control" />
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-6">
                        <button class="btn btn-primary" id="proceed_to_pay" style="display: none"
                                onclick="if (!window.__cfRLUnblockHandlers) return false; alert('Payment process called with token');"
                                data-cf-modified-00d12b43096ce92307477686-="">Proceed to pay
                        </button>
                      </div>
                    </div>

                  <!--                  Apple pay end-->


                  <div class="form-group mb-3" id="stripe_element_name">
                    <input autocomplete="cc-name" class="form-control ng-untouched ng-pristine ng-invalid" formcontrolname="name_on_card" :placeholder="config_labels.checkout_payment.lbl_name??'Name on card'" type="text" name="card_name" id="card_name" v-model="card_name"/>
                  </div>
                    <div class="stripe_element" id="card-element" :data-stripe-key="gateway.config.publishable_key">
                    </div>

                </div>
              <div v-if="stripeError.length > 0" class="rentmy-error-wrapper">{{ stripeError }}</div>
            </div>

<!--Square payments-->

          <div class="card-header" id="headingOne2" v-if="gateway.type == 'online' && gateway.name == 'Square'">
            <h2 class="mb-0">
              <div class="custom-control custom-radio" data-toggle="collapse" :data-target="'#collapseTwo'+gateway.id" aria-expanded="true" aria-controls="collapseTwo">
                <input type="radio" :id="'customRadio'+gateway.id" name="payment_gateway" v-model="payment_gateway_id" :value="gateway.id" class="custom-control-input"/>
                <label class="custom-control-label mb-0" :for="'customRadio'+gateway.id">{{rentmy_payment_labels.title_credit_card}}</label>
              </div>
            </h2>
          </div>

          <div :id="'collapseTwo'+gateway.id" :class="'collapse '+ ((rentmy_payment_gateways.length==1)?'show':'')" aria-labelledby="headingTwo" data-parent="#accordionExamplePayment" v-if="gateway.type == 'online' && gateway.name == 'Square'">
            <div class="card-body">
              <div v-if="gateway.config && gateway.config.instructions">
                <label v-html="gateway.config.instructions"></label>
              </div>
              <partial-payment v-if="isPartialRequired(gateway)" @get_payment_amount="setPaymentAmount"></partial-payment>
              <square-payment :showPaymentForm="true" :id="gateway.id" :gateway="gateway" @get_token="getSquareToken" ref="sqPayment"></square-payment>
            </div>
          </div>




          <!--Transafe payments-->

          <div class="card-header" id="headingOne3" v-if="gateway.type == 'online' && gateway.name == 'Transafe'">
            <h2 class="mb-0">
              <div class="custom-control custom-radio" data-toggle="collapse" :data-target="'#collapseTwo'+gateway.id" aria-expanded="true" aria-controls="collapseTwo">
                <input type="radio" :id="'customRadio'+gateway.id" name="payment_gateway" v-model="payment_gateway_id" :value="gateway.id" class="custom-control-input"/>
                <label class="custom-control-label mb-0" :for="'customRadio'+gateway.id">{{rentmy_payment_labels.title_credit_card}}</label>
              </div>
            </h2>
          </div>

          <div :id="'collapseTwo'+gateway.id" :class="'collapse '+ ((rentmy_payment_gateways.length==1)?'show':'')" aria-labelledby="headingTwo" data-parent="#accordionExamplePayment" v-if="gateway.type == 'online' && gateway.name == 'Transafe'">
            <div class="card-body">
              <div v-if="gateway.config && gateway.config.instructions">
                <label v-html="gateway.config.instructions"></label>
              </div>
              <partial-payment v-if="isPartialRequired(gateway)" @get_payment_amount="setPaymentAmount"></partial-payment>
              <transafe-payment :showPaymentForm="true" :id="gateway.id" :gateway="gateway" @get_token="getTransafeToken"></transafe-payment>
            </div>
          </div>


<!--          Other payments-->
            <div class="card-header" id="headingOne3" v-if="(gateway.type == 'online') && ((gateway.name.toLowerCase() != 'stripe') && (gateway.name.toLowerCase() != 'square') && (gateway.name.toLowerCase() != 'transafe'))">
                <h2 class="mb-0">
                    <div class="custom-control custom-radio" data-toggle="collapse" :data-target="'#collapseTwo'+gateway.id" aria-expanded="false" aria-controls="collapseTwo">
                        <input type="radio" :id="'customRadio'+gateway.id" name="payment_gateway" v-model="payment_gateway_id" :value="gateway.id" class="custom-control-input">
                        <label class="custom-control-label mb-0" :for="'customRadio'+gateway.id">{{rentmy_payment_labels.title_credit_card}}</label>
                    </div>
                </h2>
            </div>

            <div :id="'collapseTwo'+gateway.id" :class="'collapse '+ ((rentmy_payment_gateways.length==1)?'show':'')" aria-labelledby="headingTwo" data-parent="#accordionExamplePayment" v-if="(gateway.type == 'online') && ((gateway.name.toLowerCase() != 'stripe') && (gateway.name.toLowerCase() != 'square') && (gateway.name.toLowerCase() != 'transafe'))">
                <div class="card-body">
                    <div v-if="gateway.config && gateway.config.instructions">
                        <label v-html="gateway.config.instructions"></label>
                    </div>
                  <partial-payment v-if="isPartialRequired(gateway)" @get_payment_amount="setPaymentAmount"></partial-payment>
                    <div>
                        <div id="accordion">
                            <div class="card">
                                <div class="collapse show" id="collapseOne">
                                    <form novalidate="" class="ng-untouched ng-pristine ng-invalid">
                                        <div class="form-group mb-3">
                                            <label for="card_name"> {{config_labels?.checkout_payment?.lbl_name??'Name on Card'}} </label>
                                            <input autocomplete="cc-name" class="form-control ng-untouched ng-pristine ng-invalid" formcontrolname="name_on_card" :placeholder="config_labels?.checkout_payment?.lbl_name??'Name on Card'" type="text" name="card_name" id="card_name" v-model="card_name"/>
                                        </div>
                                        <div class="form-group">
                                            <label for="card_no">{{config_labels?.checkout_payment?.lbl_card_number??'Card Number'}}</label>
                                            <input autocomplete="cc-number" class="form-control cardnumber ng-untouched ng-pristine ng-invalid" formcontrolname="card_number" numberonly="" type="text" name="card_no" id="card_no" v-model="card_no" />
                                        </div>
                                        <div class="row">
                                            <label class="col-md-12" for="exp_month">{{config_labels?.checkout_payment?.lbl_expiration_data??'Expiration Month'}}</label>
                                            <div class="form-group col-md-6">
                                                <select class="form-control" name="exp_month" id="exp_month" v-model="exp_month">
                                                    <option value="">-Select Month-</option>
                                                    <option value="01">01 January</option>
                                                    <option value="02">02 February</option>
                                                    <option value="03">03 March</option>
                                                    <option value="04">04 April</option>
                                                    <option value="05">05 May</option>
                                                    <option value="06">06 June</option>
                                                    <option value="07">07 July</option>
                                                    <option value="08">08 August </option>
                                                    <option value="09">09 September </option>
                                                    <option value="10">10 October </option>
                                                    <option value="11">11 November</option>
                                                    <option value="12">12 December</option>
                                                </select>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <select class="form-control" name="exp_year" id="exp_year" v-model="exp_year">
                                                    <option value="">-Select Year-</option>
                                                    <option v-for="n in 15" :value="19+n" :key="2019+n">{{ 2019+n }}</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="frmCCCVC">{{config_labels?.checkout_payment?.lbl_cvv??'CVV Number'}}</label>
                                            <input autocomplete="cc-csc" class="form-control ng-untouched ng-pristine ng-invalid" formcontrolname="cvv" maxlength="4" numberonly="" placeholder="CVV Number " type="text" name="cvv" id="cvv" v-model="cvv"/>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>


            <div class="card-header" id="headingOne" v-if="gateway.type != 'online'">
                <h2 class="mb-0">
                    <div class="custom-control custom-radio" data-toggle="collapse" :data-target="'#collapseTwo'+gateway.id" aria-expanded="false" aria-controls="collapseTwo">
                        <input type="radio" :id="'customRadio'+gateway.id" name="payment_gateway" v-model="payment_gateway_id" :value="gateway.id" class="custom-control-input">
                        <label class="custom-control-label mb-0" :for="'customRadio'+gateway.id">{{gateway.name | uppercase}}</label>
                    </div>
                </h2>
            </div>

            <div :id="'collapseTwo'+gateway.id" :class="'collapse '+ ((rentmy_payment_gateways.length==1)?'show':'')" aria-labelledby="headingTwo" data-parent="#accordionExamplePayment" v-if="gateway.type != 'online'">
                <div class="card-body">
                        <div v-if="gateway.config && gateway.config.instructions">
                            <label v-html="gateway.config.instructions"></label>
                        </div>
                        <partial-payment v-if="isPartialRequired(gateway)" @get_payment_amount="setPaymentAmount"></partial-payment>
                        <div class="form-group" v-if="!isPartialRequired(gateway) && (gateway.config && gateway.config.is_paid)">
                            <label>Amount to Pay</label>
                            <input class="form-control" placeholder="Amount to Pay" type="text" v-model="payment_amount"/>
                        </div>
                        <div class="form-group" v-if="gateway.config && gateway.config.add_note">
                            <label>{{config_labels?.checkout_payment?.lbl_note??'Note'}}</label>
                            <input class="form-control" :placeholder="config_labels?.checkout_payment?.lbl_note??'Note'" type="text" name="note" :id="`note`+gateway.id" v-model="note"/>
                        </div>

                </div>
            </div>
        </div>
    </div>
</template>

<script>

module.exports = {
    components:{
      'square-payment': window.httpVueLoader(rentmy_plugin_base_url+'/assets/js/components/gateways/Squareup.vue'),
      'transafe-payment': window.httpVueLoader(rentmy_plugin_base_url+'/assets/js/components/gateways/Transafe.vue'),
      'partial-payment': window.httpVueLoader(rentmy_plugin_base_url+'/assets/js/components/PartialPayment.vue'),
    },
    data() {
        return{
            errors: [],
            rentmy_payment_gateways: rm_payment_gateways.data,
            rentmy_payment_labels: rm_payment_labels,
            config: rentmy_config_data_preloaded,
            currency_symbol: rentmy_config_data_preloaded.currency_format.symbol,
            currency: rentmy_config_data_preloaded.currency_format,
            cart: {},
            card_name: '',
            card_no: '',
            exp_month: '',
            exp_year: '',
            cvv: '',
            note: '',
            payment_gateway_id: '',
            payment_gateway_type: '',
            payment_gateway_name: '',
            stripe: null,
            selements: null,
            scard: null,
            real_payment: 'full',
          payment_token: '',
            payment_amount: '',
          config_labels: config_labels,
          is_apple_pay: true,
          store_config: store_config,
          store_id: rentmy_store_id,
          stripeError: ""

        }
    },
    methods: {

        applePay: function (){
          let ref = this;
          let amount =  parseInt((ref.payment_amount * 100)??0);
          let stripe_key = jQuery('#card-element').attr('data-stripe-key');
          let name = 'total';
          if (this.$parent.billing_info.first_name != ''){
             name = this.$parent.billing_info.first_name +' '+ this.$parent.billing_info.last_name;
          }


          var stripe = Stripe(stripe_key, {});
          var paymentRequest = stripe.paymentRequest({
            country: 'US',
            currency: ref.currency.code.toLowerCase(),
            total: {
              label: name,
              amount: amount
            },

            // requestPayerName: true,
            // requestPayerEmail: true,
          });
          var elements = stripe.elements();
          var prButton = elements.create('paymentRequestButton', {
            paymentRequest: paymentRequest,
          });
          // Check the availability of the Payment Request API first.
          paymentRequest.canMakePayment().then(function (result) {
            if (result) {
              prButton.mount('#payment-request-button');

            } else {
              document.getElementById('payment-request-button').style.display = 'none';
              document.getElementById('or-text').style.display = 'none';
            }
          });
          paymentRequest.on('token', function (event) {

            ref.payment_token = event.token.id;
            document.getElementById('payment-request-button').style.display = 'block';
            document.getElementById('card-element').style.display = 'none';
            document.getElementById('stripe_element_name').style.display = 'none';
            document.getElementById('or-text').style.display = 'none';
            document.getElementById('success-text').innerHTML = 'Please Click on the <b>Place Order</b> button to complete the order';
            document.getElementById('payment-request-token').style.display = 'none';
            document.getElementById('payment-request-token-json').innerHTML = JSON.stringify(event.token);
            document.getElementById('payment-request-token-json').style.display = 'none';
            document.getElementById('amount_to_pay').style.display = 'none';
            document.getElementById('proceed_to_pay').style.display = 'none';

            event.complete('success');
          });

          prButton.on('click', e=> {
            let name = '';
            if (this.$parent?.billing_info?.first_name!= undefined && this.$parent.billing_info.first_name != ''){
              name = this.$parent.billing_info.first_name +' '+ (this.$parent?.billing_info?.last_name || '');
            }
            let amount =  parseInt((ref.payment_amount * 100)??0);
            paymentRequest.update({
              total: {
                label: name,
                amount: parseInt((ref.payment_amount * 100)??0),
              },
            });
          });


        },
        paymentValidation: function(value) {

            this.broadcastPaymentValue();
        },
        broadcastPaymentValue: function() {
            this.$emit('payment-validation', {
                card_name: this.card_name,
                card_no: this.card_no,
                exp_month: this.exp_month,
                exp_year: this.exp_year,
                cvv: this.cvv,
                note: this.note,
                errors: this.errors,
                payment_gateway_id: this.payment_gateway_id,
                payment_gateway_type: this.payment_gateway_type,
                payment_gateway_name: this.payment_gateway_name,
              stripe: this.stripe,
              scard:this.scard
            });
        },
      priceFormat: function (amount, withSymbol=false) {
        // return parseFloat(priceVal).toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,');
        amount = parseFloat(amount);
        if (isNaN(amount)){
          return;
        }
        amount = new Number(amount);
        var myObj = { style: 'currency', currency: 'USD' };
        let currency_config = this.store_config.currency_format;
        let symbol = currency_config.symbol?currency_config.symbol:'$';

        let locale = currency_config.locale;
        let amountStr = amount;
        if (locale){
          amountStr = Number(amount).toLocaleString(locale, myObj);
          amountStr = amountStr.replace('US', '');
          amountStr = amountStr.replace('$', '');
        }
        if (withSymbol){
          amountStr = '<span class="pre">'+symbol + amountStr +'</span>';

          if (currency_config.post){
            amountStr = '<span class="post">'+ amountStr +symbol+'</span>';
          }
        }

        return amountStr;
      },
        validate: function() {
            this.errors = [];

            if(!this.payment_gateway_id) {
                this.errors.push('Please select a payment method.');
                return;
            }

            let gateway = this.rentmy_payment_gateways.find( gt => gt.id == this.payment_gateway_id);

            if(!gateway) {
                this.errors.push('Please select a payment method.');
                return;
            }

            var vpapp = this;

            if(gateway.name=="Stripe" || gateway.name=="Square" || gateway.name=="Transafe")
            {
                // this.stripe.createToken(this.scard).then(function (result) {
                //     if (result.error) {
                //         vpapp.errors.push('Unable to authorize your card.');
                //     } else {
                //         vpapp.card_no = result.token.id;
                //         vpapp.card_name = result.token.card.name;
                //         vpapp.exp_month = result.token.card.exp_month;
                //         vpapp.exp_year = result.token.card.exp_year;
                //         vpapp.cvv = (result.token.card.cvv_check=="unchecked")?0:1;
                //     }
                // });
            }
            else if(gateway.type == "online")
            {
                if(!this.card_name) {
                    this.errors.push('Please enter card holder name.');
                }
                if(!this.card_no) {
                    this.errors.push('Please enter card number.');
                }
                if(!this.exp_month) {
                    this.errors.push('Please enter card expiry month.');
                }
                if(!this.exp_year) {
                    this.errors.push('Please enter card expiry year.');
                }
                if(!this.cvv) {
                    this.errors.push('Please enter card cvv.');
                }
            }
            else
            {
                /* if(!this.note) {
                    this.errors.push('Note is required.');
                } */
            }

            return !this.errors.length;
        },

        loadCartElement: async function() {
            var data = new FormData();
            data.set('action', 'rentmy_cart_topbar');
            var cartResponse = await axios({
                method: 'post',
                url: rentmy_ajax_object.ajaxurl,
                data: data
            });
            this.cart = cartResponse.data;
            this.payment_amount = this.cart.total;
            if (this.cart.enduring_amount){
              this.payment_amount = this.payment_amount - this.cart.enduring_amount;
            }
        },

        isPartialRequired: function(gateway) {

            if(gateway.online_type!='card' && !gateway.config) return false;

            if((gateway.type != 'online') && !gateway.config.is_paid) return false;

            if(!this.config.payments || !this.cart.booking)   return false;

            let pc = this.config.payments;

            return (pc.type=="percent" && pc.booking && (pc.booking < 100)) || ((pc.type=="fixed") && pc.booking && (pc.booking < this.cart.total));
        },

        setCart: function(cart) {
            this.cart = cart;
            if(this.real_payment=="full")
                this.payment_amount = this.cart.total;
            else if(this.real_payment=="partial")
                this.payment_amount = this.cart.booking;
        },

      generateSquare: function (){
        this.$refs.sqPayment.generateToken();
      },

      getSquareToken: function (token){
        this.payment_token = token
        this.$emit('get_token_sq', this.payment_token)
      },
      getTransafeToken: function (token){
        this.payment_token = token

        this.$emit('get_token_transafe', this.payment_token)
      },

      setPaymentAmount: function (amount){
        this.payment_amount = amount
      }
    },
    created: function() {
        this.$parent.$on('checkValidation', this.paymentValidation);
      if (this.rentmy_payment_gateways.length == 1){
        this.payment_gateway_id = this.rentmy_payment_gateways[0].id
      }
    },

    mounted: async function() {
      await this.loadCartElement();


        for(x in this.rentmy_payment_gateways) {
            let gateway = this.rentmy_payment_gateways[x];
            if(gateway.type == 'online' && gateway.name == 'Stripe') {

              this.applePay();
                jQuery('#card-element').show();
                let locale = {locale: 'en'};
                if (this.store_id == 2277){
                  locale.locale = 'fr';
                }
                this.stripe = Stripe( jQuery('#card-element').attr('data-stripe-key'), locale );
                this.selements = this.stripe.elements();
                this.scard = this.selements.create('card');
                this.scard.mount('#card-element');
            }
        }


      serveBus.$on('loadCartElement', (data) => {
        if (data){

          this.payment_amount  = data.total;
          if (data.enduring_amount){
            this.payment_amount = this.payment_amount - data.enduring_amount;
          }
          this.cart.booking = data.booking;
          this.cart.total = data.total;
        }

      });
      serveBus.$on('taxLookUp', (data) => {
        if (data){
          this.payment_amount  = data.total;
          if (data.enduring_amount){
            this.payment_amount = this.payment_amount - data.enduring_amount;
          }
          this.cart.booking = data.booking;
          this.cart.total = data.total;
        }

      });

      serveBus.$on('stripePaymentError', (msg) => {
        this.stripeError = msg
      })
    },
    watch: {
        payment_gateway_id: function(gid) {
            let gateway = this.rentmy_payment_gateways.find( gt => gt.id == gid);

            if(gateway && gateway.type != "online") {
                this.note = "";
            }
            this.payment_gateway_name = gateway.name;
            this.payment_gateway_type = gateway.type;
        },
        real_payment: function(value) {
            if(value=="partial")    this.payment_amount = this.cart.booking;
            else    this.payment_amount = this.cart.total;
        }
    },
    computed: {
        minimum_booking: function() {
            let pc = this.config.payments;
            if(pc.type=="percent") {
                return pc.booking+'%';
            }
            else {
                return this.currency_symbol+this.priceFormat(this.cart.booking);
            }
            return true;//(pc.type=="percent" && pc.booking < 100) || (pc.type=="fixed" && pc.booking < this.cart.total);
        }
    },
    filters: {
        uppercase: function(value) {
            if (!value) return ''
            value = value.toString()
            return value.charAt(0).toUpperCase() + value.slice(1)
        }
    }
}
</script>
