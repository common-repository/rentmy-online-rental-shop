<template>
<span class="">
  <div v-if="store_config.customer.active && Object.keys(customer_info).length == 0" class="single-page-checkout-login">
    <a data-toggle="modal" data-target="#rentmy-customer-login-modal" style="cursor: pointer;font-size: 18px;">
      <p class="checkout-welcom-title">{{ lbl_welcome_to_login }}</p></a></div>
        

  <div class="billingdetails-leftside-inner mb-5">
        
    <h2 class="wc-checkout-title">{{ config_labels?.checkout_info?.title_billing }}</h2>

    <ul class="rentmy-error-wrapper" v-if="errors.length">
      <li v-for="(error,i) in errors" :key="i">
        <strong>{{ error }}</strong>
      </li>
    </ul>
    <br>
    <div class="row">
      <div class="col-xl-6">
        <div class="form-group">
          <label for="first_name">{{ config_labels.checkout_info.lbl_first_name }}*</label>
          <input type="text" class="form-control" name="first_name" v-model="first_name"
                 autocomplete="billing given-name"  @change="billingValidation();">
        </div>
      </div>
      <div class="col-xl-6">
        <div class="form-group">
          <label for="last_name">{{ config_labels.checkout_info.lbl_lastname }}*</label>
          <input type="text" class="form-control" name="last_name" v-model="last_name"
                 autocomplete="billing family-name" @change="billingValidation();">
        </div>
      </div>
      <div class="col-xl-6">
        <div class="form-group">
          <label for="mobile">{{ config_labels.checkout_info.lbl_mobile }}*</label>
          <input type="text" class="form-control" name="mobile" v-model="mobile" autocomplete="billing phone"
                 @change="billingValidation(); taxLookUp()" >
        </div>
      </div>
      <div class="col-xl-6">
        <div class="form-group">
          <label for="email">{{ config_labels.checkout_info.lbl_email }}*</label>
          <input type="email" class="form-control" name="email" v-model="email" autocomplete="billing email"
                 @change="billingValidation()">
        </div>
      </div>
      
      <div class="col-xl-12" v-if="Object.keys(customer_info).length == 0">
        <div class="form-group">
          <label for="country">{{ config_labels.checkout_info.lbl_country }}*</label>
          <select class="form-control" name="country" v-model="country" autocomplete="billing country"
                  @change="broadcastBillingValue(); billingValidation(); taxLookUp()">
            <option disabled value="">Select One</option>
            <option v-for="country in rentmy_countries" :key="country.id" :value="country.code">{{ country.name }}
            </option>
          </select>
        </div>
      </div>
      <div class="col-xl-6" v-if="Object.keys(customer_info).length == 0">
        <div class="form-group">
          <label for="address_line1">{{ config_labels.checkout_info.lbl_address_line_1 }}*</label>
          <input type="text" class="form-control" name="address_line1" v-model="address_line1"
                 autocomplete="billing address-line1"
                 @change="broadcastBillingValue(); billingValidation();">
        </div>
      </div>
      <div class="col-xl-6" v-if="Object.keys(customer_info).length == 0">
        <div class="form-group">
          <label for="address_line2">{{ config_labels.checkout_info.lbl_address_line_2 }}</label>
          <input type="text" class="form-control" name="address_line2" v-model="address_line2"
                 autocomplete="billing address-line2"
                 @change="broadcastBillingValue(); billingValidation();">
        </div>
      </div>
      <div class="col-xl-6" v-if="Object.keys(customer_info).length == 0">
        <div class="form-group">
          <label for="city">{{ config_labels.checkout_info.lbl_city }}*</label>
          <input type="text" class="form-control" name="city" id="city" v-model="city"
                 autocomplete="billing address-level2"
                 @change="broadcastBillingValue(); billingValidation(); taxLookUp()">
        </div>
      </div>
      <div class="col-xl-6" v-if="Object.keys(customer_info).length == 0">
        <div class="form-group">
          <label for="zipcode">{{ config_labels.checkout_info.lbl_zipcode }}*</label>
          <input type="text" class="form-control" name="zipcode" v-model="zipcode" autocomplete="billing postal-code"
                 @change="broadcastBillingValue(); billingValidation(); taxLookUp()">
        </div>
      </div>
      <div class="col-xl-6" v-if="Object.keys(customer_info).length == 0">
        <div class="form-group">
          <label for="state">{{ config_labels.checkout_info.lbl_state }}*</label>
          <input type="text" class="form-control" name="state" v-model="state" autocomplete="billing region"
                 @change="broadcastBillingValue(); billingValidation(); taxLookUp()">
        </div>
      </div>

      <div class="card-body p-0 fulfilment-body" v-if="Object.keys(customer_info).length != 0">
