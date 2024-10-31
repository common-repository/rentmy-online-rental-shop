<template>
    <div class="woocommerce-checkout-review-order">
        <div class="rent-my-loader text-center" v-show="!cart.cart_items">
            <i class="fa fa-spin fa-spinner fa-2x"></i>
        </div>
        <div class="order_summery" v-if="cart.cart_items">
            <div class="order-list" v-for="cart_item in cart.cart_items" :key="cart_item.id">
                <div class="order-img">
                    <img class="img-fluid img-avatar img-thumbnail" :src=imageLink(cart_item) />
                </div>
                <div class="order-product_details">
                    <div class="name">{{cart_item.product.name}}&nbsp; <strong class="product-quantity">×&nbsp;{{cart_item.quantity}}</strong>
                      <span class="custom-options" v-if="cart_item.product && cart_item.product.value != ''">
                          <br />
                          {{ cart_item.product.variant_chain }}
                      </span>
                      <span class="custom-options" v-if="cart_item.cart_product_options.length">
                        <br><span v-for="(fields, keys) in cart_item.cart_product_options" :key="keys">
                          <br> <span v-for="(option, key) in fields.options" :key="key">
                          {{option.label +': '+option.value}}{{ ((fields.options.length -1)==key)?'':','}}

                        </span>
                          {{"("+(config_labels?.product_details?.quantity??'Qty')+": "+fields.quantity +")"}}
                        </span>
                      </span>
                    </div>
                    <div class="bottom_info">
<!--                        <p class="qty">{{config_labels.cart.th_quantity}}: {{cart_item.quantity}}</p>-->
                      <p v-if="cart_item.discount.coupon_amount > 0" class="order-product_price">{{config_labels.cart.th_price}}: {{currencySymbol}}{{priceFormat(cart_item.sub_total)}} ({{currencySymbol}}{{priceFormat(cart_item.coupon_amount)}} {{config_labels.cart.coupon_applied??'coupon applied'}})</p>
                      <p v-else class="order-product_price">{{config_labels.cart.th_price}}: {{currencySymbol}}{{priceFormat(cart_item.sub_total)}}</p>
                    </div>
                </div>
                <div class="order-product_price"></div>
            </div>
        </div>

        <table class="shop_table checkout-ordertable" v-if="cart.cart_items" style="width: 100%">
            <tfoot>
            <tr class="cart-subtotal" v-if="cart.coupon_amount > 0">
              <th>Coupon Discount</th>
              <td><span class="woocommerce-Price-amount amount"><span class="woocommerce-Price-currencySymbol">{{currencySymbol}}</span>{{priceFormat(cart.coupon_amount)}}</span>
              </td>
            </tr>
            <tr class="cart-subtotal" v-if="config_labels.cart.th_subtotal != ''">
              <th>{{config_labels.cart.th_subtotal}}</th>
              <td><span class="woocommerce-Price-amount amount"><span class="woocommerce-Price-currencySymbol">{{currencySymbol}}</span>{{priceFormat(cart.sub_total)}}</span>
              <small v-if="store_config.tax.price_with_tax">(inc. tax)</small>
              </td>
            </tr>

                <tr class="cart-subtotal" v-if="cart.additional_charge>0">
                    <th>{{config_labels.cart.lbl_additional_charge??'Optional Services'}}</th>
                    <td><span class="woocommerce-Price-amount amount"><span class="woocommerce-Price-currencySymbol">{{currencySymbol}}</span>{{priceFormat(cart.additional_charge)}}</span>
                    </td>
                </tr>
<!--            <tr class="cart-subtotal" v-if="config_labels.cart.lbl_discount != ''">-->
<!--              <th>{{ config_labels.cart.lbl_discount}} </th>-->
<!--              <td><span class="woocommerce-Price-amount amount"><span class="woocommerce-Price-currencySymbol">{{currencySymbol}}</span>{{priceFormat(cart.total_discount)}}</span>-->
<!--              </td>-->
<!--            </tr>-->

            <tr class="cart-subtotal" v-if="config_labels.cart.lbl_total_deposite != ''">
              <th>{{ config_labels.cart.lbl_total_deposite}} </th>
              <td><span class="woocommerce-Price-amount amount"><span class="woocommerce-Price-currencySymbol">{{currencySymbol}}</span>{{priceFormat(cart.deposit_amount)}}</span>
              </td>
            </tr>

                <tr class="cart-subtotal" v-if="config_labels.cart.lbl_tax != '' && !cart.tax?.regular">
                    <th>{{ config_labels.cart.lbl_tax}} </th>
                    <td><span class="woocommerce-Price-amount amount"><span class="woocommerce-Price-currencySymbol">{{currencySymbol}}</span>{{priceFormat(cart.tax?.total??0)}}</span>
                    </td>
                </tr>
                <tr class="cart-subtotal" v-if="(cart.tax != null) && (cart.tax?.regular.length > 0)" v-for="rate in cart.tax?.regular">
                  <th>{{ rate.name }} ({{rate.rate}}%)</th>
