<template>
  <div class="rentmy-profile-rightside w-100 p-0">
    <div class="profile-body p-0">
      <table class="table custome-table-responsive">
        <tr>
          <!-- <th></th> -->
          <th>{{store_content?.cart?.th_order_id??'Order ID'}}</th>
          <!-- <th>Jobs</th> -->
          <th>{{store_content?.cart?.th_address??'Address'}}</th>
          <th>{{store_content?.cart?.th_address??'Quantity'}}</th>
          <th>{{store_content?.cart?.th_price??'Price'}}</th>
          <th>{{store_content?.cart?.th_subtotal??'Total Price'}}</th>
          <th>{{store_content?.cart?.th_status??'Status'}}</th>
          <th>{{store_content?.cart?.th_action??'Action'}}</th>
        </tr>
        

        <tr class="even-tr" v-for="order in customer_orders" :key="order.id">
          <!-- <td style="max-width: 50px">
            <span
              id="product_sidebar_toggle"
              title="View Payment"
              class="m-portlet__nav-link btn m-btn m-btn--hover-brand m-btn--icon m-btn--icon-only m-btn--pill"
            >
              <i class="fa fa-plus-circle" id="order-icon-3371"></i>
            </span>
          </td>-->
          <td class="table-td-themecolor">{{ order.id }}</td>         
          <td>{{ order.address }}, {{order.city}}, {{ order.state }}, {{ order.zipcode }}</td>
          <td>{{ order.total_quantity }}</td>
          <td v-html="priceFormat(order.total_price)"></td>
          <td v-html="priceFormat(order.total)"></td>
          <td class="pl-0 pr-0">
            <span
              :class="'m-badge status-for-'+order.status "
              style="cursor: pointer"
              > {{ convertStatus(order.status) }} </span>
          </td>
          <td>
            <a title="View details" class="mr-3" v-on:click="viewOrderDetail(order.id)"><i class="fa fa-eye"></i></a>            
            <a
              title="Download PDF"
              class="mr-3"
              :href="'https://clientapi.rentmy.co/api/pages/pdf?order_id='+order.id"
              ><i class="fa fa-file-pdf-o"></i
            ></a>
            <a v-on:click="send_feedback_msg='';sendModal.message='';feedback_status='';sendModal.isShow = true; selected_order=order.id;">
              <i class="fa fa-paper-plane-o fa-paper-plane"></i>
            </a>            
            <!-- <a title="Cancel order" v-on:click="cancelOrder(order.id)"><i class="fa fa-trash"></i></a> -->
          </td>
        </tr>


      </table>

      <!-- <nav aria-label="Page navigation example" class="orderhistory-pagination">
        <ul class="pagination">
          <li class="page-item"><a class="page-link" href="#">Previous</a></li>
          <li class="page-item"><a class="page-link" href="#">1</a></li>
          <li class="page-item"><a class="page-link" href="#">2</a></li>
          <li class="page-item"><a class="page-link" href="#">3</a></li>
          <li class="page-item"><a class="page-link" href="#">Next</a></li>
        </ul>
      </nav> -->
      <paginate
      :page-count="Math.ceil(pagination.total/pagination.limit)"
      :click-handler="paginationHandler"
      :prev-text="'<<'"
      :next-text="'>>'"
      :container-class="'orderhistory-pagination'"
      :page-class="'page-item'">
    </paginate>
      

      <!-- Start Modal -->
      <div id="exampleModalCenter" :class="['modal fade', {show: sendModal.isShow}]" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" :style="sendModal.isShow ? 'padding-right: 17px; display: block;background: #00000047;' : 'display: none'">
        <div class="modal-dialog modal-dialog-centered" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="exampleModalCenterTitle">Send message for order #{{selected_order}}</h5>
              <button v-on:click="sendModal.isShow = false" type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">×</span>
              </button>
            </div>
            <div class="modal-body">
              <p :class="feedback_status=='NOK'?'text-danger':'text-success'" v-html="send_feedback_msg"></p>
              <label for="order-message">Feedback</label>
              <textarea id="order-message" name="message" id="" cols="30" rows="3" v-model="sendModal.message"></textarea>
            </div>
            <div class="modal-footer">
              <button v-on:click="sendModal.isShow = false" type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
              <button :disabled="sendModal.message==''?true:false" type="button" class="btn btn-primary" v-on:click="sendMessage(selected_order)">send</button>
            </div>
          </div>
        </div>
      </div><!-- End Modal -->


    </div>


  </div>
</template>

<script>
Vue.component('paginate', VuejsPaginate);
module.exports = {
  data() {
    return {
      customer_orders:[],
      order_status:[],
      pagination:{
        limit: 10,
        page_no: 1,
        total: 0
      },
      sendModal:{
        isShow:false,
        message:'',
      },
      store_content: store_content,
      selected_order: '',
      send_feedback_msg: '',
      feedback_status: '',
      store_config: store_config,
    };
  },
  methods: {
    getCustomerOrder: function(){
        let vm = this;
        let url = rentmy_ajax_object.ajaxurl;
        let data = new FormData();
        data.set("action_type", "get_customer_orders");
        data.set("action", "rentmy_options");
        data.set("page_no", vm.pagination.page_no);
        data.set("limit", vm.pagination.limit);
        axios.post(url, data).then(function(response){
          if (response.status == 200) {
            vm.customer_orders = response.data.data;
            // vm.pagination.limit = response.data.limit;
            vm.pagination.total = response.data.total;
            vm.pagination.page_no = response.data.page_no;

            customerBus.$emit("getCustomerOrder", false);
          }
        });
    },
    viewOrderDetail: function(order_id){
      let vm = this;
      this.$parent.is_active_component.customer_order_history = false;
      this.$parent.is_active_component.customer_order_summary = true;
      this.$parent.selected_order_id = order_id;
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

    cancelOrder: function(id){
        let vm = this;
        let url = rentmy_ajax_object.ajaxurl;
        let data = new FormData();
        data.set("action_type", "cancel_order");
        data.set("action", "rentmy_options");
        data.set("order_id", id);
        data.set("status", 1);
        axios.post(url, data).then(function(response){
         this.getCustomerOrder();
        });
    },

    sendMessage(order_id){
      let vm = this;
      let url = rentmy_ajax_object.ajaxurl;
      let data = new FormData();
      if (vm.sendModal.message == ''){
        return;
      }
      data.set("action_type", "send_message");
      data.set("action", "rentmy_options");
      data.set("order_id", order_id);
      data.set("message", vm.sendModal.message);
      axios.post(url, data).then(function(response){
        console.log(response)
        vm.send_feedback_msg = response?.data?.result?.message??'';
        vm.feedback_status = response?.data?.status??'';
        vm.sendModal.message = '';
      });
    },
    priceFormat: function (amount, withSymbol=true) {
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
    paginationHandler: function(pageNum){
      this.pagination.page_no = pageNum;
      this.getCustomerOrder();
    }
  },
  created: function(){
    this.getCustomerOrder();
  },
  mounted: function () {
    customerBus.$emit("getCustomerOrder", true);
    this.order_status = this.$parent.order_status;
    // console.log("Order History Mounted");
  },
};
</script>