<!--        <h5 style="padding-left: 15px;margin-left: 5px;">Billing Address</h5>-->
        <div class="custom-control custom-radio" style="padding-left: 0px;">
          <label class="m-radio col-md-12" v-for="(address, index) in billing_address" :key="index"  v-on:click="selectCustomerAddress(address.id)">
            <input name="loc" type="radio"
                   class="ng-valid ng-dirty ng-touched"> {{ address.full_address }}</label>
            <label class="m-radio col-md-12">
            <input name="loc" type="radio" v-on:click="createNewAddress" class="ng-valid ng-dirty ng-touched"> Create New </label>
            
        </div>
<div class="create-new-address row" style="margin-left: 0px;margin-right: 0px;" v-show="ia_create_new_address">
      <div class="col-xl-12">
        <div class="form-group">
          <label for="country">{{ config_labels.checkout_info.lbl_country }}*</label>
          <select class="form-control" name="country" v-model="new_address.country" autocomplete="billing country">
            <option disabled value="">Select One</option>
            <option v-for="country in rentmy_countries" :key="country.id" :value="country.code">{{ country.name }}
            </option>
          </select>
        </div>
      </div>
      <div class="col-xl-6">
        <div class="form-group">
          <label for="address_line1">{{ config_labels.checkout_info.lbl_address_line_1 }}*</label>
          <input type="text" class="form-control" name="address_line1" v-model="new_address.address_line1"
                 autocomplete="billing address-line1">
        </div>
      </div>
      <div class="col-xl-6">
        <div class="form-group">
          <label for="address_line2">{{ config_labels.checkout_info.lbl_address_line_2 }}</label>
          <input type="text" class="form-control" name="address_line2" v-model="new_address.address_line2"
                 autocomplete="billing address-line2">
        </div>
      </div>
      <div class="col-xl-6">
        <div class="form-group">
          <label for="city">{{ config_labels.checkout_info.lbl_city }}*</label>
          <input type="text" class="form-control" name="city" id="new_city" v-model="new_address.city"
                 autocomplete="billing address-level2"
          >
        </div>
      </div>
      <div class="col-xl-6">
        <div class="form-group">
          <label for="zipcode">{{ config_labels.checkout_info.lbl_zipcode }}*</label>
          <input type="text" class="form-control" name="zipcode" v-model="new_address.zipcode"
                 autocomplete="billing postal-code">
        </div>
      </div>
      <div class="col-xl-6">
        <div class="form-group">
          <label for="state">{{ config_labels.checkout_info.lbl_state }}*</label>
          <input type="text" class="form-control" name="state" v-model="new_address.state" autocomplete="billing region"
          >
        </div>
      </div>

      <div class="col-md-12 col-xs-12">
        <button class="btn theme-btn bg-secondary text-light" v-on:click="addnewAddress" type="button">Add</button>
        <button class="btn bg-secondary text-light" v-on:click="ia_create_new_address=false"
                type="button">Cancel</button>
      </div>
