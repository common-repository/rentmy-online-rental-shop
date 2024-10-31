<template>
  <div class="billingdetails-leftside-inner fulfillment-form-area" v-if="is_fullfilment">
    <h2 class="wc-checkout-title pt-0 pb-3">{{ config_labels.checkout_info.title_shipping }}</h2>
    <ul class="rentmy-error-wrapper" v-if="errors.length">
      <li v-for="(error,i) in errors" :key="i">
        <strong>{{ error }}</strong>
      </li>
    </ul>
    <br>
    <div class="rent-my-loader text-center" v-if="Object.keys(rm_delivery_settings).length == 0">
      <i class="fa fa-spin fa-spinner fa-2x"></i>
    </div>
    <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
      <li class="nav-item" role="presentation" v-on:click="selectFullfilmentMethod('instore_pickup')"
          v-if="rm_delivery_settings.instore_pickup">
        <a :class="'nav-link ' + ((fftype=='instore_pickup')?'active':'')" id="pills-home-tab" data-toggle="pill"
           href="#pills-pickup" role="tab" aria-controls="pills-pickup" aria-selected="true">
          <img :src="base_url + 'assets/images/pickup-truck-bold.png'" class="fulfillment-active-img">
          <img :src="base_url + 'assets/images/pickup-truck.png'" class="fulfillment-noneactive-img">
          {{ config_labels.checkout_info.title_pickup_option??"Pickup" }}
        </a>
      </li>
      <li class="nav-item" role="presentation" v-on:click="selectFullfilmentMethod('shipping')"
          v-if="rm_delivery_settings.shipping">
        <a :class="'nav-link ' + ((fftype=='shipping')?'active':'')" id="pills-profile-tab" data-toggle="pill"
           href="#pills-shipping" role="tab" aria-controls="pills-shipping" aria-selected="false">
          <img :src="base_url + 'assets/images/shipping-bold.png'" class="fulfillment-active-img">
          <img :src="base_url + 'assets/images/shipping.png'" class="fulfillment-noneactive-img">
          {{ config_labels.checkout_info.title_shipping_option??"Shipping" }}
        </a>
      </li>
      <li class="nav-item" role="presentation" v-on:click="selectFullfilmentMethod('delivery')"
          v-if="rm_delivery_settings.delivery">
        <a :class="'nav-link ' + ((fftype=='delivery')?'active':'')" id="pills-contact-tab" data-toggle="pill"
           href="#pills-delivery" role="tab" aria-controls="pills-delivery" aria-selected="false">
          <img :src="base_url + 'assets/images/delivery-bold.png'" class="fulfillment-active-img">
          <img :src="base_url + 'assets/images/delivery.png'" class="fulfillment-noneactive-img">
          {{ config_labels.checkout_info.title_delivery_option??"Delivery" }}
        </a>
      </li>
    </ul>

    <div class="tab-content" id="pills-tabContent">
      <!-- Pickup  -->
      <div :class="'tab-pane fade ' +((fftype=='instore_pickup')?'show active':'')" id="pills-pickup" role="tabpanel"
           aria-labelledby="pills-pickup-tab">
        <div class="custom-control custom-radio" v-if="rm_delivery_settings.instore_pickup" v-show="false">
          <input type="radio" id="pickup-check" name="fftype" class="custom-control-input" v-model="fftype"
                 value="instore_pickup">
          <label class="custom-control-label mb-0"
                 for="pickup-check">&nbsp;&nbsp;&nbsp;{{ config_labels.checkout_info.title_pickup_option }}</label>
        </div>
        <div
            :class="'collapse pt-2 show '+((rm_delivery_settings.instore_pickup && (!rm_delivery_settings.shipping && !rm_delivery_settings.delivery))?'show':'')"
            id="checkout-pickup" aria-labelledby="headingOne" data-parent="#accordionExample"
            v-if="rm_delivery_settings.instore_pickup">
          <div class="card-body p-0 fulfilment-body">
            <div class="row">
              <div class="col-xl-12">
                <div class="custom-control custom-radio" v-for="location in rentmy_locations" :key="location.id">
                  <input type="radio" :id="location.id" name="rm_instore_loc" class="custom-control-input"
                         :value="location.id" v-model="rm_instore_loc">
                  <label class="custom-control-label mb-0" :for="location.id">{{ location.name }}
                    ({{ location.location }})</label>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!--shipping-->
      <div :class="'tab-pane fade ' +((fftype=='shipping')?'show active':'')" id="pills-shipping" role="tabpanel"
           aria-labelledby="pills-shipping-tab">
        <div class="custom-control custom-radio"
             v-if="rm_delivery_settings.shipping && Object.keys(customer_info).length == 0" v-show="false">
          <input v-on:click="getShippingDataInit" type="radio" id="shipping-check" name="fftype"
                 class="custom-control-input" v-model="fftype" value="shipping">
          <label v-on:click="getShippingDataInit" class="custom-control-label mb-0" for="shipping-check"
                 data-toggle="collapse" data-target="#checkout-shippingaddress" aria-expanded="false"
                 aria-controls="collapseExample">&nbsp;&nbsp;&nbsp;{{ config_labels.checkout_info.title_shipping_option }}</label>
        </div>
        <div class="custom-control custom-radio"
             v-if="rm_delivery_settings.shipping && Object.keys(customer_info).length != 0" v-show="false">
          <input type="radio" id="shipping-check" name="fftype" class="custom-control-input" v-model="fftype"
                 value="shipping">
          <label class="custom-control-label mb-0"
                 for="shipping-check">&nbsp;&nbsp;&nbsp;{{ config_labels.checkout_info.title_shipping_option }}</label>
        </div>
        <div
            :class="'collapse pt-2 show '+((rm_delivery_settings.shipping && (!rm_delivery_settings.instore_pickup && !rm_delivery_settings.delivery))?'show':'')"
            id="checkout-shippingaddress" aria-labelledby="headingOne" data-parent="#accordionExample"
            v-if="rm_delivery_settings.shipping">
          <div class="card card-body p-0">
            <div class="sameabove-checkbox" v-if="Object.keys(customer_info).length == 0">
              <label class="m-checkbox">
                <input id="shipping_checkbox" type="checkbox" v-on:change="shippingCheck" name="shipping_check"
                       :checked="is_shipping_with_above">
                {{config_labels?.checkout_info?.lbl_same_as_billing??"Same as above"}}
                <span></span>
              </label>
            </div>
            <div class="container">
              <div class="row" v-if="is_shipping_field">
                <div class="col-xl-6" v-if="Object.keys(customer_info).length == 0">
                  <div class="form-group">
                    <label for="shipping_country">{{ config_labels.checkout_info.lbl_shipping_name ?? 'Name' }}</label>
                    <input type="text" class="form-control" name="ship_to_name" id="ship_to_name"
                           autocomplete="shipping address-line1"
                           v-bind:value="ship_to_name"
                           @change="ship_to_name = $event.target.value;changeShippingData();shipping_name=$event.target.value">
                  </div>
                </div>
                <div class="col-xl-6" v-if="Object.keys(customer_info).length == 0">
                  <div class="form-group">
                    <label for="shipping_country">{{
                        config_labels.checkout_info.lbl_shipping_mobile ?? 'Mobile Number'
                      }}</label>
                    <input type="text" class="form-control" name="ship_to_phone" id="ship_to_phone"
                           autocomplete="shipping address-line1"
                           v-bind:value="ship_to_phone"
                           @change="ship_to_phone = $event.target.value;changeShippingData(); shipping_phone = $event.target.value">
                  </div>
                </div>

                <div class="col-xl-12" v-if="Object.keys(customer_info).length == 0">
                  <div class="form-group">
                    <label for="ship_to_email">{{ config_labels.checkout_info.lbl_shipping_email ?? 'Email' }}</label>
                    <input type="text" class="form-control" name="ship_to_phone" id="ship_to_email"
                           autocomplete="shipping address-line1"
                           v-bind:value="ship_to_email"
                           @change="ship_to_email = $event.target.value;changeShippingData(); shipping_email = $event.target.value">
                  </div>
                </div>


                <div class="col-xl-12" v-if="Object.keys(customer_info).length == 0">
                  <div class="form-group">
                    <label for="shipping_country">{{ config_labels.checkout_info.lbl_country }}</label>
                    <select class="form-control" name="shipping_country" id="shipping_country"
                            v-bind:value="shipping_country || 'US'"
                            @change="shipping_country=$event.target.value;changeShippingData()">
                      <option disabled value="">Select One</option>
                      <option v-for="country in rentmy_countries" :key="country.id" :value=country.code>
                        {{ country.name }}
                      </option>
                    </select>
                  </div>
                </div>
                <div class="col-xl-12" v-if="Object.keys(customer_info).length == 0">
                  <div class="form-group">
                    <label for="shipping_address1">{{ config_labels.checkout_info.lbl_shipping_address_line_1 }}</label>
                    <input type="text" class="form-control" name="shipping_address1" id="shipping_address1"
                           autocomplete="shipping address-line1"
                           v-bind:value="shipping_address1"
                           @change="shipping_address1 = $event.target.value;changeShippingData()">
                  </div>
                </div>
                <div class="col-xl-12" v-if="Object.keys(customer_info).length == 0">
                  <div class="form-group">
                    <label for="shipping_address2">{{ config_labels.checkout_info.lbl_shipping_address_line_2 }}</label>
                    <input type="text" class="form-control" name="shipping_address2" id="shipping_address2"
                           autocomplete="shipping  address-line2"
                           v-bind:value="shipping_address2"
                           @change="shipping_address2 = $event.target.value;changeShippingData()">
                  </div>
                </div>
                <div class="col-xl-6" v-if="Object.keys(customer_info).length == 0">
                  <div class="form-group" id="shipping_city_container">
                    <label for="shipping_city">{{ config_labels.checkout_info.lbl_shipping_city }}</label>
                    <input type="text" class="form-control" name="shipping_city" id="shipping_city" autocomplete="off"
                           v-bind:value="shipping_city"
                           @change="shipping_city = $event.target.value;changeShippingData()">
                  </div>
                </div>
                <div class="col-xl-6" v-if="Object.keys(customer_info).length == 0">
                  <div class="form-group">
                    <label for="shipping_zipcode">{{ config_labels.checkout_info.lbl_shipping_zipcode }}</label>
                    <input type="text" class="form-control" name="shipping_zipcode" id="shipping_zipcode"
                           autocomplete="shipping postal-code"
                           v-bind:value="shipping_zipcode"
                           @change="shipping_zipcode = $event.target.value;changeShippingData()">
                  </div>
                </div>
                <div class="col-xl-6" v-if="Object.keys(customer_info).length == 0">
                  <div class="form-group">
                    <label for="shipping_state">{{ config_labels.checkout_info.lbl_state }}</label>
                    <input type="text" class="form-control" name="shipping_state" id="shipping_state"
                           autocomplete="shipping region"
                           v-bind:value="shipping_state"
                           @change="shipping_state = $event.target.value;changeShippingData()">
                  </div>
                </div>
              </div>


              <div class="card-body p-0 fulfilment-body" v-show="Object.keys(customer_info).length != 0">
                <!--                        <h5 style="padding-left: 15px;">Shipping Address</h5>-->
                <div class="custom-control custom-radio" style="padding-left: 0px;">
                  <label class="m-radio col-md-12" v-for="(address, index) in shipping_address" :key="index">
                    <input name="shipping_loc" type="radio" :checked="address.id==billing_address_id"
                           v-on:click="selectShippingAddress(address.id)" class="ng-valid ng-dirty ng-touched">
                    {{ address.full_address }}</label>
                  <label class="m-radio col-md-12">
                    <input name="shipping_loc" type="radio" v-on:click="createNewShippingAddress"
                           class="ng-valid ng-dirty ng-touched"> Create New </label>

                </div>
                <div class="create-new-address row" v-show="ia_create_new_shipping_address">
                  <div class="col-xl-12">
                    <div class="form-group">
                      <label for="country">{{ config_labels.checkout_info.lbl_country }}*</label>
                      <select class="form-control" name="country" v-model="new_shipping_address.country"
                              autocomplete="billing country">
                        <option disabled value="">Select One</option>
                        <option v-for="country in rentmy_countries" :key="country.id" :value="country.code">
                          {{ country.name }}
                        </option>
                      </select>
                    </div>
                  </div>
                  <div class="col-xl-6">
                    <div class="form-group">
                      <label for="address_line1">{{ config_labels.checkout_info.lbl_address_line_1 }}*</label>
                      <input type="text" class="form-control" name="address_line1"
                             v-model="new_shipping_address.address_line1" autocomplete="billing address-line1">
                    </div>
                  </div>
                  <div class="col-xl-6">
                    <div class="form-group">
                      <label for="address_line2">{{ config_labels.checkout_info.lbl_address_line_2 }}</label>
                      <input type="text" class="form-control" name="address_line2"
                             v-model="new_shipping_address.address_line2" autocomplete="billing address-line2">
                    </div>
                  </div>
                  <div class="col-xl-6">
                    <div class="form-group">
                      <label for="new_shipping_city">{{ config_labels.checkout_info.lbl_city }}*</label>
                      <input type="text" class="form-control" name="city" id="new_shipping_city"
                             v-model="new_shipping_address.city" autocomplete="billing address-level2"
                      >
                    </div>
                  </div>
                  <div class="col-xl-6">
                    <div class="form-group">
                      <label for="zipcode">{{ config_labels.checkout_info.lbl_zipcode }}*</label>
                      <input type="text" class="form-control" id="new_shipping_zipcode" name="zipcode"
                             v-model="new_shipping_address.zipcode" autocomplete="billing postal-code">
                    </div>
                  </div>
                  <div class="col-xl-6">
                    <div class="form-group">
                      <label for="state">{{ config_labels.checkout_info.lbl_state }}*</label>
                      <input type="text" class="form-control" id="new_shipping_state" name="state"
                             v-model="new_shipping_address.state" autocomplete="billing region"
                      >
                    </div>
                  </div>

                  <div class="col-md-12 col-xs-12">
                    <button class="btn theme-btn bg-secondary text-light" v-on:click="addNewShippingAddress"
                            type="button">Add
                    </button>
                    <button class="btn bg-secondary text-light" v-on:click="ia_create_new_shipping_address=false"
                            type="button">Cancel
                    </button>
                  </div>
                </div>
              </div>


              <div class="col-xl-12">
                <div class="form-group mb-2" v-if="!free_shipping">
                  <button type="button" class="btn btn-sm placeorder-btn float-right" v-on:click="getShippingMethod"
                          :disabled="loading">
                    <i v-if="loading" class="fa fa-spinner fa-spin"></i>
                    {{ config_labels.checkout_info.btn_get_shipping_method }}
                  </button>
                </div>
              </div>
              <div class="col-xl-12 mb-2">
                <div v-for="(methods,index) in shipping_methods" :key="index">
                  <template v-if="index=='flat' || index=='standard'">
                    <div>
                      <label class="radio-container radiolist-container">
                        <input type='radio' v-model='shipping_method' :value='JSON.stringify({index,method:methods})'>
                        <span class='rentmy-radio-text'> {{ methods.carrier_code }} </span>
                        <!-- <span class="rentmy-radio-date">Estimated Delivery Date: </span> -->
                        <!-- <span class="rentmy-radio-day">  Delivery days: </span> -->
                        <span class="rentmy-radio-price">{{ currency_symbol }}{{ priceFormat(methods.charge) }}</span>
                        <span class="checkmark"></span>
                      </label>
                    </div>
                  </template>
                  <template v-else-if="index != 'weight' && index != 'boxes'">
                    <div v-for="(method,idx) in methods" :key="index+`_`+idx">
                      <label class="radio-container radiolist-container">
                        <input type='radio' v-model='shipping_method' :value='JSON.stringify({index,method})'>
                        <span class='rentmy-radio-text'> {{ method.service_name }} </span>
                        <!-- <span class="rentmy-radio-date">Estimated Delivery Date: {{ dateFormat(method.delivery_date) }}</span> -->
                        <!-- <span class="rentmy-radio-day">  Delivery days: {{ method.delivery_days }}</span> -->
                        <span class="rentmy-radio-price">{{ currency_symbol }}{{ priceFormat(method.charge) }}</span>
                        <span class="checkmark"></span>
                      </label>
                    </div>
                  </template>
                </div>
                <div v-if="shipping_error" v-html="shipping_error"></div>
              </div>
            </div>


          </div>
        </div>
      </div>

      <!--      delivery-->
      <div :class="'tab-pane fade ' +((fftype=='delivery')?'show active':'')" id="pills-delivery" role="tabpanel" aria-labelledby="pills-contact-tab">
        <div v-if="!delivery_flow">
          <div class="custom-control custom-radio"
               v-if="rm_delivery_settings.delivery && Object.keys(customer_info).length != 0" v-show="false">
            <input type="radio" id="delivery-check" name="fftype" class="custom-control-input" v-model="fftype"
                   value="delivery">
            <label class="custom-control-label mb-0"
                   for="delivery-check">&nbsp;&nbsp;&nbsp;{{ config_labels.checkout_info.title_delivery_option }}</label>
          </div>
          <div class="custom-control custom-radio"
               v-if="rm_delivery_settings.delivery && Object.keys(customer_info).length == 0" v-show="false">
            <input v-on:click="getDeliveryDataInit" type="radio" id="delivery-check" name="fftype"
                   class="custom-control-input" v-model="fftype" value="delivery">
            <label v-on:click="getDeliveryDataInit" class="custom-control-label mb-0" for="delivery-check"
                   data-toggle="collapse" data-target="#checkout-delivery" aria-expanded="false"
                   aria-controls="collapseExample">&nbsp;&nbsp;&nbsp;{{ config_labels.checkout_info.title_delivery_option }}</label>
          </div>
          <div
              :class="'collapse pt-2 show '+((rm_delivery_settings.delivery && (!rm_delivery_settings.shipping && !rm_delivery_settings.instore_pickup))?'show':'')"
              id="checkout-delivery" aria-labelledby="headingOne" data-parent="#accordionExample"
              v-if="rm_delivery_settings.delivery">
            <div class="card card-body p-0">
              <div class="sameabove-checkbox" v-if="Object.keys(customer_info).length == 0">
                <label class="m-checkbox">
                  <input id="delivery_checkbox" type="checkbox" v-on:change="deliveryCheck" name="delivery_check"
                         :checked="is_delivery_with_above">
                  {{config_labels?.checkout_info?.lbl_same_as_billing??"Same as above"}}
                  <span></span>
                </label>
              </div>
              <div class="container">
                <div class="row" v-if="is_delivery_field">
                  <div class="col-xl-6" v-if="Object.keys(customer_info).length == 0">
                    <div class="form-group">
                      <label for="shipping_country">{{ config_labels.checkout_info.lbl_shipping_name ?? 'Name' }}</label>
                      <input type="text" class="form-control" name="ship_to_name" id="delivery_to_name"
                             autocomplete="shipping address-line1"
                             v-bind:value="delivery_to_name"
                             @change="shipping_name = delivery_to_name = $event.target.value;changeDeliveryData()">
                    </div>
                  </div>
                  <div class="col-xl-6" v-if="Object.keys(customer_info).length == 0">
                    <div class="form-group">
                      <label for="shipping_country">{{ config_labels.checkout_info.lbl_shipping_mobile }}</label>
                      <input type="text" class="form-control" name="delivery_to_phone" id="delivery_to_phone"
                             autocomplete="shipping address-line1"
                             v-bind:value="delivery_to_phone"
                             @change="shipping_phone = delivery_to_phone = $event.target.value;changeDeliveryData()">
                    </div>
                  </div>

                  <div class="col-xl-12" v-if="Object.keys(customer_info).length == 0">
                    <div class="form-group">
                      <label for="delivery_to_email">{{ config_labels.checkout_info.lbl_email ?? 'Email' }}</label>
                      <input type="text" class="form-control" name="ship_to_phone" id="delivery_to_email"
                             autocomplete="shipping address-line1"
                             v-bind:value="delivery_to_email"
                             @change="delivery_to_email = shipping_email = $event.target.value;changeShippingData();">
                    </div>
                  </div>

                  <div class="col-xl-12" v-show="Object.keys(customer_info).length == 0">
                    <div class="form-group">
                      <label for="delivery_country">{{ config_labels.checkout_info.lbl_country }}</label>
                      <select class="form-control" name="delivery_country" id="delivery_country"
                              v-bind:value="delivery_country || 'US'"
                              @change="delivery_country = $event.target.value;changeDeliveryData()">
                        <option disabled value="">Select One</option>
                        <option v-for="country in rentmy_countries" :key="country.id" :value=country.code>
                          {{ country.name }}
                        </option>
                      </select>
                    </div>
                  </div>
                  <div class="col-xl-12" v-show="Object.keys(customer_info).length == 0">
                    <div class="form-group">
                      <label for="delivery_address1">{{ config_labels.checkout_info.lbl_shipping_address_line_1 }}</label>
                      <input type="text" class="form-control" name="delivery_address1" id="delivery_address1"
                             autocomplete="shipping address-line1"
                             v-bind:value="delivery_address1"
                             @change="delivery_address1 = $event.target.value;changeDeliveryData()">
                    </div>
                  </div>
                  <div class="col-xl-12" v-show="Object.keys(customer_info).length == 0">
                    <div class="form-group">
                      <label for="delivery_address2">{{ config_labels.checkout_info.lbl_shipping_address_line_2 }}</label>
                      <input type="text" class="form-control" name="delivery_address2" id="delivery_address2"
                             autocomplete="shipping  address-line2"
                             v-bind:value="delivery_address2"
                             @change="delivery_address2 = $event.target.value;changeDeliveryData()">
                    </div>
                  </div>
                  <div class="col-xl-6" v-show="Object.keys(customer_info).length == 0">
                    <div class="form-group">
                      <label for="delivery_city">{{ config_labels.checkout_info.lbl_shipping_city }}</label>
                      <input type="text" class="form-control" name="delivery_city" id="delivery_city"
                             autocomplete="shipping address-level2"
                             v-bind:value="delivery_city"
                             @change="delivery_city = $event.target.value;changeDeliveryData()">
                    </div>
                  </div>
                  <div class="col-xl-6" v-show="Object.keys(customer_info).length == 0">
                    <div class="form-group">
                      <label for="delivery_zipcode">{{ config_labels.checkout_info.lbl_shipping_zipcode }}</label>
                      <input type="text" class="form-control" name="delivery_zipcode" id="delivery_zipcode"
                             autocomplete="shipping postal-code"
                             v-bind:value="delivery_zipcode"
                             @change="delivery_zipcode = $event.target.value;changeDeliveryData()">
                    </div>
                  </div>
                  <div class="col-xl-6" v-show="Object.keys(customer_info).length == 0">
                    <div class="form-group">
                      <label for="delivery_state">{{ config_labels.checkout_info.lbl_state }}</label>
                      <input type="text" class="form-control" name="delivery_state" id="delivery_state"
                             autocomplete="billing region"
                             v-bind:value="delivery_state"
                             @change="delivery_state = $event.target.value;changeDeliveryData()">
                    </div>
                  </div>
                </div>
                <div class="col-xl-6"></div>


                <div class="card-body p-0 fulfilment-body" v-if="Object.keys(customer_info).length != 0">
                  <!--                        <h5 style="padding-left: 15px;">Delivery Address</h5>-->
                  <div class="custom-control custom-radio" style="padding: 0px;">
                    <label class="m-radio col-md-12" v-for="(address, index) in delivery_address" :key="index">
                      <input name="delivery_loc" type="radio" :checked="address.id==billing_address_id"
                             v-on:click="selectDeliveryAddress(address.id)" class="ng-valid ng-dirty ng-touched">
                      {{ address.full_address }}</label>
                    <label class="m-radio col-md-12">
                      <input name="delivery_loc" type="radio" v-on:click="createNewDeliveryAddress"
                             class="ng-valid ng-dirty ng-touched"> Create New </label>

                  </div>
                  <div class="create-new-address row" v-show="ia_create_new_delivery_address">
                    <div class="col-xl-12">
                      <div class="form-group">
                        <label for="country">{{ config_labels.checkout_info.lbl_country }}*</label>
                        <select class="form-control" name="country" v-model="new_delivery_address.country"
                                autocomplete="billing country">
                          <option disabled value="">Select One</option>
                          <option v-for="country in rentmy_countries" :key="country.id" :value="country.code">
                            {{ country.name }}
                          </option>
                        </select>
                      </div>
                    </div>
                    <div class="col-xl-6">
                      <div class="form-group">
                        <label for="address_line1">{{ config_labels.checkout_info.lbl_address_line_1 }}*</label>
                        <input type="text" class="form-control" name="address_line1"
                               v-model="new_delivery_address.address_line1" autocomplete="billing address-line1">
                      </div>
                    </div>
                    <div class="col-xl-6">
                      <div class="form-group">
                        <label for="address_line2">{{ config_labels.checkout_info.lbl_address_line_2 }}</label>
                        <input type="text" class="form-control" name="address_line2"
                               v-model="new_delivery_address.address_line2" autocomplete="billing address-line2">
                      </div>
                    </div>
                    <div class="col-xl-6">
                      <div class="form-group">
                        <label for="city">{{ config_labels.checkout_info.lbl_city }}*</label>
                        <input type="text" class="form-control" name="city" id="new_delivery_city"
                               v-model="new_delivery_address.city" autocomplete="billing address-level2"
                        >
                      </div>
                    </div>
                    <div class="col-xl-6">
                      <div class="form-group">
                        <label for="zipcode">{{ config_labels.checkout_info.lbl_zipcode }}*</label>
                        <input type="text" class="form-control" id="new_delvery_zipcode" name="zipcode"
                               v-model="new_delivery_address.zipcode" autocomplete="billing postal-code">
                      </div>
                    </div>
                    <div class="col-xl-6">
                      <div class="form-group">
                        <label for="state">{{ config_labels.checkout_info.lbl_state }}*</label>
                        <input type="text" class="form-control" id="new_delivery_state" name="state"
                               v-model="new_delivery_address.state" autocomplete="billing region"
                        >
                      </div>
                    </div>

                    <div class="col-md-12 col-xs-12">
                      <button class="btn theme-btn bg-secondary text-light" v-on:click="addNewDeliveryAddress"
                              type="button">Add
                      </button>
                      <button class="btn bg-secondary text-light" v-on:click="ia_create_new_delivery_address=false"
                              type="button">Cancel
                      </button>
                    </div>
                  </div>
                </div>


                <div class="col-xl-12">
                  <div class="form-group pt-3 mb-0">
                    <button type="button" class="btn btn-sm placeorder-btn float-right" v-on:click="getDeliveryFee"
                            :disabled="loading" v-if="!this.rm_delivery_settings.charge_by_zone">
                      <i v-if="loading" class="fa fa-spinner fa-spin"></i>
                      {{ config_labels.checkout_info.btn_get_delivery_cost }}
                    </button>
                  </div>
                </div>
                <div class="col-xl-12 mb-2"
                     v-if="(rm_delivery_settings.charge_by_zone && zone_list.length!=0) && isDeliveryAddress()">
                  <div v-for="(zone,index) in zone_list" :key="index">
                    <label class="radio-container radiolist-container" v-if="zone_list.length==1">
                      <input type='radio' checked>
                      <span class='rentmy-radio-text'> {{ zone.name }} </span>
                      <span class="rentmy-radio-price">{{ currency_symbol }}{{ priceFormat(zone.charge) }}</span>
                      <span class="checkmark"></span>
                    </label>
                    <label class="radio-container radiolist-container" v-else>
                      <input type='radio' v-model='delivery_zone' :value='JSON.stringify(zone)'>
                      <span class='rentmy-radio-text'> {{ zone.name }} </span>
                      <span class="rentmy-radio-price">{{ currency_symbol }}{{ priceFormat(zone.charge) }}</span>
                      <span class="checkmark"></span>
                    </label>
                  </div>
                </div>
                <div class="col-xl-12" v-if="!rm_delivery_settings.charge_by_zone && delivery_fee && !max_distance">
                  <div class="form-group">
                    <label for="delivery_charge">Delivery Charge {{ currency_symbol }}{{ priceFormat(delivery_fee) }}</label>
                  </div>
                </div>
                <div class="col-xl-12" v-if="delivery_error != ''">
                  <span class="text-danger">{{ delivery_error }}</span>
                </div>
              </div>

            </div>
          </div>
        </div>
        <multi-store-delivery v-else @charge="multiStoreCharged" :delivery_data="getOrderDeliveryData"></multi-store-delivery>
      </div>
    </div>
  </div>
