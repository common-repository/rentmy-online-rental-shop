<template>
  <div class="rentmy-profile-rightside w-100">
    <div class="profile-info-title">
      <h4>{{store_content?.customer_portal?.lbl_profile_info??'Profile Information'}}</h4>
      <button class="pull-right btn profile-edit-btn" v-on:click="activateEdit">
        <i class="fa fa-pencil"></i> {{store_content?.customer_portal?.btn_edit_profile??'Edit Profile'}}
      </button>
    </div>
    <customer-profile-info v-if="is_active.profile_info" ref="profile_info"></customer-profile-info>
    <customer-profile-edit v-if="is_active.profile_edit"></customer-profile-edit>
    <customer-address ></customer-address>

    
  </div>
</template>

<script>
module.exports = {
  components:{
    'customer-profile-info': window.httpVueLoader(rentmy_plugin_base_url+'/assets/js/components/customer/profile/ProfileInfo.vue'),
    'customer-profile-edit': window.httpVueLoader(rentmy_plugin_base_url+'/assets/js/components/customer/profile/ProfileEdit.vue'),
    'customer-address': window.httpVueLoader(rentmy_plugin_base_url+'/assets/js/components/customer/profile/Address.vue')
  },
    data(){
        return{
           is_active:{
             profile_info: true,
             profile_edit: false,
             store_content: store_content
           },
        }
    },
    methods:{
      activateEdit: function(){
        let vm = this;
        vm.is_active.profile_info = false;
        vm.is_active.profile_edit = true;
      }
    },
    created: function(){
    },
    mounted: function(){
     
      // console.log("Profile mounted");
    }
};
</script>