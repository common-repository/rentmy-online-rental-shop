<template>
  <div>
    <div v-if="errors.length > 0" class="error mb-1">
      <h6 class="error">Required field missing</h6>
    </div>
    <div class="delivery-checkout-multistore-body">
      <div class="accordion" id="delivery-multistore-accordion">
        <div class="card">
          <div class="card-header pb-3" id="headingOne">
            <h2 class="mb-0 card-head-row">
              <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">          
                <div class="delivery-collaps">
                  <div class="delivery-collaps-row">
                    <div class="delivery-collaps-title">
                          {{config_labels?.cart?.lbl_drop_off_address??"Drop off address"}}
                    </div>
                    <div class="delivery-collaps-nameaddress">
                      <span>{{mergingData([drop_address.shipping_first_name, drop_address.shipping_last_name])}}</span>
                      <span>{{mergingData([drop_address.shipping_address1, drop_address.shipping_address2])}}</span>
                      <span>{{mergingData([drop_address.shipping_city, drop_address.shipping_state, drop_address.shipping_zipcode])}}</span>
                    </div>
                    <div class="delivery-collaps-above">
                    </div>
                  </div>
                </div>
                <div class="collaps-arrow">
                  <i class="fa fa-angle-down"></i>
                  <i class="fa fa-angle-up"></i>
                </div>
              </button>
              <div class="delivery-collaps-above same-as-above">
                <div class="checkbox sameabove-checkbox">
                  <label class="m-checkbox">
                    <input type="checkbox" id="sameAsAbove" name="same_as_above" v-model="same_as_above" />
                      {{config_labels?.checkout_info?.lbl_same_as_billing??"Same as above"}}
                    <span></span>
                  </label>
                </div>
              </div>
            </h2>
          </div>
      
          <div id="collapseOne" class="collapse" aria-labelledby="headingOne" data-parent="#delivery-multistore-accordion">
            <div class="card-body">
                <form class="row">
                  <div class="col-md-12 order_details">
                      <div class="row">
                          <div class="col-md-6">
                              <div class="form-group">
                                  <label for="shippingFirstName">{{ config_labels.checkout_info.lbl_shipping_first_name ?? 'First Name' }}</label>
                                  <input type="text" id="shippingFirstName" v-model="drop_address.shipping_first_name" class="col-md-12 mb-2 form-control" />
                              </div>
                          </div>
                          <!---->
                          <div class="col-md-6">
                              <div class="form-group">
                                  <label for="shippingLastName">{{ config_labels.checkout_info.lbl_shipping_last_name ?? 'Last Name' }}</label>
                                  <input type="text" id="shippingLastName" v-model="drop_address.shipping_last_name" class="col-md-12 mb-2 form-control" />
                              </div>
                          </div>
                          <div class="col-md-12">
                              <div class="form-group">
                                  <label for="shippingEmail"> {{ config_labels.checkout_info.lbl_email ?? 'Email' }} </label><sup style="color: red;">*</sup>
                                  <input type="email" id="shippingEmail" v-model="drop_address.shipping_email" class="form-control" />
                              </div>
                          </div>
                          <div class="col-md-12">
                              <div class="form-group">
                                  <label for="">{{ config_labels.checkout_info.lbl_country??"Country" }}</label><sup style="color: red;">*</sup>
                                  <select class="form-control dropdown-cls m-input" v-model="drop_address.shipping_country">
                                    <option disabled value="">Select One</option>
                                    <option v-for="country in rentmy_countries" :key="country.id" :value=country.code>
                                      {{ country.name }}
                                    </option>
                                  </select>
                              </div>
                          </div>
                          <!---->
                          <div class="col-md-12">
                              <div class="form-group">
                                  <label for="">{{ config_labels.checkout_info.lbl_shipping_address_line_1??"Address Line 1" }}</label><sup style="color: red;">*</sup>
                                  <input type="text" class="col-md-12 mb-2 form-control" v-model="drop_address.shipping_address1"/>
                              </div>
                          </div>
                          <div class="col-md-12">
                              <div class="form-group">
                                  <label for="">{{ config_labels.checkout_info.lbl_shipping_address_line_2??"Address Line 2" }}</label>
                                  <input type="text" class="col-md-12 mb-2 form-control" v-model="drop_address.shipping_address2"/>
                              </div>
                          </div>
                          <div class="col-md-6">
                              <div class="form-group">
                                  <label for="">{{ config_labels.checkout_info.lbl_shipping_city??"City" }}</label><sup style="color: red;">*</sup>
                                  <input type="text" class="form-control" v-model="drop_address.shipping_city"/>
                              </div>
                          </div>
                          <div class="col-md-6">
                              <div class="form-group">
                                  <label for="">{{ config_labels.checkout_info.lbl_state??"State" }}</label><sup style="color: red;">*</sup>
                                  <input type="text" class="col-md-12 form-control" v-model="drop_address.shipping_state"/>
                              </div>
                          </div>
                          <div class="col-md-6">
                              <div class="form-group">
                                  <label for="">{{ config_labels.checkout_info.lbl_shipping_zipcode??"Zipcode" }}</label><sup style="color: red;">*</sup>
                                  <input type="text" class="col-md-12 form-control" v-model="drop_address.shipping_zipcode"/>
                              </div>
                          </div>
                      </div>
                  </div>
              </form>

            </div>
          </div>
        </div>
        <div class="card" v-if="deliveryFlow.delivery_flow != 1">
          <div class="card-header pb-3" id="headingTwo">
            <h2 class="mb-0 card-head-row">
              <button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                <div class="delivery-collaps">
                  <div class="delivery-collaps-row">
                    <div class="delivery-collaps-title">
                      {{config_labels?.cart?.lbl_pickup_address??"Pickup address"}}
                    </div>
                    <div class="delivery-collaps-nameaddress">
                      <span>{{mergingData([pickup_address.shipping_first_name, pickup_address.shipping_last_name])}}</span>
                      <span>{{mergingData([pickup_address.shipping_address1, pickup_address.shipping_address2])}}</span>
                      <span>{{mergingData([pickup_address.shipping_city, pickup_address.shipping_state, pickup_address.shipping_zipcode])}}</span>
                    </div>
                    <div class="delivery-collaps-above">
      
                    </div>
                  </div>
                </div>
                <div class="collaps-arrow">
                  <i class="fa fa-angle-down"></i>
                  <i class="fa fa-angle-up"></i>
                </div>
              </button>
            </h2>
          </div>
          <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#delivery-multistore-accordion">
            <div class="card-body">
                <form class="row block">
                  <div class="col-md-12 order_details">
                      <div class="row">
                          <div class="col-md-6">
                              <div class="form-group">
                                  <label for="shippingFirstName">{{ config_labels.checkout_info.lbl_shipping_first_name ?? 'First Name' }}</label>
                                  <input type="text" class="col-md-12 mb-2 form-control" v-model="pickup_address.shipping_first_name"/>
                              </div>
                          </div>
                          <!---->
                          <div class="col-md-6">
                              <div class="form-group">
                                  <label for="shippingLastName">{{ config_labels.checkout_info.lbl_shipping_last_name ?? 'Last Name' }}</label>
                                  <input type="text" class="col-md-12 mb-2 form-control" v-model="pickup_address.shipping_last_name"/>
                              </div>
                          </div>
                          <div class="col-md-12">
                              <div class="form-group">
                                  <label for="shippingEmail">  {{ config_labels.checkout_info.lbl_email ?? 'Email' }}  </label><sup style="color: red;">*</sup>
                                  <input type="email" class="form-control" v-model="pickup_address.shipping_email"/>
                              </div>
                          </div>
                          <div class="col-md-12">
                              <div class="form-group">
                                  <label for="">{{ config_labels.checkout_info.lbl_country??"Country" }}</label><sup style="color: red;">*</sup>
                                  <select class="form-control dropdown-cls m-input" v-model="pickup_address.shipping_country">
                                    <option disabled value="">Select One</option>
                                    <option v-for="country in rentmy_countries" :key="country.id" :value=country.code>
                                      {{ country.name }}
                                    </option>
                                  </select>
                              </div>
                          </div>
                          <!---->
                          <div class="col-md-12">
                              <div class="form-group">
                                  <label for="">{{ config_labels.checkout_info.lbl_shipping_address_line_1??"Address Line 1" }}</label><sup style="color: red;">*</sup>
                                  <input type="text" class="col-md-12 mb-2 form-control" v-model="pickup_address.shipping_address1"/>
                              </div>
                          </div>
                          <div class="col-md-12">
                              <div class="form-group">
                                  <label for="">{{ config_labels.checkout_info.lbl_shipping_address_line_2??"Address Line 2" }}</label>
                                  <input type="text" class="col-md-12 mb-2 form-control" v-model="pickup_address.shipping_address2"/>
                              </div>
                          </div>
                          <div class="col-md-6">
                              <div class="form-group">
                                  <label for="">{{ config_labels.checkout_info.lbl_shipping_city??"City" }}</label><sup style="color: red;">*</sup>
                                  <input type="text" class="form-control" v-model="pickup_address.shipping_city"/>
                              </div>
                          </div>
                          <div class="col-md-6">
                              <div class="form-group">
                                  <label for="">{{ config_labels.checkout_info.lbl_state??"State" }}</label><sup style="color: red;">*</sup>
                                  <input type="text" class="col-md-12 form-control" v-model="pickup_address.shipping_state"/>
                              </div>
                          </div>
                          <div class="col-md-6">
                              <div class="form-group">
                                  <label for="">{{ config_labels.checkout_info.lbl_shipping_zipcode??"Zipcode" }}</label><sup style="color: red;">*</sup>
                                  <input type="text" class="col-md-12 form-control" v-model="pickup_address.shipping_zipcode"/>
                              </div>
                          </div>
                      </div>
                  </div>
              </form>
            </div>
          </div>
        </div>
    
        
        <div class="text-right">
            <button class="btn delivery-cost-btn" v-on:click="getDeliveryCost">
              <i v-if="loading" class="fa fa-spinner fa-spin"></i>
              {{config_labels?.checkout_info?.btn_get_delivery_cost??"Get Delivery Cost"}}<i class="fa fa-forward"></i>
          </button>
        </div>

        <div v-if="charge_data.charges" class="delivery-cost-option mt-base">
          <ul>
            <li v-if="Object.keys(delivery_flows).length !== 0" v-for="(flow, key) in delivery_flows">
              <span v-if="key=='storage_delivery'">{{flow.label2}} :<span v-html="priceFormat(flow.loaded.delivery.charge, true)"></span></span>
              <span v-else-if="key=='storage_pickup'">{{flow.label1}} :<span v-html="priceFormat(flow.loaded.pickup.charge, true)"></span></span>
              <span v-else>{{flow.label}} :<span v-html="priceFormat(flow.charge, true)"></span></span>
            </li>
            <li v-if="charge_data.total"><span >Total :<span v-html="priceFormat(charge_data.total, true)"></span></span></li>
          </ul>