</template>

<script>
module.exports = {
  data: function () {
    return {
      title: 'Fulfilment',
      rentmy_countries: rm_countries,
      rentmy_locations: rm_locations.data || null,
      rm_delivery_settings: {},
      currency_symbol: rentmy_config_data_preloaded.currency_format.symbol,
      smethods: {pickup: 1, delivery: 2, internal: 3, fedex: 4, usps: 4, free: 5, standard: 6, flat: 7},
      errors: [],
      fftype: '',
      rm_instore_loc: '',
      ship_to_phone: '',
      ship_to_email: '',
      ship_to_name: '',
      shipping_name: '',
      shipping_phone: '',
      shipping_email: '',
      delivery_to_phone: '',
      delivery_to_email: '',
      delivery_to_name: '',
      shipping_country: rm_storeCountry != undefined ? rm_storeCountry : 'US',
      shipping_address1: '',
      shipping_address2: '',
      shipping_city: '',
      shipping_zipcode: '',
      shipping_state: '',
      delivery_country: rm_storeCountry != undefined ? rm_storeCountry : 'US',
      delivery_address1: '',
      delivery_address2: '',
      delivery_city: '',
      delivery_zipcode: '',
      delivery_state: '',
      loading: false,
      delivery_location: {},
      delivery_fee: null,
      shipping_methods: '',
      shipping_method: '',
      shipping_error: '',
      instance_delivery: '',
      instance_shipping: '',
      input_delivery: '',
      input_shipping: '',
      input: '',
      zone_list: [],
      delivery_zone: {},
      config_labels: config_labels,
      customer_info: customer_info,
      store_config: store_config,
      shipping_address: billing_address,
      delivery_address: billing_address,
      billing_address_id: '',
      ia_create_new_shipping_address: false,
      new_shipping_address: {
        country: rm_storeCountry != undefined ? rm_storeCountry : 'US',
        address_line1: "",
        address_line2: "",
        city: "",
        zipcode: "",
        state: "",
      },
      ia_create_new_delivery_address: false,
      new_delivery_address: {
        country: rm_storeCountry != undefined ? rm_storeCountry : 'US',
        address_line1: "",
        address_line2: "",
        city: "",
        zipcode: "",
        state: "",
      },
      is_shipping_with_above: false,
      is_delivery_with_above: false,
      delivery_error: '',
      is_fullfilment: !(store_config.checkout.hide_fulfillment ?? false),
      is_shipping_field: false,
      is_delivery_field: false,
      base_url: rm_baseUrl,
      free_shipping: false,
      delivery_flow: '',
      delivery_multi_store: {},
      max_distance: false
    }
  },
  components: {
    'multi-store-delivery': window.httpVueLoader(rentmy_plugin_base_url+'/assets/js/components/MultiStoreDelivery.vue'),
  },
  methods: {
    selectFullfilmentMethod: function (value) {
      this.fftype = value;

      if (this.fftype == "shipping") {
        this.is_shipping_with_above = true;
        this.is_shipping_field = false;
      } else if (this.fftype == "delivery") {
        this.is_delivery_with_above = true;
        this.is_delivery_field = false;
      } else if (this.fftype == "instore_pickup") {

        if (this.rentmy_locations.length == 1) {
          this.updateCart({
            shipping_cost: 0,
            shipping_method: 1,
            tax: 0,
            tax_id: null,
          });
        }
      }

    },
    fullfillmentValidation: function (value) {

      this.errors = [];
      if (!this.is_fullfilment) {
        return;
      }
      if (this.fftype == "shipping")
        this.shippingValidation();
      else if (this.fftype == "delivery")
        this.deliveryValidation();

      if (this.errors.length === 0) {
        this.broadcastFullfillmentValue();
      }
    },
    shippingValidation: function (value) {

      this.errors = [];
      if (!this.shipping_country) {
        this.errors.push('Country is required.');
      }
      if (!this.shipping_address1) {
        this.errors.push('Address Line 1 is required.');
      }
      if (!this.shipping_city) {
        this.errors.push('City is required.');
      }
      if (!this.shipping_state) {
        this.errors.push('State is required.');
      }
      if (!this.shipping_zipcode) {
        this.errors.push('Zipcode is required.');
      }
    },
    deliveryValidation: function (value) {

      this.errors = [];
      if (!this.delivery_country) {
        this.errors.push('Country is required.');
      }
      if (!this.delivery_address1) {
        this.errors.push('Address Line 1 is required.');
      }
      if (!this.delivery_city) {
        this.errors.push('City is required.');
      }
      if (!this.delivery_state) {
        this.errors.push('State is required.');
      }
      if (!this.delivery_zipcode) {
        this.errors.push('Zipcode is required.');
      }
    },
    isDeliveryAddress: function () {
      if (!this.delivery_country || !this.delivery_address1 || !this.delivery_city || !this.delivery_state || !this.delivery_zipcode) {
        return false;
      }
      return true;

    },
    broadcastFullfillmentValue: function () {
      this.$emit('fullfillment-validation', {
        rm_instore_loc: this.rm_instore_loc,
        shipping_country: this.shipping_country,
        shipping_address1: this.shipping_address1,
        shipping_address2: this.shipping_address2,
        shipping_city: this.shipping_city,
        shipping_zipcode: this.shipping_zipcode,
        shipping_state: this.shipping_state,
        delivery_country: this.delivery_country,
        delivery_address1: this.delivery_address1,
        delivery_address2: this.delivery_address2,
        delivery_city: this.delivery_city,
        delivery_zipcode: this.delivery_zipcode,
        delivery_state: this.delivery_state,
        errors: this.errors
      });
    },

    validate: function () {
      this.errors = [];
      if (!this.is_fullfilment) {
        return !this.errors.length;
      }
      if (!this.fftype)
        this.errors.push("Please select a delivery method.");
      else if (this.fftype == "instore_pickup") {
        if (!this.rm_instore_loc)
          this.errors.push('Please select a pickup location.');
      } else if (this.fftype == "shipping") {
        this.shippingValidation();
        if (this.free_shipping) {
          return true;
        }
        if (!this.shipping_method)
          this.errors.push('Please select a shipping method.');
      } else if (this.fftype == "delivery") {
        this.deliveryValidation();
        if (this.rm_delivery_settings.charge_by_zone && !this.delivery_zone)
          this.errors.push('Please select a delivery zone.');
        else if (this.delivery_fee === null)
          this.errors.push('Please select a delivery charge');

        else if (this.delivery_error)
          this.errors.push('Delivery not possible.');
      }

      return !this.errors.length;
    },

    dateFormat: function (date) {
      let d = new Date(date);
      return d.getDate() + "-" + d.getMonth() + "-" + d.getFullYear() + " " + d.toLocaleTimeString();
    },
    priceFormat: function (amount, withSymbol = false) {
      // return parseFloat(priceVal).toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,');
      amount = parseFloat(amount);
      if (isNaN(amount)) {
        return;
      }
      amount = new Number(amount);
      var myObj = {style: 'currency', currency: 'USD'};
      let currency_config = this.store_config.currency_format;
      let symbol = currency_config.symbol ? currency_config.symbol : '$';

      let locale = currency_config.locale;
      let amountStr = amount;
      if (locale) {
        amountStr = Number(amount).toLocaleString(locale, myObj);
        amountStr = amountStr.replace('US', '');
        amountStr = amountStr.replace('$', '');
      }
      if (withSymbol) {
        amountStr = '<span class="pre">' + symbol + amountStr + '<span>';

        if (currency_config.post) {
          amountStr = '<span class="post">' + amountStr + symbol + '<span>';
        }
      }

      return amountStr;
    },
    getShippingMethod: function () {
      this.shippingValidation();
      if (this.errors.length) return;
      this.loading = true;
      // let shipping_name = this.ship_to_name.split(' ');
      let sp_first_name = this.shipping_name.split(' ')[0];
      let sp_last_name = this.shipping_name.split(' ').filter(function (item) {
        return item != sp_first_name
      });

      let address = {
        shipping_first_name: sp_first_name,
        shipping_last_name: sp_last_name.join(" "),
        shipping_mobile: this.ship_to_phone,
        shipping_address1: this.shipping_address1,
        shipping_address2: this.shipping_address2,
        shipping_city: this.shipping_city,
        shipping_state: this.shipping_state,
        shipping_zipcode: this.shipping_zipcode,
        shipping_country: this.shipping_country
      };
      let ajaxdata = {
        action: 'rentmy_options',
        action_type: 'get_shipping_methods_by_kn',
        data: address,
      };

      var vfapp = this;
      vfapp.shipping_error = '';
      jQuery.post(rentmy_ajax_object.ajaxurl, ajaxdata, function (response) {
        //console.log(response);
        if (response.status == 'NOK') {
          vfapp.shipping_error = '<span style="color:red">' + response.result.error + '</span>';
          vfapp.loading = false;
          return;
        }
        vfapp.shipping_methods = response.result;
        vfapp.loading = false;
      });

    },

    getDeliveryFee: function () {
      this.deliveryValidation();
      if (this.errors.length) return;
      this.loading = true;
      let sp_first_name = this.shipping_name.split(' ')[0];
      let sp_last_name = this.shipping_name.split(' ').filter(function (item) {
        return item != sp_first_name
      });
      let ajaxdata = {
        action: 'rentmy_options',
        action_type: 'get_delivery_cost',
        data: {
          shipping_first_name: sp_first_name,
          shipping_last_name: sp_last_name.join(" "),
          shipping_mobile: this.delivery_to_phone,
          shipping_address1: this.delivery_address1,
          shipping_address2: this.delivery_address2,
          shipping_city: this.delivery_city,
          shipping_state: this.delivery_state,
          shipping_zipcode: this.delivery_zipcode,
          shipping_country: this.delivery_country
        },
      };

      var vfapp = this;
      vfapp.delivery_error = "";
      jQuery.post(rentmy_ajax_object.ajaxurl, ajaxdata, function (response) {
        if (response.error) {
          vfapp.delivery_error = response.error;
          vfapp.loading = false;
          return;
        }
        if (vfapp.rm_delivery_settings.charge_by_zone) {
          vfapp.zone_list = response.location;
        } else {

          vfapp.delivery_fee = response.location[0].charge;
          vfapp.max_distance = response?.location[0]?.max_distance??false;

          if (vfapp.max_distance){
            vfapp.delivery_error = "Your address is outside of our delivery area. Please contact us to make other arrangements.";
            vfapp.loading = false;
            return;
          }
          vfapp.delivery_location = response.location[0];
          vfapp.updateCart({
            shipping_cost: vfapp.delivery_fee,
            shipping_method: 2,
            address: {
              shipping_city: vfapp.delivery_city,
              shipping_country: vfapp.delivery_country,
              shipping_state: vfapp.delivery_state,
              shipping_zipcode: vfapp.delivery_zipcode,
              billing_city: vfapp.$parent.billing_info.city ?? '',
              billing_country: vfapp.$parent.billing_info.country ?? '',
              billing_state: vfapp.$parent.billing_info.state ?? '',
              billing_zipcode: vfapp.$parent.billing_info.zipcode ?? '',
            }
          });
        }
        vfapp.loading = false;
      });
    },

    updateCart: function (data) {

      if ((this.fftype != 'instore_pickup') && (data.address.shipping_state == '' || data.address.shipping_country == '' || data.address.shipping_city == '' || data.address.shipping_zipcode == '')) {
        return;
      }
      let ajaxdata = {
        action: 'rentmy_options',
        action_type: 'add_shipping_to_cart',
        data: data
      };
      var vfapp = this;
      this.loading = true;
      //console.log(ajaxdata);
      jQuery.post(rentmy_ajax_object.ajaxurl, ajaxdata, function (response) {
        //console.log(response);
        if (response.status == "OK") {
          if (response.result && response.result.data) {
            if (vfapp.fftype == 'delivery') {
              vfapp.delivery_fee = ajaxdata.data.shipping_cost;
            }
            vfapp.$emit('update-cart', response.result.data);
            serveBus.$emit('loadCartElement', response.result.data);
            serveBus.$emit('update-cart');
          } else {
            vfapp.$emit('update-cart-forced');
          }
        }
        vfapp.loading = false;
      });
    },

    // algoliaDelivery: function() {
    //     let vm = this;
    //     let country = vm.delivery_country??"US";
    //     this.input_delivery = document.getElementById('delivery_city');

    //     this.instance_delivery = places({
    //         container: this.input_delivery,
    //         appId: 'plIF9PULAHKJ',
    //         apiKey: '33382f2e6281756a1ceb6302fbc6bcbe',
    //         type: "city",
    //         countries: [country],
    //         templates: {
    //             value: function (suggestion) {
    //             return suggestion.name;
    //             }
    //         }
    //     });

    //     this.instance_delivery.on('change', e => {
    //         jQuery("#delivery_state").val( e.suggestion.administrative );
    //         jQuery("#delivery_zipcode").val( e.suggestion.postcode );
    //         this.delivery_state = e.suggestion.administrative;
    //         this.delivery_zipcode = e.suggestion.postcode;
    //         //this.city = e.suggestion.name;
    //         // this.address_line1 = e.suggestion.name;
    //         //  this.address_line2 = e.suggestion.name;
    //         //console.log(e.suggestion);
    //         //console.log(e.suggestion.name)
    //     });
    // },
    // algoliaNewDelivery: function() {
    //     let vm = this;
    //     let country = vm.new_delivery_address.country??"US";
    //     this.input_delivery = document.getElementById('new_delivery_city');

    //     this.instance_delivery = places({
    //         container: this.input_delivery,
    //         appId: 'plIF9PULAHKJ',
    //         apiKey: '33382f2e6281756a1ceb6302fbc6bcbe',
    //         type: "city",
    //         countries: [country],
    //         templates: {
    //             value: function (suggestion) {
    //             return suggestion.name;
    //             }
    //         }
    //     });

    //     this.instance_delivery.on('change', e => {
    //         jQuery("#new_delivery_state").val( e.suggestion.administrative );
    //         jQuery("#new_delivery_zipcode").val( e.suggestion.postcode );
    //         this.new_delivery_address.state = e.suggestion.administrative;
    //         this.new_delivery_address.zipcode = e.suggestion.postcode;
    //         //this.city = e.suggestion.name;
    //         // this.address_line1 = e.suggestion.name;
    //         //  this.address_line2 = e.suggestion.name;
    //         //console.log(e.suggestion);
    //         //console.log(e.suggestion.name)
    //     });
    // },

    // algoliaShipping: function() {
    //     let vm = this;
    //     let country = vm.shipping_country??"US";

    //     this.input_shipping = document.getElementById('shipping_city');
    //     this.instance_shipping = places({
    //         container: this.input_shipping,
    //         appId: 'plIF9PULAHKJ',
    //         apiKey: '33382f2e6281756a1ceb6302fbc6bcbe',
    //         type: "city",
    //         countries: [country],
    //         templates: {
    //             value: function (suggestion) {
    //                 return suggestion.name;
    //             }
    //         }
    //     });

    //     this.instance_shipping.on('change', e => {
    //         jQuery("#shipping_state").val( e.suggestion.administrative );
    //         jQuery("#shipping_zipcode").val( e.suggestion.postcode );
    //         this.shipping_state = e.suggestion.administrative;
    //         this.shipping_zipcode = e.suggestion.postcode;
    //         //this.city = e.suggestion.name;
    //         // this.address_line1 = e.suggestion.name;
    //         //  this.address_line2 = e.suggestion.name;
    //         //console.log(e.suggestion);
    //         //console.log(e.suggestion.name)
    //     });
    // },


    // algoliaNewShipping: function() {
    //     let vm = this;
    //     let country = vm.new_shipping_address.country??"US";

    //     this.input_shipping = document.getElementById('new_shipping_city');
    //     console.log(this.input_shipping);

    //     this.instance_shipping = places({
    //         container: this.input_shipping,
    //         appId: 'plIF9PULAHKJ',
    //         apiKey: '33382f2e6281756a1ceb6302fbc6bcbe',
    //         type: "city",
    //         countries: [country],
    //         templates: {
    //             value: function (suggestion) {
    //                 return suggestion.name;
    //             }
    //         }
    //     });

    //     this.instance_shipping.on('change', e => {
    //         jQuery("#new_shipping_state").val( e.suggestion.administrative );
    //         jQuery("#new_shipping_zipcode").val( e.suggestion.postcode );
    //         this.new_shipping_address.state = e.suggestion.administrative;
    //         this.new_shipping_address.zipcode = e.suggestion.postcode;
    //         //this.city = e.suggestion.name;
    //         // this.address_line1 = e.suggestion.name;
    //         //  this.address_line2 = e.suggestion.name;
    //         //console.log(e.suggestion);
    //         //console.log(e.suggestion.name)
    //     });
    // },


    getOrderDeliveryData: function () {
      let delivery = {};

      if (!this.is_fullfilment) {
        if (this.rentmy_locations.length > 0) {
          let location = this.rentmy_locations[0];
          delivery.id = location.id;
          delivery.name = location.name;
          delivery.location = location.location;
          delivery.type = "instore";
          delivery.method = 1;
          return delivery;
        }
      }
      if (this.fftype == "pickup") {
        let location = this.rentmy_locations.find(l => l.id == lid);
        if (location) {
          delivery.id = location.id;
          delivery.name = location.name;
          delivery.location = location.location;
          delivery.type = "instore";
          delivery.method = 1;
        }
      } else if (this.fftype == "shipping" && this.shipping_method) {
        let sm = JSON.parse(this.shipping_method);
        Object.assign(delivery, sm.method);
        if (sm.index == "standard") {
          delivery.method = 6;
        } else if (sm.index == "flat") {
          delivery.method = 7;
        } else {
          delivery.method = 4;
        }
      } else if (this.fftype == "delivery") {
        if (this.rm_delivery_settings.charge_by_zone && this.delivery_zone) {
          Object.assign(delivery, JSON.parse(this.delivery_zone));
          delivery.method = 2;
        } else if (this.delivery_location) {
          Object.assign(delivery, this.delivery_location);
          delivery.method = 2;
        }
      }
      return delivery;
    },
    createNewShippingAddress: function () {
      this.ia_create_new_shipping_address = true;
      //   this.algoliaNewShipping();
    },
    selectShippingAddress: function (id) {
      let vm = this;

      let addresses = vm.shipping_address;
      let selected_address = addresses.filter(function (address) {
        return address.id == id;
      });
      vm.shipping_country = selected_address[0].country;
      vm.shipping_zipcode = selected_address[0].zipcode;
      vm.shipping_city = selected_address[0].city;
      vm.shipping_state = selected_address[0].state;
      vm.shipping_address1 = selected_address[0].address_line1;
      vm.shipping_address2 = selected_address[0].address_line2;

    },
    addNewShippingAddress() {
      let vm = this;
      let ajaxdata = new FormData();
      ajaxdata.append("action", "rentmy_options");
      ajaxdata.append("action_type", "add_new_address");
      ajaxdata.append("country", vm.new_shipping_address.country);
      ajaxdata.append("state", vm.new_shipping_address.state);
      ajaxdata.append("address_line1", vm.new_shipping_address.address_line1);
      ajaxdata.append("address_line2", vm.new_shipping_address.address_line2);
      ajaxdata.append("city", vm.new_shipping_address.city);
      ajaxdata.append("zipcode", vm.new_shipping_address.zipcode);

      axios.post(rentmy_ajax_object.ajaxurl, ajaxdata, {headers: {contentType: 'application/json'}}).then(function (response) {
        if (response.data.status == 'OK') {
          vm.shipping_address.push(response.data.result.data[0]);
          vm.ia_create_new_shipping_address = false;
        }
      });
    },

    createNewDeliveryAddress: function () {
      this.ia_create_new_delivery_address = true;
      //   this.algoliaNewDelivery();
    },
    selectDeliveryAddress: function (id) {
      let vm = this;

      let addresses = vm.shipping_address;
      let selected_address = addresses.filter(function (address) {
        return address.id == id;
      });
      vm.delivery_country = selected_address[0].country;
      vm.delivery_zipcode = selected_address[0].zipcode;
      vm.delivery_city = selected_address[0].city;
      vm.delivery_state = selected_address[0].state;
      vm.delivery_address1 = selected_address[0].address_line1;
      vm.delivery_address2 = selected_address[0].address_line2;

    },
    addNewDeliveryAddress() {
      let vm = this;
      let ajaxdata = new FormData();
      ajaxdata.append("action", "rentmy_options");
      ajaxdata.append("action_type", "add_new_address");
      ajaxdata.append("country", vm.new_delivery_address.country);
      ajaxdata.append("state", vm.new_delivery_address.state);
      ajaxdata.append("address_line1", vm.new_delivery_address.address_line1);
      ajaxdata.append("address_line2", vm.new_delivery_address.address_line2);
      ajaxdata.append("city", vm.new_delivery_address.city);
      ajaxdata.append("zipcode", vm.new_delivery_address.zipcode);

      axios.post(rentmy_ajax_object.ajaxurl, ajaxdata, {headers: {contentType: 'application/json'}}).then(function (response) {
        if (response.data.status == 'OK') {
          vm.delivery_address.push(response.data.result.data[0]);
          vm.ia_create_new_delivery_address = false;
        }
      });
    },
    shippingCheck: function (event) {
      let vm = this;
      let is_checked = event.target.checked;
      vm.is_shipping_field = true;
      if (is_checked) {
        vm.is_shipping_field = false;
        let shipping_data = {
          country: this.$parent.billing_info.country,
          ship_to_name: (this.$parent.billing_info.first_name == undefined ? '' : this.$parent.billing_info.first_name) + ' ' + (this.$parent.billing_info.last_name == undefined ? '' : this.$parent.billing_info.last_name),
          ship_to_phone: this.$parent.billing_info.mobile,
          zipcode: this.$parent.billing_info.zipcode,
          city: this.$parent.billing_info.city,
          state: this.$parent.billing_info.state,
          address1: this.$parent.billing_info.address_line1,
          address2: this.$parent.billing_info.address_line2,
        }
        localStorage.setItem('is_shipping_checked', true);
        vm.is_shipping_with_above = true;
        localStorage.setItem('shipping_data', JSON.stringify(shipping_data));
        vm.setShippingData();
      } else {
        localStorage.setItem('is_shipping_checked', false);
        vm.is_shipping_with_above = false;
      }

    },
    setShippingData: function () {
      let vm = this;
      let shipping_data = JSON.parse(localStorage.getItem('shipping_data'));
      if (shipping_data != null) {
        vm.ship_to_name = shipping_data.ship_to_name;
        vm.ship_to_phone = shipping_data.ship_to_phone;
        vm.shipping_country = shipping_data.country;
        vm.shipping_zipcode = shipping_data.zipcode;
        vm.shipping_city = shipping_data.city;
        vm.shipping_state = shipping_data.state;
        vm.shipping_address1 = shipping_data.address1;
        vm.shipping_address2 = shipping_data.address2;

        vm.shipping_name = vm.ship_to_name;
        vm.shipping_phone = vm.ship_to_phone;
        vm.shipping_email = vm.ship_to_email;
      } else {

        let shipping_data = {
          ship_to_name: (this.$parent.billing_info.first_name == undefined ? '' : this.$parent.billing_info.first_name) + ' ' + (this.$parent.billing_info.last_name == undefined ? '' : this.$parent.billing_info.last_name),
          ship_to_phone: this.$parent.billing_info.mobile == undefined ? '' : this.$parent.billing_info.mobile,
          country: this.$parent.billing_info.country == undefined ? 'US' : this.$parent.billing_info.country,
          zipcode: this.$parent.billing_info.zipcode == undefined ? '' : this.$parent.billing_info.zipcode,
          city: this.$parent.billing_info.city == undefined ? '' : this.$parent.billing_info.city,
          state: this.$parent.billing_info.state == undefined ? '' : this.$parent.billing_info.state,
          address1: this.$parent.billing_info.address_line1 == undefined ? '' : this.$parent.billing_info.address_line1,
          address2: this.$parent.billing_info.address_line2 == undefined ? '' : this.$parent.billing_info.address_line2,
        }

        localStorage.setItem('shipping_data', JSON.stringify(shipping_data));
        vm.setShippingData();
      }

    },
    changeShippingData: function () {
      let is_checked = jQuery('#shipping_checkbox').is(':checked');
      let vm = this;
      if (!is_checked) {
        let shipping_data = {
          ship_to_name: this.ship_to_name,
          ship_to_phone: this.ship_to_phone,
          country: this.shipping_country,
          zipcode: this.shipping_zipcode,
          city: this.shipping_city,
          state: this.shipping_state,
          address1: this.shipping_address1,
          address2: this.shipping_address2,
        }

        vm.shipping_name = this.ship_to_name;
        vm.shipping_phone = this.ship_to_phone;
        vm.shipping_email = this.ship_to_email;
        localStorage.setItem('shipping_data', JSON.stringify(shipping_data));
      }
    },
    getShippingDataInit: function () {

      let is_shipping_checked = localStorage.getItem('is_shipping_checked');

      if (is_shipping_checked == null) {
        jQuery('#shipping_checkbox').prop("checked", true);
        localStorage.setItem('is_shipping_checked', true);
        this.setShippingData();
      }
    },

    deliveryCheck: function (event) {
      let vm = this;
      let is_checked = event.target.checked;
      vm.is_delivery_field = true;
      if (is_checked) {
        vm.is_delivery_field = false;
        let delivery_data = {
          delivery_to_name: (this.$parent.billing_info.first_name == undefined ? '' : this.$parent.billing_info.first_name) + ' ' + (this.$parent.billing_info.last_name == undefined ? '' : this.$parent.billing_info.last_name),
          delivery_to_phone: this.$parent.billing_info.mobile,
          country: this.$parent.billing_info.country,
          zipcode: this.$parent.billing_info.zipcode,
          city: this.$parent.billing_info.city,
          state: this.$parent.billing_info.state,
          address1: this.$parent.billing_info.address_line1,
          address2: this.$parent.billing_info.address_line2,
        }
        localStorage.setItem('is_delivery_checked', true);
        vm.is_delivery_with_above = true;

        localStorage.setItem('delivery_data', JSON.stringify(delivery_data));
        vm.setDeliveryData();
      } else {
        localStorage.setItem('is_delivery_checked', false);
        vm.is_delivery_with_above = false;
      }

    },
    setDeliveryData: function () {
      let vm = this;
      let shipping_data = JSON.parse(localStorage.getItem('delivery_data'));

      if (shipping_data != null) {
        vm.delivery_to_name = shipping_data.delivery_to_name;
        vm.delivery_to_phone = shipping_data.delivery_to_phone;
        vm.delivery_to_email = shipping_data.delivery_to_email;
        vm.delivery_country = shipping_data.country;
        vm.delivery_zipcode = shipping_data.zipcode;
        vm.delivery_city = shipping_data.city;
        vm.delivery_state = shipping_data.state;
        vm.delivery_address1 = shipping_data.address1;
        vm.delivery_address2 = shipping_data.address2;

        vm.shipping_name = vm.delivery_to_name;
        vm.shipping_phone = vm.delivery_to_phone;
        vm.shipping_email = vm.delivery_to_email;
      } else {
        let shipping_data = {
          delivery_to_name: (this.$parent.billing_info.first_name == undefined ? '' : this.$parent.billing_info.first_name) + ' ' + (this.$parent.billing_info.last_name == undefined ? '' : this.$parent.billing_info.last_name),
          delivery_to_phone: this.$parent.billing_info.mobile,
          country: this.$parent.billing_info.country == undefined ? 'US' : this.$parent.billing_info.country,
          zipcode: this.$parent.billing_info.zipcode == undefined ? '' : this.$parent.billing_info.zipcode,
          city: this.$parent.billing_info.city == undefined ? '' : this.$parent.billing_info.city,
          state: this.$parent.billing_info.state == undefined ? '' : this.$parent.billing_info.state,
          address1: this.$parent.billing_info.address_line1 == undefined ? '' : this.$parent.billing_info.address_line1,
          address2: this.$parent.billing_info.address_line2 == undefined ? '' : this.$parent.billing_info.address_line2,
        }
        localStorage.setItem('delivery_data', JSON.stringify(shipping_data));
        vm.setDeliveryData();
      }

    },
    changeDeliveryData: function () {
      let is_checked = jQuery('#delivery_checkbox').is(':checked');
      let vm = this;
      if (!is_checked) {
        let shipping_data = {
          delivery_to_name: this.delivery_to_name,
          delivery_to_phone: this.delivery_to_phone,
          country: this.shipping_country,
          zipcode: this.shipping_zipcode,
          city: this.shipping_city,
          state: this.shipping_state,
          address1: this.shipping_address1,
          address2: this.shipping_address2,
        }
        vm.shipping_name = this.delivery_to_name;
        vm.shipping_phone = this.delivery_to_phone;
        vm.shipping_email = this.delivery_to_email;
        localStorage.setItem('delivery_data', JSON.stringify(shipping_data));
      }
      if (vm.rm_delivery_settings.charge_by_zone && this.zone_list.length == 1) {
        let billing_info = vm.$parent.billing_info;
        vm.delivery_zone = this.zone_list[0]
        this.updateCart({
          shipping_cost: this.zone_list[0].charge,
          shipping_method: 2,
          address: {
            shipping_city: vm.delivery_city,
            shipping_country: vm.delivery_country,
            shipping_state: vm.delivery_state,
            shipping_zipcode: vm.delivery_zipcode,
            billing_city: billing_info.city ?? '',
            billing_country: billing_info.country ?? '',
            billing_state: billing_info.state ?? '',
            billing_zipcode: billing_info.zipcode ?? '',
          }
        });
      }
    },
    getDeliveryDataInit: function () {

      let is_delivery_checked = localStorage.getItem('is_delivery_checked');
      if (is_delivery_checked == null) {
        jQuery('#delivery_checkbox').prop("checked", true);
        localStorage.setItem('is_delivery_checked', true);
        this.setDeliveryData();
      }
    },

    checkFreeShipping: function () {
      let ajaxdata = {
        action: 'rentmy_options',
        action_type: 'free_shipping'
      };

      var vfapp = this;
      jQuery.post(rentmy_ajax_object.ajaxurl, ajaxdata, function (response) {
        vfapp.free_shipping = response.data ? true : false

      });
    },
    multiStoreCharged: function (data){
      let vfapp = this;
      vfapp.delivery_multi_store = data.delivery_multi_store;
      vfapp.delivery_city = data.shipping_city;
      vfapp.delivery_country = data.shipping_country;
      vfapp.delivery_state = data.shipping_state;
      vfapp.delivery_zipcode = data.shipping_zipcode;
      vfapp.delivery_address1 = data.shipping_address1;
      vfapp.delivery_address2 = data.shipping_address2;
      vfapp.delivery_to_name = data.shipping_first_name +' '+data.shipping_last_name;
      vfapp.delivery_to_email = data.shipping_email;

      vfapp.updateCart({
        shipping_cost: data.delivery_charge,
        shipping_method: 3,
        address: {
          shipping_city: data.shipping_city,
          shipping_country: data.shipping_country,
          shipping_state: data.shipping_state,
          shipping_zipcode: data.shipping_zipcode,
          billing_city: vfapp.$parent.billing_info.city ?? '',
          billing_country: vfapp.$parent.billing_info.country ?? '',
          billing_state: vfapp.$parent.billing_info.state ?? '',
          billing_zipcode: vfapp.$parent.billing_info.zipcode ?? '',
        }
      });
    },
    resetFulfilmentData(){
      this.delivery_fee = null;
      this.shipping_method = '';
      this.delivery_zone = '';
    }

  },
  watch: {
    rm_instore_loc: function (lid) {
      let location = this.rentmy_locations.find(l => l.id == lid);
      if (location) {
        this.updateCart({
          shipping_cost: 0,
          shipping_method: 1,
          tax: 0,
          tax_id: null,
        });
      }
    },
    shipping_method: function (sm) {

      let vm = this;
      if (!sm)
        return;

      sm = JSON.parse(sm);

      this.updateCart({
        shipping_cost: sm.method.charge,
        shipping_method: this.smethods[sm.index] ? this.smethods[sm.index] : 4,
        address: {
          shipping_city: vm.shipping_city,
          shipping_country: vm.shipping_country,
          shipping_state: vm.shipping_state,
          shipping_zipcode: vm.shipping_zipcode,
          billing_city: vm.$parent.billing_info.city ?? '',
          billing_country: vm.$parent.billing_info.country ?? '',
          billing_state: vm.$parent.billing_info.state ?? '',
          billing_zipcode: vm.$parent.billing_info.zipcode ?? '',
        }
      });
    },
    delivery_zone: function (sm) {
      let vm = this;
      let zone = sm ? JSON.parse(sm) : '';
      let billing_info = vm.$parent.billing_info;
      this.updateCart({
        shipping_cost: zone.charge,
        shipping_method: 2,
        // tax: zone.tax,
        // tax_id: zone.tax_id,
        address: {
          shipping_city: vm.delivery_city,
          shipping_country: vm.delivery_country,
          shipping_state: vm.delivery_state,
          shipping_zipcode: vm.delivery_zipcode,
          billing_city: billing_info.city ?? '',
          billing_country: billing_info.country ?? '',
          billing_state: billing_info.state ?? '',
          billing_zipcode: billing_info.zipcode ?? '',
        }
      });
    },
    fftype: function (type) {
      let vm = this;
      vm.resetFulfilmentData();
      if (type == "instore_pickup") return;

      if (type == "delivery") {

        if (vm.rm_delivery_settings.charge_by_zone && this.zone_list.length == 1) {
          let billing_info = vm.$parent.billing_info;
          this.updateCart({
            shipping_cost: this.zone_list[0].charge,
            shipping_method: 2,
            // tax: zone.tax,
            // tax_id: zone.tax_id,
            address: {
              shipping_city: vm.delivery_city,
              shipping_country: vm.delivery_country,
              shipping_state: vm.delivery_state,
              shipping_zipcode: vm.delivery_zipcode,
              billing_city: billing_info.city ?? '',
              billing_country: billing_info.country ?? '',
              billing_state: billing_info.state ?? '',
              billing_zipcode: billing_info.zipcode ?? '',
            }
          });
        }
      }
      if(type=="shipping")
        this.shipping_methods = [];
      // if(type=="shipping")
      //     this.algoliaShipping();
      // else
      //     this.algoliaDelivery();
    }
  },
  created: function () {
    this.checkFreeShipping();
    this.$parent.$on('checkValidation', this.fullfillmentValidation);
    var vfapp = this;
    let ajaxdata = {
      action: 'rentmy_options',
      action_type: 'get_delivery_settings'
    }
    jQuery.post(rentmy_ajax_object.ajaxurl, ajaxdata, function (response) {
      //console.log(response);
      vfapp.rm_delivery_settings = response.delivery_settings
      if (vfapp.rm_delivery_settings.charge_by_zone)
        vfapp.zone_list = response.delivery_settings.location
      if (vfapp.rm_delivery_settings.instore_pickup) {
        vfapp.fftype = 'instore_pickup';
        if (vfapp.rentmy_locations.length == 1) {
          vfapp.rm_instore_loc = vfapp.rentmy_locations[0].id;
        }
      } else if (vfapp.rm_delivery_settings.shipping) {
        vfapp.fftype = 'shipping';
        vfapp.is_shipping_with_above = true;
      } else if (vfapp.rm_delivery_settings.delivery) {
        vfapp.fftype = 'delivery';
        vfapp.is_delivery_with_above = true;
      }
    });

  },
  mounted: function () {
    let vm = this;

    localStorage.removeItem('shipping_data');
    localStorage.removeItem('is_shipping_checked');
    localStorage.removeItem('delivery_data');
    localStorage.removeItem('is_delivery_checked');
    serveBus.$on('selectCustomerAddress', (data) => {
      vm.billing_address_id = data.id;
      vm.shipping_country = data.country;
      vm.shipping_address1 = data.address_line1;
      vm.shipping_address2 = data.address_line2;
      vm.shipping_city = data.city;
      vm.shipping_state = data.state;
      vm.shipping_zipcode = data.zipcode;
      vm.delivery_country = data.country;
      vm.delivery_address1 = data.address_line1;
      vm.delivery_address2 = data.address_line2;
      vm.delivery_city = data.city;
      vm.delivery_state = data.state;
      vm.delivery_zipcode = data.zipcode;

    });

    serveBus.$on('broadcastBillingValue', (data) => {

      vm.billing_address_id = data.id;
      if (vm.fftype == 'shipping' && !vm.is_shipping_with_above) {
        vm.delivery_to_name = (data.first_name == undefined ? '' : this.$parent.billing_info.first_name) + ' ' + (data.last_name == undefined ? '' : data.last_name);
        vm.delivery_to_phone = this.$parent.billing_info.mobile;
        vm.delivery_to_email = this.$parent.billing_info.email;
        vm.delivery_country = data.country;
        vm.delivery_address1 = data.address_line1;
        vm.delivery_address2 = data.address_line2;
        vm.delivery_city = data.city;
        vm.delivery_state = data.state;
        vm.delivery_zipcode = data.zipcode;
        vm.shipping_name = vm.delivery_to_name;
      } else if (vm.fftype == 'delivery' && !vm.is_delivery_with_above) {
        vm.shipping_country = data.country;
        vm.shipping_address1 = data.address_line1;
        vm.shipping_address2 = data.address_line2;
        vm.shipping_city = data.city;
        vm.shipping_state = data.state;
        vm.shipping_zipcode = data.zipcode;
        vm.ship_to_name = (data.first_name == undefined ? '' : this.$parent.billing_info.first_name) + ' ' + (data.last_name == undefined ? '' : data.last_name);
        vm.shipping_name = (data.first_name == undefined ? '' : this.$parent.billing_info.first_name) + ' ' + (data.last_name == undefined ? '' : data.last_name);
        vm.ship_to_phone = this.$parent.billing_info.mobile;
        vm.ship_to_email = this.$parent.billing_info.email;

      } else {
        vm.shipping_country = data.country;
        vm.shipping_address1 = data.address_line1;
        vm.shipping_address2 = data.address_line2;
        vm.shipping_city = data.city;
        vm.shipping_state = data.state;
        vm.shipping_zipcode = data.zipcode;
        vm.ship_to_name = (data.first_name == undefined ? '' : this.$parent.billing_info.first_name) + ' ' + (data.last_name == undefined ? '' : data.last_name);
        vm.ship_to_phone = this.$parent.billing_info.mobile;
        vm.ship_to_email = this.$parent.billing_info.email;
        vm.shipping_name = (data.first_name == undefined ? '' : this.$parent.billing_info.first_name) + ' ' + (data.last_name == undefined ? '' : data.last_name);


        vm.delivery_to_name = (data.first_name == undefined ? '' : this.$parent.billing_info.first_name) + ' ' + (data.last_name == undefined ? '' : data.last_name);
        vm.delivery_to_phone = this.$parent.billing_info.mobile;
        vm.delivery_to_email = this.$parent.billing_info.email;
        vm.delivery_country = data.country;
        vm.delivery_address1 = data.address_line1;
        vm.delivery_address2 = data.address_line2;
        vm.delivery_city = data.city;
        vm.delivery_state = data.state;
        vm.delivery_zipcode = data.zipcode;

      }

    });

    this.delivery_flow = localStorage.getItem('deliveryFlow');
  },
  props: ['billing_info'],
}
</script>
