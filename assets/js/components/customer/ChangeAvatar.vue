<template>
  <div class="rentmy-profile-rightside w-100">
    <div class="profile-info-title">
      <h4>Change Avatar</h4>

    </div>
    <div class="rentmy-profile-body profile-body">
            <span class="text-danger" v-if="msg.error!= ''">{{ msg.error }}</span>
      <span class="text-success" v-if="msg.success != ''">{{ msg.success }}</span>
      <div class="animated fadeIn change-avatar">
        <label class="avatar-label">
          Upload Image <small>(Maximum file size 2MB)</small></label
        >
        <div class="custom-alert"></div>
        <div class="row">

          <form @submit.prevent="uploadAvatar" method="POST" enctype="multipart/form-data">

          <div class="col-sm-6">
            <div appdragdrop="" class="drop">
              <div class="cont">
                <i class="fa fa-cloud-upload"></i>
                <div class="tit">Drop files here or click to upload.</div>
                <div class="desc">Maximium size limit 2 MB</div>
              </div>
              <input
                type="file"
                id="images"
                name="file"
                class="form-control"
                accept="image/*"
              />
            </div>
            <div class="text-center upload-btn" style="margin: 20px">
              <div>
                <button
                  type="submit"
                  class="btn btn-sm btn-brand btn-primary"
                  style="margin-right: 10px"
                >
                  <i class="fa fa-upload"></i
                  ><span style="padding-left: 5px">Upload</span>
                </button>
                <button type="button" class="btn btn-sm btn-danger">
                  <i class="fa fa-refresh"></i
                  ><span style="padding-left: 5px">Reset</span>
                </button>
              </div>
            </div>
          </div>
          <div class="image-container col-sm-6"></div>
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
      msg:{
        error:'',
        success:''
      }
    };
  },
  methods:{
    uploadAvatar: function(event){
      let vm = this;
      let url = rentmy_ajax_object.ajaxurl;
      let data = new FormData(event.target);
      data.set("action_type", "change_customer_avatar");
      data.set("action", "rentmy_options");
      axios.post(url, data).then(function(response){
        if(response.data.status == 'NOK'){
          vm.msg.error = response.data.result.message;
        }else{
          vm.msg.success = "Avatar Changed";
        }
      });
    }
  },
  mounted: function () {
    console.log("Change Avatar Mounted");
  },
};
</script>