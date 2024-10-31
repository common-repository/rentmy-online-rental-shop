<template>
  <div>
    <div class="btn-group creditcard-pay-btn" role="group" aria-label="Partial Payment">
      <button type="button" :class="'btn '+( full?'paybtn-active':'')" v-on:click="changePaymentAmount(true)">{{rentmy_payment_labels?.btn_pay_in_full??'Pay in Full'}}</button>
      <button type="button" :class="'btn '+( !full?'paybtn-active':'')" v-on:click="changePaymentAmount(false)">{{rentmy_payment_labels?.btn_pay_down_payment??'Pay down payment'}}</button>
    </div>
    <div class="form-group mt-1" v-if="!full">
      <label v-html="bookingText()"></label>
    </div>
    <div class="form-group">
      <label>{{rentmy_payment_labels?.lbl_amount_to_pay??"Amount to Pay"}}</label>
      <input class="form-control" placeholder="Amount to Pay" type="text" v-model="payment_amount" />
    </div>
  </div>
</template>

<script>
module.exports = {
  data() {
    return {
      payment_amount: this.$parent.payment_amount,
      gateway: '',
      full: true,
      rentmy_payment_labels: this.$parent.rentmy_payment_labels
    }
  },
  methods: {
    changePaymentAmount: function (is_full) {
      this.full = is_full
      if (is_full)
        this.payment_amount = this.$parent.cart.total;
      else
        this.payment_amount = this.$parent.cart.booking;
    },
    bookingText: function () {
      let str = this.rentmy_payment_labels?.lbl_minimum_payment;
      let bookingAmount = this.$parent.priceFormat(this.$parent.cart?.booking ?? 0, true);
      return str.replace('%amount%', bookingAmount);
    }
  },
  watch: {
    payment_amount: function (newVal, oldVal) {
      if (newVal !== oldVal) {
        this.$emit('get_payment_amount', newVal)
      }
    }
  },
  mounted() {
    serveBus.$on('loadCartElement', (data) => {
      this.changePaymentAmount(this.full);
    });
    serveBus.$on('taxLookUp', (data) => {
      this.changePaymentAmount(this.full);
    });
  }
}
</script>
