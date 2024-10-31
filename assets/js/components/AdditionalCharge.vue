<template>
  <!-- optional service -->
  <div class="row optional-service pt-4 m-0" v-if="is_additional">
    <div class="additional-charge-title">
      {{ config_labels.cart.lbl_consider_optional_services }}
    </div>

    <div class="rent-my-loader text-center" v-if="loading">
      <i class="fa fa-spin fa-spinner fa-2x"></i>
    </div>

    <div class="col-md-12" v-if="!loading">
      <div class="additional-charges-body">
        <form>
          <!-- optional service -->
          <div class="row optional-service m-0">

            <div class="col-md-12" v-for="(charge, index) in additional_services" :key="index">
              <div class="form-group row" v-if="charge.fee.amounts.length > 1 && charge.status && !charge.is_required">
                <label class="m-checkbox">
                  <input class="service" type="checkbox" :value="charge.id" :checked="charge.is_selected" @change="changeService($event)">
                  {{ charge.description }}
                  <span>&nbsp;&nbsp;</span>
                </label>
                <div class="col-sm-10 optional-service-content">
                  <div class="btn-toolbar"  role="toolbar" aria-label="Toolbar with button groups">
                    <div class="btn-group mr-2" role="group" aria-label="First group">
                      <button type="button" v-for="(amount, k) in charge.fee.amounts" :key="k"  v-on:click="update_additional_services(charge.id, amount)"
                              :class="'btn btn-secondary '+((charge?.existing?.config.user_entered == amount)?'btn-active':'')">
                        {{ (charge.fee.type!="percentage"?currencySymbol:'') + priceFormat(amount, charge.fee.type) + (charge.fee.type=="percentage"?'%':'')}}
                      </button>
                      <button type="button" :class="
                                            'btn btn-secondary input-amount-btn '+(parseFloat(custom_additional_charges[custom_additional_charges.findIndex(x=>x.id==charge.id)].inputed_amount) > 0?'btn-active':'')
                                          " v-on:click="takeInput(charge.id)" v-if="charge.input_custom">
                        Input Amount<span v-if="parseFloat(custom_additional_charges[custom_additional_charges.findIndex(x=>x.id==charge.id)].inputed_amount) > 0">{{
                          "(" +(charge.fee.type != "percentage" ? currencySymbol : "")+
                          priceFormat(custom_additional_charges[
                              custom_additional_charges.findIndex(
                                  (x) => x.id == charge.id
                              )
                              ].inputed_amount, charge.fee.type) +
                          (charge.fee.type == "percentage" ? "%" : "") +
                          ")"
                        }}</span>
                      </button>
                    </div>
                    <select v-if="charge.options != '' && charge.options != null" class="form-control" @change="selectCargeOption($event, charge.id)">
                      <option value="">--Select--</option>
                      <option v-for="opt in charge.options.split(';')" :value="opt" :selected="charge?.existing?.config.selected_option==opt">{{opt}}</option>

                    </select>

                  </div>
                  <div class="input-ammount-area" v-if="custom_additional_charges[custom_additional_charges.findIndex(x=>x.id==charge.id)].is_input">
                    <div class="input-group">
                      <input type="text" class="form-control" aria-describedby="button-addon2" v-model="custom_additional_charges[custom_additional_charges.findIndex(x=>x.id==charge.id)].input">
                      <div class="input-group-append">
                        <button class="btn btn-outline-secondary optional-ok-btn ml-3" type="button" id="button-addon2"
                                v-on:click="saveInputValue(charge.id)">
                          <i class="fa fa-check"></i>
                        </button>
                        <button class="btn btn-outline-secondary optional-cancel-btn ml-3" type="button" id="button-addon2"
                                v-on:click="cancelInput(charge.id)">
                          <i class="fa fa-times"></i>
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="form-group row" v-else>
                <label class="m-checkbox" >
                  <input  type="checkbox" :value="charge.id" class="service" :checked="charge.is_required || charge.is_selected" :disabled="(charge.is_required==0?false:true)" @change="changeService($event)">
                  {{ charge.description }}
                  <span>&nbsp;&nbsp;</span>
                </label>
                <div class="col-sm-10 optional-service-content">
                  <div class="single-optional-service">
                    <label for="" v-if="charge.fee.type == 'percentage'">{{ charge.fee.amounts[0] + "%" }}</label>
                    <label for="" v-else>{{ currencySymbol + priceFormat(charge.fee.amounts[0], charge.fee.type)  }}</label>
                    <div class="btn-toolbar" role="toolbar" aria-label="Toolbar with button groups">
                      <div class="btn-group mr-2" role="group" aria-label="First group">
                        <button type="button" v-if="charge.input_custom && !charge.is_required" :class="
                                                  'btn btn-secondary input-amount-btn '+(parseFloat(custom_additional_charges[custom_additional_charges.findIndex(x=>x.id==charge.id)].inputed_amount) > 0?'btn-active':'')
                                                " v-on:click="takeInput(charge.id)">
                          Input Amount<span v-if="parseFloat(custom_additional_charges[custom_additional_charges.findIndex(x=>x.id==charge.id)].inputed_amount) > 0">{{
                            "(" + (charge.fee.type != "percentage" ? currencySymbol : "")+
                            priceFormat(custom_additional_charges[
                                custom_additional_charges.findIndex(
                                    (x) => x.id == charge.id
                                )
                                ].inputed_amount, charge.fee.type) +
                            (charge.fee.type == "percentage" ? "%" : "") +
                            ")"
                          }}</span>
                        </button>
                        <select v-if="charge.options != '' && charge.options != null" class="form-control" @change="selectCargeOption($event, charge.id)">
                          <option value="">--Select--</option>
                          <option v-for="opt in charge.options.split(';')" :value="opt" :selected="charge?.existing?.config.selected_option==opt">{{opt}}</option>

                        </select>
                      </div>
                    </div>
                  </div>
                  <div class="input-ammount-area" v-if="custom_additional_charges[custom_additional_charges.findIndex(x=>x.id==charge.id)].is_input">
                    <div class="input-group">
                      <input type="text" class="form-control" aria-describedby="button-addon2" v-model="custom_additional_charges[custom_additional_charges.findIndex(x=>x.id==charge.id)].input"/>
                      <div class="input-group-append">
                        <button class="btn btn-outline-secondary optional-ok-btn ml-3" type="button" id="button-addon2" v-on:click="saveInputValue(charge.id)"
                        >
                          <i class="fa fa-check"></i>
                        </button>
                        <button class="btn btn-outline-secondary optional-cancel-btn ml-3" type="button" id="button-addon2"
                        v-on:click="cancelInput(charge.id)">
                          <i class="fa fa-times"></i>
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

            </div>

          </div>
      </div>

      <!-- <div class="form-group row">
        <label class="col-sm-2 col-form-label">Gift Wrapping</label>
        <div class="col-sm-10 optional-service-content">
          <label class="m-checkbox">
            <input type="checkbox" />
            2.99 USD
            <span>&nbsp;&nbsp;</span>
          </label>
        </div>
      </div> -->
      <!-- <div class="form-group row">
        <label class="col-sm-2 col-form-label">Expedited Shipping</label>
        <div class="col-sm-10 optional-service-content">
          <label class="m-checkbox">
            <input type="checkbox" />
            5.00 USD
            <span>&nbsp;&nbsp;</span>
          </label>
        </div>
      </div> -->

    </div>
  </div>

