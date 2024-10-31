<template>
  <div>
    <form id="payment-form">

      <div id="card-container"></div>

      <button id="card-button" type="button" ref="card_button" @click="eventHandler(event)" style="visibility: hidden">Pay</button>

    </form>
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
      card: ''
    };
  },
  watch: {
    showPaymentForm: function() {
      if (!this.showPaymentForm) {
        return 1;
      }
      this.paymentForm.build();
    }
  },
  props: {
    showPaymentForm: Boolean,
    id: Number,
    gateway: Object
  },
  mounted: async function() {
    let ref = this;
    const config = this.gateway.config;
    const payments = Square.payments(config.access_key, config.location_id);

    ref.card = await payments.card();

    ref.card.attach('#card-container');

    serveBus.$on('accessSquareToken', (isChange) => {
      if (isChange){
        this.$refs.card_button.click()
      }
    })
  },
  methods: {
    onGetCardNonce: function (event) {
        event.preventDefault();
        this.paymentForm.requestCardNonce();
  },
    eventHandler: async function(event) {
    ref = this;
      event.preventDefault();



      try {

        const result = await this.card.tokenize();

        if (result.status === 'OK') {
          ref.payment_token = result.token
          await ref.$emit('get_token', ref.payment_token)
        }

      } catch (e) {

        console.error(e);

      }

    },
    generateToken: function (){

    }
  }
};
</script>