</div>
      </div>
    



    <div class="row" v-if="config.show_checkout_additional_field">
      <!-- <h4 class="">{{ config_labels.checkout_info.title_additional }}</h4>
      <div class="col-xl-6">
        <div class="form-group">
          <label for="special_instructions">{{ config_labels.checkout_info.lbl_special_comments }}</label>
          <input type="text" class="form-control" name="special_instructions" v-model="special_instructions">
        </div>
      </div>
      <div class="col-xl-6">
        <div class="form-group">
          <label for="special_requests">{{ config_labels.checkout_info.lbl_special_request }}</label>
          <input type="text" class="form-control" name="special_requests" v-model="special_requests">
        </div>
      </div>
      <div class="col-xl-6">
        <div class="form-group">
          <label for="driving_license">{{ config_labels.checkout_info.lbl_driving_license }}</label>
          <input type="text" class="form-control" name="driving_license" v-model="driving_license">
        </div>
      </div>
      <div class="clear"></div> -->
    </div>
   <div class="row" style="margin-right: 0px;margin-left: 0px;" v-if="custom_fields.length > 0">
     <div class="clear"></div>
      <h4 class="">{{ config_labels.checkout_info.title_custom_checkout }}</h4>
     <div class="col-xl-6" v-for="(field,i) in custom_fields" :key="i">
       <div class="form-group">
         <label :for="`rm_custom_`+field.field_name">{{ field.field_label+(field.field_is_required?'*':'') }}</label>
         <input v-if="field.field_type==0" type="text" class="form-control" :name="`custom_`+field.field_name" :id="`rm_custom_`+field.field_name" v-model="custom_fields[i].field_values">
         <div class="file-uploade-box" v-else-if="field.field_type==2" @click="openTragetFile($event)">
           <input type="file" @change="fileChanged($event, i)" class="form-control file-uploader" :name="`custom_`+field.field_name" :id="`rm_custom_`+field.field_name" v-on:change="uploadFile(field,i)">
           <small class="selected-filename" :id="'file_index_' + i"></small>
         </div>
         <select v-else-if="field.field_type==1" class="form-control" :name="field.id" :id="`rm_custom_` + field.field_name" v-on:change="setCustomSelectFieldValue">
            <option value="">{{config_labels.others.txt_placeholder_select??"--select--" }}</option>
           <option v-for="value in field.options" :key="value" :value="value">{{ value }}</option>
         </select>
     </div>
      </div>
   </div>
    <div class="rent-my-loader text-center" v-if="loading">
        <i class="fa fa-spin fa-spinner fa-2x"></i>
    </div>
  </div>
  </div>

  </span>

</template>

