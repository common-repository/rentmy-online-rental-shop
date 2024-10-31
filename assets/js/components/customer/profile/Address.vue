<template>
  <div class="rentmy-profile-body p-0 pb-4">
    <div class="rentmy-profile-itemlist rentmy-profile-itemlist-100">
      <div class="profile-address-area">
        <div class="row profile-address-list m-0">
          <div class="col-md-12 mt-0 p-0">
            <div class="address-title w-100">
              <h5><i class="fa fa-map-marker mr-2"></i>{{store_content?.customer_portal?.lbl_address??'Address'}}</h5>
              <button
                class="btn btn-sm biling-address-add float-right"
                data-toggle="modal"
                data-target="#customer-address-modal"
                data-whatever="@getbootstrap"
                v-on:click="addressAddModal"
              >
                <i class="fa fa-plus"></i>
              </button>
            </div>
          </div>
          <div class="col-md-12 mt-0 p-0">
            <div class="address-body">
              <div
                class="form-group"
                v-for="(address, index) in addresses"
                :key="index"
              >
                <span class="w-100"
                  ><b>{{ address.type }}</b></span
                >

                <label>{{ address.full_address }} </label>
                <button class="btn-sm biling-address-edit float-right" v-on:click="deleteAddress(address.id)">
                  <i class="fa fa-trash"></i>
                </button>
                <button class="btn-sm biling-address-edit float-right btn_custom_dark" v-on:click="editAddress(address.id)">
                  <i class="fa fa-pencil"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Start Modal -->

    <div
      class="modal fade"
      id="customer-address-modal"
      tabindex="-1"
      aria-labelledby="customer-address-modalLabel"
      aria-hidden="true"
    >
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header address-modal-header">
            <h5 class="modal-title address-modal-title" id="customer-address-modalLabel" v-if="!is_edit">
              {{store_content?.customer_portal?.lbl_add_address??'Add new address'}}
            </h5>
            <h5 class="modal-title address-modal-title" id="customer-address-modalLabel" v-else>
              {{store_content?.customer_portal?.lbl_edit_address??'Update address'}}
            </h5>
            <button
              type="button"
              class="close"
              data-dismiss="modal"
              aria-label="Close"
            >
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <form
              
              class="ng-pristine ng-invalid ng-touched"
            >
              <div class="form-group">
                <label for=""
                  >{{store_content?.customer_portal?.lbl_add_address_type??"Add address type (eg: 'Primary/Office/Home')"}}</label
                >
                <input
                  type="text"
                  formcontrolname="type"
                  v-model="form_data.type"
                  class="form-control ng-pristine ng-valid ng-touched"
                />
              </div>
              <div _ngcontent-ndq-c107="" class="form-group">
                <label _ngcontent-ndq-c107="" for="">{{store_content?.customer_portal?.lbl_country??"Country"}} </label
                ><sup _ngcontent-ndq-c107="" class="red">*</sup>
                <select
                  _ngcontent-ndq-c107=""
                  formcontrolname="country"
                  v-model="form_data.country"
                  id="country_online"
                  class="form-control m-input dropdown-cls ng-untouched ng-pristine ng-valid"
                >
                  <option v-for="country in countries" :key="country.id" :value="country.code">{{ country.name }}</option>
                </select>
              </div>
              <div _ngcontent-ndq-c107="" class="form-group">
                <label _ngcontent-ndq-c107="" for="">{{store_content?.customer_portal?.lbl_address??"Address"}}</label
                ><input
                  _ngcontent-ndq-c107=""
                  type="text"
                  formcontrolname="address_line1"
                  v-model="form_data.address_line1"
                  class="form-control ng-untouched ng-pristine ng-invalid"
                />
              </div>
              <div _ngcontent-ndq-c107="" class="form-group">
                <label _ngcontent-ndq-c107="" for="">{{store_content?.customer_portal?.lbl_city??"City"}}</label
                ><input
                  _ngcontent-ndq-c107=""
                  type="text"
                  formcontrolname="city"
                  v-model="form_data.city"
                  class="form-control ng-untouched ng-pristine ng-invalid"
                />
              </div>
              <div _ngcontent-ndq-c107="" class="form-group">
                <label _ngcontent-ndq-c107="" for="">{{store_content?.customer_portal?.lbl_state??"State"}}</label
                ><input
                  _ngcontent-ndq-c107=""
                  type="text"
                  formcontrolname="state"
                  v-model="form_data.state"
                  class="form-control ng-untouched ng-pristine ng-valid"
                />
              </div>
              <div _ngcontent-ndq-c107="" class="form-group">
                <label _ngcontent-ndq-c107="" for="">{{store_content?.customer_portal?.lbl_zipcode??"Zip code"}}</label
                ><input
                  _ngcontent-ndq-c107=""
                  type="text"
                  formcontrolname="zipcode"
                  v-model="form_data.zipcode"
                  class="form-control ng-untouched ng-pristine ng-valid"
                />
              </div>
              <div _ngcontent-ndq-c107="" class="form-group">
                <button
                  _ngcontent-ndq-c107=""
                  type="submit"
                  v-on:click="addNewAddress"
                  class="btn theme-btn btn-lg btn-block"
                  v-if="!is_edit"
                >
                  {{store_content?.customer_portal?.btn_add_address??"Add Address"}}
                </button>

                <button
                  _ngcontent-ndq-c107=""
                  type="submit"
                  v-on:click="updateAddress"
                  class="btn theme-btn btn-lg btn-block"
                  v-else
                >
                  {{store_content?.customer_portal?.btn_edit_address??"Updated Address"}}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
    <!-- End Modal -->
  </div>
