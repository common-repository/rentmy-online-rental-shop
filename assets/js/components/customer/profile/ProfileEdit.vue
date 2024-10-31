<template>
  <div class="my-profile-body">
    <div class="my-profile-itemlist my-profile-itemlist-100">
      <div _ngcontent-ndq-c106="" class="my-profile-body">      

        <form
          id="rm_customer_info_update"
          _ngcontent-crl-c146=""
          novalidate=""
          class="my-profile-itemlist my-profile-itemlist-100 ng-pristine ng-valid ng-touched"
        >
          <div _ngcontent-crl-c146="" class="row">
            <div _ngcontent-crl-c146="" class="col-md-6 col-sm-12">
              <div _ngcontent-crl-c146="" class="my-profile-item">
                <h3 _ngcontent-crl-c146="" class="my-profile-item-title">
                  {{store_content?.customer_portal?.lbl_first_name??'First name'}}
                </h3>
                <input
                  _ngcontent-crl-c146=""
                  type="text"
                  :placeholder="store_content?.customer_portal?.lbl_first_name??'First name'"
                  v-model="profile_info.first_name"
                  class="form-control ng-untouched ng-pristine ng-valid"
                />
              </div>
            </div>
            <div _ngcontent-crl-c146="" class="col-md-6 col-sm-12">
              <div _ngcontent-crl-c146="" class="my-profile-item">
                <h3 _ngcontent-crl-c146="" class="my-profile-item-title">
                  {{store_content?.customer_portal?.lbl_last_name??'Last name'}}
                </h3>
                <input
                  _ngcontent-crl-c146=""
                  type="text"
                  :placeholder="store_content?.customer_portal?.lbl_last_name??'Last name'"
                  formcontrolname="last_name"
                   v-model="profile_info.last_name"
                  class="form-control ng-untouched ng-pristine ng-valid"
                />
              </div>
            </div>
            <div _ngcontent-crl-c146="" class="col-md-6 col-sm-12">
              <div _ngcontent-crl-c146="" class="my-profile-item">
                <h3 _ngcontent-crl-c146="" class="my-profile-item-title">
                  {{store_content?.customer_portal?.lbl_email??'Email Address2222'}}
                </h3>
                <input
                  _ngcontent-crl-c146=""
                  type="Email"
                  :placeholder="store_content?.customer_portal?.lbl_email??'Email Address'"
                  formcontrolname="email"
                   v-model="profile_info.email"
                  class="form-control ng-pristine ng-valid ng-touched"
                />
              </div>
            </div>
            <div _ngcontent-crl-c146="" class="col-md-6 col-sm-12">
              <div _ngcontent-crl-c146="" class="my-profile-item">
                <h3 _ngcontent-crl-c146="" class="my-profile-item-title">
                  {{store_content?.customer_portal?.lbl_contact_no??'Mobile'}}
                </h3>
                <input
                  _ngcontent-crl-c146=""
                  type="text"
                  :placeholder="store_content?.customer_portal?.lbl_contact_no??'Mobile'"
                  formcontrolname="mobile"
                   v-model="profile_info.mobile"
                  class="form-control ng-untouched ng-pristine ng-valid"
                />
              </div>
            </div>
            <div _ngcontent-crl-c146="" class="col-md-6 col-sm-12">
              <div _ngcontent-crl-c146="" class="my-profile-item">
                <h3 _ngcontent-crl-c146="" class="my-profile-item-title">
                  {{store_content?.customer_portal?.lbl_company_name??'Company'}}
                </h3>
                <input
                  _ngcontent-crl-c146=""
                  type="text"
                  :placeholder="store_content?.customer_portal?.lbl_company_name??'Company'"
                  formcontrolname="company"
                   v-model="profile_info.company"
                  class="form-control ng-untouched ng-pristine ng-valid"
                />
              </div>
            </div>
            <div _ngcontent-crl-c146="" class="col-sm-12">
              <div _ngcontent-crl-c146="" class="my-profile-item">
                <button  _ngcontent-crl-c146="" type="button" v-on:click="updateCustomerInfo" class="btn theme-btn mr-3 my-profile-btn-update">{{store_content?.customer_portal?.btn_update??'Update'}}</button>
                <button type="button" class="btn defualt-btn my-profile-btn-cancel" v-on:click="cancelEdit">
                  {{store_content?.cart?.btn_cancel??'Cancel'}}
                </button>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
  
</template>

<script>
module.exports = {
  data() {
    return {
      profile_info: {
        first_name: "",
        last_name: "",
        email: "",
        mobile: "",
        company: "",
        store_content: this.$parent.store_content

      },

    };
  },
  methods: {
    cancelEdit: function () {
      this.$parent.is_active.profile_info = true;
      this.$parent.is_active.profile_edit = false;
    },
    updateCustomerInfo: function(e){
      e.preventDefault();
        let vm = this;
        let url = rentmy_ajax_object.ajaxurl;
        let data = new FormData();
        data.set("action_type", "update_customer_info");
        data.set("action", "rentmy_options");
        data.set("first_name", vm.profile_info.first_name);
        data.set("last_name", vm.profile_info.last_name);
        data.set("email", vm.profile_info.email);
        data.set("mobile", vm.profile_info.mobile);
        data.set("company", vm.profile_info.company);

        axios.post(url, data).then(function(response){          
          if (response.status == 200) {
            vm.$parent.is_active.profile_edit = false;
            vm.$parent.is_active.profile_info = true;
          }
        });
    },
      getCustomerProfile: function(){
        let vm = this;
        let url = rentmy_ajax_object.ajaxurl;
        let data = new FormData();
        data.set("action_type", "get_customer_profile");
        data.set("action", "rentmy_options");
        axios.post(url, data).then(function(response){
          if(response.status == 200){
            vm.profile_info = response.data.data;
            console.log(vm.profile_info);
          }
        });
      },
  },

  created: function(){
    this.getCustomerProfile();
  }
};
</script>