<!--                  <td><span class="woocommerce-Price-amount amount"><span class="woocommerce-Price-currencySymbol">{{currencySymbol}}{{priceFormat(rate.total)}}</span></span></td>-->
                  <td><span class="woocommerce-Price-amount amount"><span class="woocommerce-Price-currencySymbol">{{currencySymbol}}</span>{{priceFormat(rate.total)}}</span>

                </tr>

            <tr class="cart-subtotal" v-if="config_labels.cart.lbl_shipping != ''">
              <th>{{config_labels.cart.lbl_shipping}}</th>
              <td><span class="woocommerce-Price-amount amount"><span class="woocommerce-Price-currencySymbol">{{currencySymbol}}</span>{{priceFormat(cart.delivery_charge)}}</span>
              </td>
            </tr>

            <tr  v-if="Object.keys(delivery_flows).length !== 0" v-for="(flow, key) in delivery_flows">

              <td style="font-size: 13px;padding-left: 10px" v-if="key=='storage_delivery'" v-html="flow.label2+' fee </br> '+ priceFormat(flow.loaded.delivery.fixed_charge, true) +' + '+priceFormat(flow.loaded.delivery.additional_charge, true) + ' (' +flow.loaded.delivery.distance.toFixed(2)+ ' ' + cart.options?.multi_store_delivery?.distance_unit +')'"></td>
              <td style="font-size: 13px;padding-left: 10px" v-else-if="key=='storage_pickup'" v-html="flow.label1 +' fee </br> '+ priceFormat(flow.loaded.pickup.fixed_charge, true) +' + '+priceFormat(flow.loaded.pickup.additional_charge, true) + ' (' +flow.loaded.pickup.distance.toFixed(2)+ ' ' + cart.options?.multi_store_delivery?.distance_unit +')'"></td>
              <td style="font-size: 13px;padding-left: 10px" v-else v-html="flow.label +' fee </br> '+ priceFormat(flow.fixed_charge, true) +' + '+priceFormat(flow.additional_charge??0, true) + ' (' +flow.total_distance.toFixed(2)+ ' ' + cart.options?.multi_store_delivery?.distance_unit +')'"></td>

              <td style="font-size: 13px"  v-if="key=='storage_delivery'" v-html="priceFormat(flow.loaded.delivery.charge, true)"></td>
              <td style="font-size: 13px"  v-else-if="key=='storage_pickup'" v-html="priceFormat(flow.loaded.pickup.charge, true)"></td>
              <td style="font-size: 13px"  v-else v-html="priceFormat(flow.charge, true)"></td>
            </tr>

            <tr class="cart-subtotal" v-if="config_labels.cart.lbl_delivery_tax != ''">
              <th>{{ config_labels.cart.lbl_delivery_tax}} </th>
              <td><span class="woocommerce-Price-amount amount"><span class="woocommerce-Price-currencySymbol">{{currencySymbol}}</span>{{priceFormat(cart.delivery_tax??0)}}</span>
              </td>
            </tr>

                <tr class="order-total" v-if="config_labels.cart.lbl_total != ''">
                    <th>{{ config_labels.cart.lbl_total}}</th>
                    <td><strong><span class="woocommerce-Price-amount amount"><span class="woocommerce-Price-currencySymbol">{{currencySymbol}}</span>{{priceFormat(cart.total)}}</span></strong>
                     <span v-if="cart.tax?.regular && (cart.tax.regular.length > 0) && store_config.tax.price_with_tax"><br> (<small v-for="tax in cart.tax.regular">includes <span class="woocommerce-Price-currencySymbol">{{currencySymbol}}</span>{{tax.total}}({{tax.rate}}%) {{tax.name}} ,</small>)</span>
                    </td>
                </tr>

            </tfoot>
        </table>
    </div>
</template>

