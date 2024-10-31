<template>
  <div class="rentmy-profile-rightside w-100">
    <div class="profile-info-title">
      <h4>{{store_content?.customer_portal?.lbl_change_password??'Change Password'}}</h4>
     
    </div>
    <div class="rentmy-profile-body profile-body">
       <span class="text-danger" v-if="error != ''">{{ error }}</span>
      <div class="row">
        <div class="col-md-6">
          <form class="ng-untouched ng-pristine ng-invalid">
            <div class="form-group">
              <label class="mb-0"> {{store_content?.customer_portal?.lbl_old_password??'Old Password'}}</label>
              <input
                type="password"
                v-model="form_data.old_password"
                :placeholder="store_content?.customer_portal?.lbl_old_password??'Old Password'"
                class="form-control ng-untouched ng-pristine ng-invalid"
              />
            </div>
            <div class="form-group">
              <label class="mb-0">{{store_content?.customer_portal?.lbl_new_password??'New Password'}}</label>
              <input
                type="password"
                v-model="form_data.password"
                :placeholder="store_content?.customer_portal?.lbl_new_password??'New Password'"
                class="form-control ng-untouched ng-pristine ng-invalid"
              />
            </div>
            <div class="form-group">
              <label class="mb-0">{{store_content?.customer_portal?.lbl_confirm_password??'Confirm password'}}</label>
              <input
                type="password"
                v-model="form_data.confirm_password"
                :placeholder="store_content?.customer_portal?.lbl_confirm_password??'Confirm password'"
                class="form-control ng-untouched ng-pristine ng-valid"
              />
            </div>
            <div class="form-group">
              <button type="button" v-on:click="changePassword"  class="btn theme-btn">{{store_content?.customer_portal?.btn_submit??'Submit'}}</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
module.exports = {
  data() {
    return {
      form_data: {
        old_password: '',
        password: '',
        confirm_password: ''
      },
      error: '',
      store_content: store_content
    };
  },

  methods: {
      changePassword: function(){
        let vm = this;
        vm.error = '';
        let url = rentmy_ajax_object.ajaxurl;
        let data = new FormData();
        data.set("action_type", "change_customer_password");
        data.set("action", "rentmy_options");
        data.set("old_password", vm.form_data.old_password);
        data.set("password", vm.form_data.password);
        data.set("confirm_password", vm.form_data.confirm_password);
        axios.post(url, data).then(function(response){
          if(response.data.status == 'OK'){
            location.reload();
          }
          if(response.data.status == 'NOK'){
            vm.error = response.data.result.message;
          }
        });
      }
  },
  mounted: function () {
    // console.log("Password Reset Mounted");
  },
};
</script>