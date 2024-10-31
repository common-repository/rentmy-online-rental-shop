<template>
  <div>
    <div id="iframe-container">
    </div>
    <div></div>
    <button type="submit" ref="card_button" @click="submitPaymentInfo()" class="btn theme-btn" style="visibility: hidden"> Submit</button>
    <p v-if="cardErrors" class="mt-3" style="color: red;">{{ cardErrors }}</p>
  </div>
</template>

<script>
module.exports = {
  data: function() {
    return {
      errors: [],
      masterpass: false,
      applePay: false,
      paymentForm: '',
      payment_token: '',
      card: '',
      transafe_url: '',
      transafe_base_url:'https://post.live.transafe.com:443',
      cardErrors:'',
      paymentFrame:{}
    };
  },
  watch: {

  },
  props: {
    showPaymentForm: Boolean,
    id: Number,
    gateway: Object,
    attr_data:{}
  },
  mounted: async function() {
    let ref = this;

    await this.setApiUrl();
    this.loadExternalScript().then(res => {
      this.getIframe().then(response => {
        let result = response?.data?.result
        if(result){
          this.setIframe(result.data['attributes_str'], result.data['host']);
        }
      });

    })

    serveBus.$on('accessTransafeToken', (isChange) => {
      if (isChange){
        this.$refs.card_button.click()
      }
    })
  },
  methods: {
    setApiUrl(){
      const config = this.gateway.config;
      if (config && config.payment_server_base_url){
        this.transafe_base_url = config.payment_server_base_url;
      }else if (config && (config.is_live == 'false')){
        this.transafe_base_url = 'https://test.transafe.com';
      }
    },
    submitPayment() {
      let ref = this;
      this.cardErrors = '';
      return new Promise((resolve, reject)=>{
        serveBus.$emit('transafeError', false);
        let loader = true;
        this.paymentFrame.setPaymentSubmittedCallback((response) => {
          loader = false;
          if (response.code === 'AUTH') {
            let transafeData = {
              payment_gateway_name: "Transafe",
              account: response['ticket']
            }
            resolve(transafeData);
          } else {
            serveBus.$emit('transafeError', true);
            if (response.verbiage){
              this.cardErrors = response.verbiage;
            }else{
              this.cardErrors = "Card payment is not success, Please try again !";
            }

            reject(null);
            this.paymentFrame.enableSubmitButton();
          }
        });
          this.paymentFrame.submitPaymentData();

          if (loader){
            serveBus.$emit('transafeError', true);
          }


      });

    },

    async submitPaymentInfo(capture) {
      let ref = this;
      await this.submitPayment()
          .then((res)=>{
            if(capture) {
              res['capture'] = capture;
            }
            res['source'] = 'wp';
            ref.$emit('get_token', res.account)
          })
          .catch((err)=>{
            serveBus.$emit('transafeError', true);
          })
    },

    getIframe: function () {

      return new Promise((resolve, reject)=>{
        const {
          host, hostname, href, origin, pathname, port, protocol, search
        } = window.location
        let ajaxdata = new FormData();
        ajaxdata.set("action", "rentmy_options");
        ajaxdata.set("action_type", "transafe_iframe_attr");
        ajaxdata.set("client_host", origin);

        axios.post(rentmy_ajax_object.ajaxurl, ajaxdata)
            .then(function (response){
              resolve(response);
            })
            .catch((err)=>{
              reject(err);
            })
      })

      let ref = this;

    },

    stringToHTML(str) {
      var parser = new DOMParser();
      var doc = parser.parseFromString(str, 'text/html');
      return doc.body.childNodes[0];
    },

    async setIframe(iframe,host) {

      let iframe_text = `<iframe id="iframe" ${iframe}></iframe>`;
      let container = document.getElementById('iframe-container');
      container.appendChild(this.stringToHTML(iframe_text));
      await this.createPaymentForm(host)
    },

    async createPaymentForm(host){

      this.paymentFrame = await new PaymentFrame("iframe",`${host}`);

      await this.paymentFrame.request();

    },

    loadExternalScript() {
      this.transafe_url = `${this.transafe_base_url}/PaymentFrame/PaymentFrame.js`;
      return new Promise((resolve, reject) => {
        if(!document.getElementById('transafejs')) {
          const scriptElement = document.createElement("script");
          scriptElement.src = this.transafe_url;
          scriptElement.onload = resolve;
          scriptElement.id = "transafejs";
          document.body.appendChild(scriptElement);
        } else {
          resolve(()=>{
            return true;
          })
        }
      });
    }

  }
};
</script>

<style>
#iframe-container{
  height: auto !important;
}

#iframe-container iframe{
    min-height: 256px !important;
  border: 1px #7e7f86 !important;
}

</style>