</template>
<script>
module.exports = {
  data(){
    return{
      config_labels: config_labels,
      additional_services:[],
      cartable_additional_services:[],
      currencySymbol: rentmy_config_data_preloaded.currency_format.symbol,
      custom_additional_charges:[],
      loading: false,
      is_additional: false,
      store_config: store_config
    }
  },
  methods:{
    selectCargeOption: function (event, id){
      let value = event.target.value;
      this.set_additional_services_option(id, value);
    },
    changeService: function (event){
      let ref = this;
      ref.loading = true;
      let id = event.target.value;
      if(event.target.checked){
        let amount = ref.additional_services[ref.additional_services.findIndex(x=>x.id==id)].fee.amounts[0];
        ref.update_additional_services(id, amount);
      }else{
        ref.remove_additional_service_from_cart(id);
      }
    },
    takeInput: function (id){
      let ref = this;
      ref.custom_additional_charges[ref.custom_additional_charges.findIndex(x=>x.id==id)].is_input = true;
    },
    cancelInput: function (id){
      let ref = this;
      ref.custom_additional_charges[ref.custom_additional_charges.findIndex(x=>x.id==id)].is_input = false;
    },
    saveInputValue: function (id){
      let ref = this;
      let amount = ref.custom_additional_charges[ref.custom_additional_charges.findIndex(x=>x.id==id)].input;
      if (amount <= 0)
        return;
      ref.update_additional_services(id, amount);
      ref.cancelInput(id);
    },
    priceFormat: function (amount, type='') {
      // return parseFloat(priceVal).toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,');
      if (type == 'percentage'){
        return amount;
      }
      amount = parseFloat(amount);
      if (isNaN(amount)){
        return;
      }
      amount = new Number(amount);
      var myObj = { style: 'currency', currency: 'USD' };
      let currency_config = this.store_config.currency_format;
      let symbol = currency_config.symbol?currency_config.symbol:'$';

      let locale = currency_config.locale;
      let amountStr = amount;
      if (locale){
        amountStr = Number(amount).toLocaleString(locale, myObj);
        amountStr = amountStr.replace('US', '');
        amountStr = amountStr.replace('$', '');
      }

      return amountStr;
    },

    additionalChargeList: function () {
      let ref = this;
      // ref.loading = true;
      let data = new FormData();
      data.set("action", "rentmy_options");
      data.set("action_type", "additional_charges");
      axios.post(rentmy_ajax_object.ajaxurl, data).then(function (response) {

        ref.additional_services = response.data.data;
        if (ref.additional_services.length > 0){
          ref.is_additional = true;
        }

        ref.cartable_additional_services = [];
        ref.custom_additional_charges = [];
        ref.additional_services.forEach(service=>{
          ref.cartable_additional_services.push({
            id: service.id,
            is_selected: service.is_selected,
            value: service.existing?service.existing.config.user_entered:'',
            order_additional_charge_id: service.existing?service.existing.id:null,
            selected_option: service.existing?service.existing.config.selected_option:null,
          });

          let custom_single = {
            id: service.id,
            is_input: false,
            input:'',
            inputed_amount:service.existing?service.existing.config.user_entered:'',
          };
          let is_matched = false;
          service.fee.amounts.forEach(amount =>{
            if (amount == service.existing?.config.user_entered){
              custom_single['inputed_amount'] = '';
              is_matched = true;
            }
          });
          if (!is_matched){
            custom_single['input'] = service.existing?service.existing.config.user_entered:'';
          }
          ref.custom_additional_charges.push(custom_single)
        });

        ref.loading = false;
      });

    },


    update_additional_services: function(service_id, charge_amount=''){
      let ref = this;
      ref.loading = true;
      this.cartable_additional_services.map(function (service){
        if(service.id == service_id){
          let service_data = ref.additional_services.filter(additional_service=>{
            return additional_service.id == service_id;
          });
          service.value = charge_amount!=''?charge_amount:service_data[0].fee.amounts[0];
          service.is_selected = true;
        }
      });
      this.additionalChargeToCart();
    },

    set_additional_services_option: function(service_id, option_value){
      this.cartable_additional_services.map(function (service){
        if(service.id == service_id){
          service.selected_option = option_value;
        }
      });
      this.additionalChargeToCart();
    },
    remove_additional_service_from_cart: function(service_id){
      this.cartable_additional_services.map(function (service){
        if(service.id == service_id){
          return service.is_selected = false;
        }
      });
      this.additionalChargeToCart();
    },

    additionalChargeToCart: function(){
      let vm = this;
      let data = new FormData();
      data.set("action", "rentmy_options");
      data.set("action_type", "add_additional_charges_to_cart");
      data.set("additional_charges", JSON.stringify(vm.cartable_additional_services, null, '\t'));
      axios.post(rentmy_ajax_object.ajaxurl, data).then(function (response) {

        vm.additionalChargeList();
        serveBus.$emit('additionalChargeToCart', response.data.data);
        vm.$parent.loading = false;
      });
    },

    // orderAdditionalCharges: function(){
    //   let vm = this;
    //   let data = new FormData();
    //   data.set("action", "rentmy_options");
    //   data.set("action_type", "cart_additional_charges");
    //   data.set("order_id", this.cart_id);
    //   axios.post(rentmy_ajax_object.ajaxurl, data).then(function (response) {
    //     ref.cartable_additional_services = [];
    //     ref.additional_services.forEach(service=>{
    //       ref.cartable_additional_services.push({
    //         id: service.id,
    //         is_selected: service.is_selected,
    //         value: service.existing?service.existing.config.user_entered:'',
    //         order_additional_charge_id: service.existing?service.existing.id:null,
    //         selected_option: service.existing?service.existing.config.selected_option:null,
    //       });
    //     });
    //   });
    // },

  },
  created: function (){
    this.additionalChargeList();
  }
}
</script>