<script>
module.exports = {
    data() {
        return{
            currencySymbol: rentmy_config_data_preloaded.currency_format.symbol,
            rentmy_base_file_url: rentmy_base_file_url,
            rentmy_asset_url: rentmy_asset_url,
            cart: {},
          config_labels: config_labels,
          is_additional_charge: false,
          store_config: store_config,
          delivery_flows: {},
          request_delivery_flow: ''
        }
    },
    methods: {
        loadCartElement: async function(add_addition_charge = false) {
            var data = new FormData();
            data.set('action', 'rentmy_cart_topbar');
            if (add_addition_charge){
              data.set('add_addition_charge', add_addition_charge);
            }

            var cartResponse = await axios({
                method: 'post',
                url: rentmy_ajax_object.ajaxurl,
                data: data
            });
            this.cart = cartResponse.data;
          this.delivery_flows = {};
            if (this.cart?.options?.multi_store_delivery?.flows){

              this.request_delivery_flow = this.cart.options?.multi_store_delivery?.delivery_flow;
              let label = config_labels?.cart?.lbl_drop_off_storage_pickup;
              if (this.request_delivery_flow == 1){
                label = config_labels?.cart?.lbl_drop_off_pickup;
              }else if (this.request_delivery_flow == 2){
                label = config_labels?.cart?.lbl_drop_off_move_pickup;
              }
              var labels = label.split(",");

              for (key in this.cart.options?.multi_store_delivery?.flows){


                if (key == 'storage'){
                  this.delivery_flows['storage_pickup'] = this.cart.options?.multi_store_delivery?.flows[key];
                  this.delivery_flows['storage_pickup']['label1'] = labels[1];

                  this.delivery_flows['storage_delivery'] = this.cart.options?.multi_store_delivery?.flows[key];
                  this.delivery_flows['storage_delivery']['label2'] = labels[2];


                }else{
                  this.delivery_flows[key] = this.cart.options?.multi_store_delivery?.flows[key];
                }

                if (key=='drop_off'){
                  if (this.request_delivery_flow==1){
                    this.delivery_flows[key]['label'] = labels[0]
                  }else if (this.request_delivery_flow==2){
                    this.delivery_flows[key]['label'] = labels[0]
                  }else if (this.request_delivery_flow==3){
                    this.delivery_flows[key]['label'] = labels[3]
                  }



                }
                if (key=='pickup'){
                  if (this.request_delivery_flow==1){
                    this.delivery_flows[key]['label'] = labels[1]
                  }else if (this.request_delivery_flow==2){
                    this.delivery_flows[key]['label'] = labels[2]
                  }else if (this.request_delivery_flow==3){
                    this.delivery_flows[key]['label'] = labels[0]
                  }
                }

                if (key=='move'){
                  if (this.request_delivery_flow==2){
                    this.delivery_flows[key]['label'] = labels[1]
                  }
                }

              }

            }
          
            serveBus.$emit('loadCartElement', cartResponse.data);
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
        let amountStr = parseFloat(amount).toFixed(2);
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
        imageLink: function (cart_item) {
            try {
                var imageLink = rentmy_base_file_url + 'products' + '/' + cart_item.store_id + '/' + cart_item.product_id + '/' + cart_item.product.images[0].image_small;
            } catch (e) {
                var imageLink = rentmy_asset_url + '/images/product-image-placeholder.jpg';
            }

            return imageLink;
        },
        setCart: function(cart) {
            this.cart = cart;
        },
        capitalizeFirstLetter: function (string) {
          return string.charAt(0).toUpperCase() + string.slice(1);
        }

    },
    mounted () {
        let vm = this;
        this.loadCartElement();
         serveBus.$on('additionalChargeToCart', (data) => {
            //  this.cart.sub_total = data.sub_total;
            //  this.cart.tax = data.tax;
            //  this.cart.delivery_charge = data.delivery_charge;
            //  this.cart.total_discount = data.total_discount;
            //  this.cart.total = data.total;
            //  this.cart.additional_charge = data.additional_charge;
            vm.loadCartElement(true);
         });

          serveBus.$on('orderAdditionalCharges', (status) => {
             this.is_additional_charge = status;
            // vm.loadCartElement();
          });
          serveBus.$on('taxLookUp', (data) => {
             this.cart = data;
          });

      serveBus.$on('update-cart', (data) => {
        vm.loadCartElement();
      });
    }
}
</script>