<script>
module.exports = {
  data: function () {
    return {
      title: "Billing Address",
      rentmy_countries: rm_countries,
      custom_fields: rm_custom_fields.data,
      errors: [],
      first_name: "",
      last_name: "",
      mobile: "",
      email: "",
      country: rm_storeCountry != undefined ?rm_storeCountry:'US',
      address_line1: "",
      address_line2: "",
      city: "",
      zipcode: "",
      state: "",
      instance: "",
      special_instructions: "",
      special_requests: "",
      driving_license: "",
      loading: false,
      config: rentmy_config_data_preloaded,
      config_labels: config_labels,
      customer_info: customer_info,
      store_config: store_config,
      billing_address: billing_address,
      ia_create_new_address: false,
      store_name: rm_storeName,
      new_address: {
        country: rm_storeCountry != undefined ?rm_storeCountry:'US',
        address_line1: "",
        address_line2: "",
        city: "",
        zipcode: "",
        state: "",
      },
      lbl_welcome_to_login: "",
      show_checkout_additional_field: store_config.show_checkout_additional_field??true
    };
  },
  methods: {
    openTragetFile:function(event){      
      jQuery(event.target).find('input').trigger('click');
    },
    fileChanged:function(event, i){
      let fullPath = event.target.value;
      let filename = fullPath.replace(/^.*[\\\/]/, '');
      document.getElementById('file_index_' + i).innerText = filename;
    },
    taxLookUp: async function (){
      if ((this.country != '') && (this.city != '') && (this.state != '') && (this.zipcode != '')){
        var data = new FormData();
        data.set('action', 'rentmy_cart_topbar');
        data.set('country', this.country);
        data.set('city', this.city);
        data.set('state', this.state);
        data.set('zipcode', this.zipcode);

        var cartResponse = await axios({
          method: 'post',
          url: rentmy_ajax_object.ajaxurl,
          data: data
        });
        serveBus.$emit('taxLookUp',  cartResponse.data);
      }
    },
    billingValidation: function (value) {
      this.validate();
      this.broadcastBillingValue();
    },
    broadcastBillingValue: function () {
      this.$emit("billing-validation", {
        country: this.country,
        zipcode: this.zipcode,
        city: this.city,
        state: this.state,
        address_line1: this.address_line1,
        address_line2: this.address_line2,
        first_name: this.first_name,
        last_name: this.last_name,
        mobile: this.mobile,
        email: this.email,
        errors: this.errors,
      });

      serveBus.$emit("broadcastBillingValue", {
        country: this.country,
        zipcode: this.zipcode,
        city: this.city,
        state: this.state,
        address_line1: this.address_line1,
        address_line2: this.address_line2,
        first_name: this.first_name,
        last_name: this.last_name,
        mobile: this.mobile,
        email: this.email,
        errors: this.errors,
      });
    },

    validate: function () {
      this.errors = [];

      if (!this.mobile) {
        this.errors.push("Mobile is required.");
      }
      if (!this.email) {
        this.errors.push("Email is required.");
      }
      if (!this.country) {
        this.errors.push("Country is required.");
      }
      if (!this.address_line1) {
        this.errors.push("Address Line 1 is required.");
      }
      if (!this.city) {
        this.errors.push("City is required.");
      }
      if (!this.state) {
        this.errors.push("State is required.");
      }
      if (!this.zipcode) {
        this.errors.push("Zipcode is required.");
      }

      let regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
      if (!regex.test(this.email)) {
        this.errors.push("Email address is not valid.");
      }

      if (isNaN(parseInt(this.mobile)) == true) {
        this.errors.push("Mobile number is not valid.");
      }

      if(this.custom_fields.length > 0){
        
      for (x in this.custom_fields) {
        let field = this.custom_fields[x];
        if (field.field_is_required) {
          // if (!field.field_values) {
          if (jQuery("#rm_custom_" + field.field_name) && !jQuery("#rm_custom_" + field.field_name).val()) {
            this.errors.push("Please fill " + field.field_label);
          }
        }
        }
      }

      return !this.errors.length;
    },

    getCustomFields: function () {
      let data = [];
console.log(this.custom_fields)
      for (x in this.custom_fields) {
        if (this.custom_fields[x].field_values){
          data.push({
            id: this.custom_fields[x].id,
            field_name: this.custom_fields[x].field_name,
            field_label: this.custom_fields[x].field_label,
            type: this.custom_fields[x].field_type,
            field_values: this.custom_fields[x].field_values,
          });
        }

      }
      return data;
    },

    uploadFile: function (field, i) {
      //console.log("Uploading...");
      //console.log( jQuery("#rm_custom_"+field.field_name).val() );
      let fd = new FormData();
      let files = jQuery("#rm_custom_" + field.field_name)[0].files[0];
      fd.append("file", files);

      /* let ajaxdata = {
          action: 'rentmy_options',
          action_type: 'upload_media',
          //data: fd
      }; */
      let ajaxdata = new FormData();
      ajaxdata.set("action", "rentmy_options");
      ajaxdata.set("action_type", "upload_media");
      ajaxdata.set("file", files);
      var vbapp = this;
      this.loading = true;

      axios
        .post(rentmy_ajax_object.ajaxurl, ajaxdata, {
          headers: {
            "Content-Type": "multipart/form-data",
          },
        })
        .then(function (response) {
          if (response.data.status == "OK") {
            vbapp.custom_fields.map(function (c_field) {
              if (c_field.id == field.id) {
                return (c_field.field_values =
                  response.data.result.data.filename);
              }
            });
          }

          vbapp.loading = false;
        })
        .catch(function (response) {
          //console.log(response);
          vbapp.loading = false;
        });

      /* jQuery.post(rentmy_ajax_object.ajaxurl, ajaxdata, function (response) {
          console.log(response);
          vfapp.loading = false;
      }); */
      /* jQuery.ajax({
          url: rentmy_ajax_object.ajaxurl,
          type: 'post',
          data: ajaxdata,
          contentType: 'multipart/form-data; boundary=----WebKitFormBoundaryRAVDyyyGxfvpF4bt',
          processData: false,
          success: function(response){
              console.log(response);
              vbapp.loading = false;
          },
          error: function(response) {
            console.log(response);
            vbapp.loading = false;
          }
      }); */
    },

    initialize_customer_info: function () {
      let ref = this;
      let customer = ref.customer_info;
      if (Object.keys(customer).length != 0) {
        ref.first_name = customer_info.first_name;
        ref.last_name = customer_info.last_name;
        ref.email = customer_info.email;
        ref.mobile = customer_info.mobile;
        ref.address_line1 = customer_info.address_line1;
        ref.address_line2 = customer_info.address_line2;
        ref.city = customer_info.city;
        ref.zipcode = customer_info.zipcode;
        ref.state = customer_info.state;
        ref.country = customer_info.country;
      }
    },

    selectCustomerAddress: function (id) {
      let vm = this;

      let addresses = vm.billing_address;
      let selected_address = addresses.filter(function (address) {
        return address.id == id;
      });

      vm.country = selected_address[0].country;
      vm.zipcode = selected_address[0].zipcode;
      vm.city = selected_address[0].city;
      vm.state = selected_address[0].state;
      vm.address_line1 = selected_address[0].address_line1;
      vm.address_line2 = selected_address[0].address_line2;
      serveBus.$emit("selectCustomerAddress", selected_address[0]);
      vm.taxLookUp();
      vm.broadcastBillingValue();
    },
    createNewAddress: function () {
      this.ia_create_new_address = true;
      // this.algoliaBillingInfo();
    },

    addnewAddress() {
      let vm = this;
      let ajaxdata = new FormData();
      ajaxdata.append("action", "rentmy_options");
      ajaxdata.append("action_type", "add_new_address");
      ajaxdata.append("country", vm.new_address.country);
      ajaxdata.append("state", vm.new_address.state);
      ajaxdata.append("address_line1", vm.new_address.address_line1);
      ajaxdata.append("address_line2", vm.new_address.address_line2);
      ajaxdata.append("city", vm.new_address.city);
      ajaxdata.append("zipcode", vm.new_address.zipcode);

      axios
        .post(rentmy_ajax_object.ajaxurl, ajaxdata, {
          headers: { contentType: "application/json" },
        })
        .then(function (response) {
          if (response.data.status == "OK") {
            vm.billing_address.push(response.data.result.data[0]);
            vm.ia_create_new_address = false;
            vm.new_address.address_line1 = "";
            vm.new_address.address_line1 = "";
            vm.new_address.city = "";
            vm.new_address.zipcode = "";
          }
        });
    },

    setCustomSelectFieldValue: function (event) {
      let value = event.target.value;
      let id = event.target.name;
      let vm = this;
      vm.custom_fields.forEach((field, index) => {
        if (field.id == id) {
          vm.custom_fields[index].field_values = value;
        }
      });
    },

    makeCustomFildOption: function () {
      let vm = this;
      vm.custom_fields.forEach((field, index) => {
        if (field.field_type == 1) {
          vm.custom_fields[index].options = field.field_values.split(";");
          vm.custom_fields[index].field_values = '';
        }
      });
    },

    convertWelcomeToLoginLabel: function () {
      let vm = this;
      let lbl = config_labels.checkout_info.lbl_welcome_to_login;
      vm.lbl_welcome_to_login = lbl.replace("%storename%", vm.store_name);
    },

    // algoliaBillingInfo: function(){
    //   this.input = document.getElementById("new_city");
    //   this.instance = places({
    //     container: this.input,
    //     appId: "plIF9PULAHKJ",
    //     apiKey: "33382f2e6281756a1ceb6302fbc6bcbe",
    //     type: "city",
    //     countries: [this.new_address.country],
    //     templates: {
    //       value: function (suggestion) {
    //         return suggestion.name;
    //       },
    //     },
    //   });

    //   this.instance.on("change", (e) => {
    //     this.new_address.state = e.suggestion.administrative;
    //     this.new_address.zipcode = e.suggestion.postcode;
    //     //this.city = e.suggestion.name;
    //     // this.address_line1 = e.suggestion.name;
    //     //  this.address_line2 = e.suggestion.name;
    //     //console.log(e.suggestion);
    //     //console.log(e.suggestion.name)
    //   });
    // },
  },

  watch: {
    /* special_instructions: function(val)
    {
      console.log(this.getCustomFields());
    } */
  },
  created: function () {
    this.initialize_customer_info();
    this.makeCustomFildOption();
    this.convertWelcomeToLoginLabel();
    this.$parent.$on("checkValidation", this.billingValidation);
  },

  mounted() {
  //   this.input = document.getElementById("city");
  //   this.instance = places({
  //     container: this.input,
  //     appId: "plIF9PULAHKJ",
  //     apiKey: "33382f2e6281756a1ceb6302fbc6bcbe",
  //     type: "city",
  //     countries: [this.country],
  //     templates: {
  //       value: function (suggestion) {
  //         return suggestion.name;
  //       },
  //     },
  //   });

  //   this.instance.on("change", (e) => {
  //     this.state = e.suggestion.administrative;
  //     this.zipcode = e.suggestion.postcode;
  //     //this.city = e.suggestion.name;
  //     // this.address_line1 = e.suggestion.name;
  //     //  this.address_line2 = e.suggestion.name;
  //     //console.log(e.suggestion);
  //     //console.log(e.suggestion.name)
  //   });

  //         this.$emit("billing-validation", {
  //       country: this.country,
  //       zipcode: this.zipcode,
  //       city: this.city,
  //       state: this.state,
  //       address_line1: this.address_line1,
  //       address_line2: this.address_line2,
  //       first_name: this.first_name,
  //       last_name: this.last_name,
  //       mobile: this.mobile,
  //       email: this.email,
  //       errors: this.errors,
  //     });
  },
};
</script>