</template>

<script>
module.exports = {
  data() {
    return {
      addresses: [],
      form_data: {
        id:'',
        type: '',
        country: 'US',
        address_line1: '',
        city: '',
        state: '',
        zipcode: '',
      },
      is_edit: false,
      countries: rm_countries,
      store_content: store_content
    };
  },
  methods: {
    getCustomerAddresses: function () {
      let vm = this;
      let url = rentmy_ajax_object.ajaxurl;
      let data = new FormData();
      data.set("action_type", "get_customer_address");
      data.set("action", "rentmy_options");
      axios.post(url, data).then(function (response) {
        if (response.status == 200) {
          vm.addresses = response.data.data;
          
        }
      });
    },

    addressAddModal: function(){
      this.is_edit = false;
        this.form_data.type = "";
        this.form_data.country = "US";
        this.form_data.address_line1 = "";
        this.form_data.city ="";
        this.form_data.state = "";
        this.form_data.zipcode = "";

    },
    addNewAddress: function (e) {
      e.preventDefault();
      let vm = this;
      let url = rentmy_ajax_object.ajaxurl;
      let data = new FormData();
      data.set("action_type", "add_customer_address");
      data.set("action", "rentmy_options");
      data.set("type", vm.form_data.type);
      data.set("country", vm.form_data.country);
      data.set("address_line1", vm.form_data.address_line1);
      data.set("city", vm.form_data.city);
      data.set("state", vm.form_data.state);
      data.set("zipcode", vm.form_data.zipcode);

      axios.post(url, data).then(function (response) {
        if (response.data.status == "OK") {
          jQuery("#customer-address-modal").modal('hide');
          vm.getCustomerAddresses();
          
        }
      });
    },
    deleteAddress: function(id){
     let vm = this;
      let url = rentmy_ajax_object.ajaxurl;
      let data = new FormData();
      data.set("action_type", "delete_customer_address");
      data.set("action", "rentmy_options");
      data.set("id", id);

      let is_del = confirm('Are you sure to delete?');

      if(!is_del){
        return;
      }

      axios.post(url, data).then(function (response) {

          vm.getCustomerAddresses();

      });
    },

    editAddress: function(id){
        let addresses = this.addresses;
        let address = addresses.filter(addr=>{
           return addr.id == id;
        })[0];
        this.is_edit = true;
        this.form_data.id = address.id;
        this.form_data.type = address.type;
        this.form_data.country = address.country.toUpperCase();
        this.form_data.address_line1 = address.address_line1;
        this.form_data.city = address.city;
        this.form_data.state = address.state;
        this.form_data.zipcode = address.zipcode;

        jQuery("#customer-address-modal").modal('show');
    },
    updateAddress: function (e) {
      e.preventDefault();
      let vm = this;
      let url = rentmy_ajax_object.ajaxurl;
      let data = new FormData();
      data.set("action_type", "edit_customer_address");
      data.set("action", "rentmy_options");
      data.set("id", vm.form_data.id);
      data.set("type", vm.form_data.type);
      data.set("country", vm.form_data.country);
      data.set("address_line1", vm.form_data.address_line1);
      data.set("city", vm.form_data.city);
      data.set("state", vm.form_data.state);
      data.set("zipcode", vm.form_data.zipcode);

      axios.post(url, data).then(function (response) {
        if (response.data.status == "OK") {
          this.is_edit == false;
          jQuery("#customer-address-modal").modal('hide');
          vm.getCustomerAddresses();
          
        }
      });
    },
  },
  created: function () {
    this.getCustomerAddresses();
    // console.log(this.countries);
  },
};
</script>