<!--          <ul>-->
<!--            <li v-if="charge_data.charges.drop_off"><span >Drop Off :<span v-html="priceFormat(charge_data.charges.drop_off, true)"></span></span></li>-->
<!--            <li v-if="charge_data.charges.move"><span >Move :<span v-html="priceFormat(charge_data.charges.move, true)"></span></span></li>-->
<!--            <li v-if="charge_data.charges.pickup"><span >Pickup :<span v-html="priceFormat(charge_data.charges.pickup, true)"></span></span></li>-->
<!--            <li v-if="charge_data.charges.storage"><span >Storage :<span v-html="priceFormat(charge_data.charges.storage, true)"></span></span></li>-->
<!--            <li v-if="charge_data.total"><span >Total :<span v-html="priceFormat(charge_data.total, true)"></span></span></li>-->
<!--          </ul>-->
        </div>
      </div> 
    </div>
  </div>

</template>

<script>
module.exports = {
    data(){
      return{
        rentmy_countries: rm_countries,
        same_as_above: true,
        drop_address: {
          shipping_first_name: '',
          shipping_last_name: '',
          shipping_email: '',
          shipping_country: rm_storeCountry != undefined ? rm_storeCountry : 'US',
          shipping_address1: '',
          shipping_address2: '',
          shipping_city: '',
          shipping_zipcode: '',
          shipping_state: '',
        },
        pickup_address: {
          shipping_first_name: '',
          shipping_last_name: '',
          shipping_email: '',
          shipping_country: rm_storeCountry != undefined ? rm_storeCountry : 'US',
          shipping_address1: '',
          shipping_address2: '',
          shipping_city: '',
          shipping_zipcode: '',
          shipping_state: '',
        },
        charge_data: {},
        drop_errors: [],
        pickup_errors: [],
        errors: [],
        deliveryFlow: {},
        delivery_flows: {},
        loading: false,
        request_delivery_flow: '',
        config_labels: config_labels,
      }
    },

  methods: {

    validate: function (){


      this.drop_errors = [];
      this.pickup_errors = [];
      //dropoff adress
      if (!this.drop_address.shipping_email){
        this.drop_errors.push('Email is required');
      }
      if (!this.drop_address.shipping_address1){
        this.drop_errors.push('Address line1 is required');
      }
      if (!this.drop_address.shipping_country){
        this.drop_errors.push('Country is required');
      }
      if (!this.drop_address.shipping_city){
        this.drop_errors.push('City is required');
      }
      if (!this.drop_address.shipping_state){
        this.drop_errors.push('State is required');
      }
      if (!this.drop_address.shipping_zipcode){
        this.drop_errors.push('Zipcode is required');
      }

      //pickup address
      if (this.deliveryFlow.delivery_flow != 1){
        if (!this.pickup_address.shipping_email){
          this.pickup_errors.push('Email is required');
        }
        if (!this.pickup_address.shipping_address1){
          this.pickup_errors.push('Address line1 is required');
        }
        if (!this.pickup_address.shipping_country){
          this.pickup_errors.push('Country is required');
        }
        if (!this.pickup_address.shipping_city){
          this.pickup_errors.push('City is required');
        }
        if (!this.pickup_address.shipping_state){
          this.pickup_errors.push('State is required');
        }
        if (!this.pickup_address.shipping_zipcode){
          this.pickup_errors.push('Zipcode is required');
        }
      }
      this.errors = this.drop_errors.concat(this.pickup_errors);
      return this.errors.length > 0;
    },
      getDeliveryCost: function (){

        if (this.validate()){
          return;
        }
        const ref = this;
        const flow = ref.deliveryFlow;
        ref.loading = true;
        let ajaxdata = {
          action: 'rentmy_options',
          action_type: 'get_multi_store_cost',
          data : {
            ...flow,
            drop_address: ref.drop_address,
            pickup_address: ref.pickup_address
          }
        }

        jQuery.post(rentmy_ajax_object.ajaxurl, ajaxdata, function (response) {

          ref.charge_data = response?.data

          this.delivery_flows = {};
          if (ref.charge_data?.flows){

            ref.request_delivery_flow = ref.charge_data?.request?.delivery_flow;

            let label = config_labels?.cart?.lbl_drop_off_storage_pickup;
            if (ref.request_delivery_flow == 1){
              label = config_labels?.cart?.lbl_drop_off_pickup;
            }else if (ref.request_delivery_flow == 2){
              label = config_labels?.cart?.lbl_drop_off_move_pickup;
            }
            console.log(ref.request_delivery_flow)
            console.log(label)
            var labels = label.split(",");
            console.log(labels)
            for (key in ref.charge_data?.flows){

              if (key == 'storage'){
                ref.delivery_flows['storage_pickup'] = ref.charge_data?.flows[key];
                ref.delivery_flows['storage_pickup']['label1'] = labels[1];

                ref.delivery_flows['storage_delivery'] = ref.charge_data?.flows[key];
                ref.delivery_flows['storage_delivery']['label2'] = labels[2];

              }else{
                ref.delivery_flows[key] = ref.charge_data?.flows[key];
              }

              if (key=='drop_off'){
                if (ref.request_delivery_flow==1){
                  ref.delivery_flows[key]['label'] = labels[0]
                }else if (ref.request_delivery_flow==2){
                  ref.delivery_flows[key]['label'] = labels[0]
                }else if (ref.request_delivery_flow==3){
                  ref.delivery_flows[key]['label'] = labels[3]
                }



              }
              if (key=='pickup'){
                if (ref.request_delivery_flow==1){
                  ref.delivery_flows[key]['label'] = labels[1]
                }else if (ref.request_delivery_flow==2){
                  ref.delivery_flows[key]['label'] = labels[2]
                }else if (ref.request_delivery_flow==3){
                  ref.delivery_flows[key]['label'] = labels[0]
                }
              }

              if (key=='move'){
                if (ref.request_delivery_flow==2){
                  ref.delivery_flows[key]['label'] = labels[1]
                }
              }

            }
            console.log(ref.delivery_flows)

          }

          let drop = ref.drop_address
          ref.$emit('charge', {
            ...drop,
            delivery_charge: ref.charge_data.total,
            delivery_multi_store: ref.charge_data
          });
          ref.loading = false
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
      let currency_config = this.charge_data.currency_format;
      let symbol = currency_config.symbol?currency_config.symbol:'$';
      let locale = currency_config.locale;
      let amountStr = parseFloat(amount).toFixed(2);
      if (locale){
        amountStr = Number(amount).toLocaleString(locale, myObj);
        amountStr = amountStr.replace('US', '');
        amountStr = amountStr.replace('$', '');
      }
      if (withSymbol){
        amountStr = '<span class="pre">'+symbol + amountStr +'<span>';

        if (currency_config.post){
          amountStr = '<span class="post">'+ amountStr +symbol+'<span>';
        }
      }

      return amountStr;
    },

    mergingData: function (dataArray){
        if (!dataArray)
          return dataArray;

      return dataArray.join(' ');
    },
  },
  watch:{
  },
  mounted(){
      let ref = this;
    let flow = localStorage.getItem('deliveryFlow');
    if (flow)
      this.deliveryFlow = JSON.parse(flow);

    serveBus.$on('broadcastBillingValue', (data) => {
      if (ref.same_as_above){
        ref.drop_address.shipping_first_name = data.first_name;
        ref.drop_address.shipping_last_name = data.last_name;
        ref.drop_address.shipping_email = data.email;
        ref.drop_address.shipping_country = data.country;
        ref.drop_address.shipping_address1 = data.address_line1;
        ref.drop_address.shipping_address2 = data.address_line2;
        ref.drop_address.shipping_state = data.state;
        ref.drop_address.shipping_city = data.city;
        ref.drop_address.shipping_zipcode = data.zipcode;
      }
    });
  }
};
</script>