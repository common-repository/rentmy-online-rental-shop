<template>
  <div class="rentmy-profile-body">
    <div class="rentmy-profile-itemlist rentmy-profile-itemlist-100">
      <div class="profile-info">
        <div class="rentmy-profile-item mb-4">
          <h2>{{ profile_info.first_name + " " + profile_info.last_name }}</h2>
          <div class="rentmy-profile-item-info profile-email">
            {{ profile_info.email }}
          </div>
        </div>
        <div class="rentmy-profile-item">
          <div class="rentmy-profile-item-info">
            <i class="fa fa-building mr-2"></i>{{ profile_info.company }}
          </div>
        </div>
        <div class="rentmy-profile-item">
          <div class="rentmy-profile-item-info">
            <i class="fa fa-phone mr-2"></i>{{ profile_info.mobile }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
module.exports =  {
    data(){
         return{
            profile_info: {},
         }
    },
    methods: {
      getCustomerProfile: function(){
        let vm = this;
        let url = rentmy_ajax_object.ajaxurl;
        let data = new FormData();
        data.set("action_type", "get_customer_profile");
        data.set("action", "rentmy_options");
        axios.post(url, data).then(function(response){
          if(response.status == 200){
            vm.profile_info = response.data.data;

            customerBus.$emit("getCustomerProfile", false);
          }
        });
      },
    },
    created: function(){
        this.getCustomerProfile();
    }
}
</script>