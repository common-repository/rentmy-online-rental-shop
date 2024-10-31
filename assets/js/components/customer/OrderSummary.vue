<template>
  <div class="rentmy-profile-rightside w-100 p-0">
    <div class="profile-info-title">
      <h4>Order Details</h4>
      <div class="profile-title">
        <button
          class="btn theme-btn order-back-btn orderdetails-backbtn float-right" v-on:click="backFromOrderSummary"
        >
          {{store_content?.checkout_info?.btn_back??'Back'}}
        </button>
        <a
          title="Download PDF"
          class="btn btn-info float-right mr-3 pdf-btn"
          :href="'https://clientapi.rentmy.co/api/pages/pdf?order_id='+customer_order_details.id"
          ><i class="fa fa-file-pdf-o"></i
        ></a>
      </div>
    </div>
    <div class="profile-body">
      <div class="info pb-2">
        <div class="description-fields">
          <p>
            <span class="description-field-title">{{store_content?.checkout_info?.lbl_customer_name??'Customer Name'}}:</span
            ><span>{{ customer_order_details.first_name +' '+customer_order_details.last_name }}</span>
          </p>
        </div>
        <div class="description-fields">
          <p>
            <span class="description-field-title">{{store_content?.checkout_info?.lbl_billing_address??'Billing Address'}}: </span
            ><span>{{ customer_order_details.address_line1 }}</span>
          </p>
        </div>
        <div class="description-fields">
          <p>
            <span class="description-field-title">{{store_content?.checkout_info?.lbl_status??'Status'}}: </span
            ><span :class="'m-badge status-for-'+customer_order_details.status "
              >{{ convertStatus(customer_order_details.status) }}</span
            >
           
          </p>
        </div>
        <div class="row mt-4 align-items-center order-rentaldate-area">
          <div class="col-lg-6 col-md-6 col-sm-12" v-if="customer_order_details.rent_start">
            <div class="w-100">
              <label class="rental-datelabel mb-0">
                {{store_content?.cart?.rent_date??'Rental Dates'}}: {{ customer_order_details.rent_start }} - {{ customer_order_details.rent_end }}
<!--                <i class="fa fa-edit edit-icon cursor-pointer"></i >-->
             </label>
            </div>
          </div>

          <!-- <div class="col-lg-6 col-md-6 col-sm-12 text-right">
            <button class="btn btn-brand mr-2">Payment History</button>

            <button class="btn btn-info float-right">Add Item</button>
          </div> -->
        </div>
      </div>
    </div>
    <div class="profile-body pt-0">
      <table class="table order-details-table custome-table-responsive">
        <thead>
          <tr>
            <th></th>
            <th>{{store_content?.cart?.th_product??'Description'}}</th>
            <th>{{store_content?.cart?.th_price??'Price'}}</th>
            <th>{{store_content?.cart?.th_address??'Quantity'}}</th>
            <th>{{store_content?.cart?.th_subtotal??'Total Price'}}</th>
            <!-- <th>Action</th> -->
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in customer_order_details.order_items" :key="item.id">
            <td>
              <img v-if="item.product.images.length != 0"
                alt="Product Image"
                class="img-fluid img-avatar img-thumbnail img-resize"
                :src="'https://s3.us-east-2.amazonaws.com/images.rentmy.co/products/'+customer_order_details.store_id+'/'+item.product_id+'/'+(item.product.images.length != 0?item.product.images[0].image_small:'')"
              />
              <img v-else
                alt="Product Image"
                class="img-fluid img-avatar img-thumbnail img-resize"
                :src="rentmy_plugin_base_url+'/assets/images/product-image-placeholder.jpg'"
              />
            </td>
            <td>
              <h5>{{ item.product.name }}</h5>
            </td>
            <td>{{currencySymbol}}{{ convertPrice(item.price) }}</td>
            <td>
                {{ item.quantity }}
            </td>
            <td>{{currencySymbol}}{{ convertPrice(item.sub_total) }}</td>
            <!-- <td>
              <a title="Delete Item"><i class="fa fa-trash"></i></a>
            </td> -->
          </tr>
        </tbody>
      </table>
      <div class="row justify-content-end m-0">
        <div class="table-responsive order-summary">
          <h4>{{store_content?.checkout_info?.title_order_summary??'Order Summary'}}</h4>
          <table class="table cart">
            <tbody>
              <tr>
                <td>{{store_content?.cart?.th_subtotal??'Sub total'}}</td>
                <td>
                  <span class="cart_p"><b>{{currencySymbol}}{{ convertPrice(customer_order_details.sub_total??0) }}</b></span>
                </td>
              </tr>
              <tr>
                <td>{{store_content?.cart?.lbl_discount??'Discount'}}</td>
                <td><span class="cart_p"> {{currencySymbol}}{{ convertPrice(customer_order_details.discount??0) }}</span></td>
              </tr>
              <tr>
                <td>{{store_content?.cart?.lbl_tax??'Sales tax'}}</td>
                <td><span class="cart_p"> {{currencySymbol}}{{ convertPrice(customer_order_details?.tax?.total??0) }}</span></td>
              </tr>
              <tr></tr>
              <tr>
                <td>{{store_content?.cart?.lbl_total_deposite??'Deposit'}}</td>
                <td><span class="cart_p">{{currencySymbol}}{{ convertPrice(customer_order_details.total_deposit??0) }}</span></td>
              </tr>
              <tr>
                <td>
                  <h5>{{store_content?.cart?.lbl_total??'Total'}}</h5>
                </td>
                <td>
                  <h5><span class="cart_p">{{currencySymbol}}{{ convertPrice(customer_order_details.total??0) }}</span></h5>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
module.exports = {
  props: ['order_id'],
  data() {
    return {
      customer_order_details: {},
      order_status: [],
      rentmy_plugin_base_url:rentmy_plugin_base_url,
      currencySymbol: rentmy_config_data_preloaded.currency_format.symbol,
      store_content: store_content,
    };
  },
  methods: {
    getCustomerOrderDetails: function(){
        let vm = this;
        let url = rentmy_ajax_object.ajaxurl;
        let data = new FormData();
        data.set("action_type", "get_customer_orders_details");
        data.set("action", "rentmy_options");
        data.set("order_id", this.order_id);

        axios.post(url, data).then(function(response){
          if (response.status == 200) {
            vm.customer_order_details = response.data.data;
            console.log(vm.customer_order_details);
          }
        });
    },
    convertPrice: function(priceVal){
      return parseFloat(priceVal).toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,');
    },
    backFromOrderSummary: function(){
      this.$parent.is_active_component.customer_order_history = true;
      this.$parent.is_active_component.customer_order_summary = false;
    },
    convertStatus: function(status_id){
      let label = '';
      this.order_status.forEach(status => {
        
        if(status.id == status_id){
          label = status.label;
        }else{
          if('child' in status){
            childList = status.child;
            childList.forEach(chil => {
              if(chil.id == status_id){
                label = chil.label;
              }
            });
          }
        }

      });
      return label;
    },
  },
  created: function(){
    this.getCustomerOrderDetails();
  },
  mounted: function () {
    this.order_status = this.$parent.order_status;

  },
};
</script>