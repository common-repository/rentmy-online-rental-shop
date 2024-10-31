var rm_single_product;
jQuery(function ($) {
    rm_single_product = {
        rental_type: 'buy',
        type: 1,
        product_id: '',
        cart_item_id: '',
        cart_item_quantity: '',
        cart_item_price: '',
        variants_products_id: '',
        price: 0,
        initial_buy_price: 0,
        initial_rent_price: 0,
        total_price: 0,
        quantity: 1,
        available: 0,
        available_for_sale: 0,
        show_rental_option: false,
        show_buy: true,
        show_buy_price: true,
        is_exact_date: false,
        config: rentmy_config_data_preloaded,
        coupon_code: '',
        term: 1,
        rent_start: '',
        rent_end: '',
        cart_item_option_id: '',
        delivery_flow: '',
        cart_token: '',
        recurring: false,
        config_labels: (typeof config_labels == 'undefined')?{}:config_labels,
        dateFormatInitial: function () {

            if (typeof this.config.date_format == 'undefined' || this.config.date_format == '') {
                this.config.date_format = 'MM-DD-YYYY';
            }

            let allFormatsDate = {
                'MM/DD/YYYY': 'MM/DD/YYYY',
                'DD/MM/YYYY': 'DD/MM/YYYY',
                'DD MMM YYYY': 'DD MMM YYYY',
                'YYYY-MM-DD': 'YYYY-MM-DD',
                'MM-DD-YYYY': 'MM-DD-YYYY'
            };
            let format = allFormatsDate[this.config.date_format];
            if (this.config.show_start_time){
                format += ' hh:mm A'
            }
            return format;
        },
        selectedDuration: '',
        selectedTime: '',
        init: function () {
            this.load_configuration();
            this.load_default_values();
           this.addon_product_data();
        },
        load_default_values() {
            $('.rm-rental-daterange').show();
            // buy and rent both type
            if ($('#rentmy-base-buy-price').val() > 0 && $('#rentmy-base-rent-price').val() > 0) {
                this.price = $('#rentmy-base-buy-price').val();
                if (this.price == 0) {
                    this.price = $('#rentmy-base-rent-price').val();
                }
                this.initial_buy_price = $('#rentmy-base-buy-price').val();
                this.initial_rent_price = $('#rentmy-base-rent-price').val();
                this.rental_type = 'rent';

                if ($('#rentmy_available_for_sale').length){
                    this.available_for_sale = $('#rentmy_available_for_sale').val();
                }

                if ($('#rentmy_available').length){
                    this.available = $('#rentmy_available').val();
                }


                $('#rental_type_rent').trigger('click');
            }

            // buy only
            else if ($('#rental_type_buy').is(':checked')) {
                this.price = $('#rentmy-base-buy-price').val();
                this.initial_buy_price = this.price;
                this.rental_type = 'buy';
                if ($('#rentmy_available_for_sale').length){
                    this.available_for_sale = $('#rentmy_available_for_sale').val();
                }
                $('#rental_type_buy').trigger('click');
            }

            // rent only
            else if ($('#rental_type_rent').is(':checked')) {
                this.price = $('#rentmy-base-rent-price').val();
                this.initial_rent_price = this.price;
                this.rental_type = 'rent';
                if ($('#rentmy_available').length){
                    this.available = $('#rentmy_available').val();
                }
                $('#rental_type_rent').trigger('click');
                $('.price-options label:first-child').trigger('click');
            }
            else{
                $('.rm-rental-daterange').hide();
                $('.add_to_cart_button').attr('disabled', 'disabled');
            }

            // quantity and product id set
            this.quantity = 1;
            this.product_id = $('#rm_pd_product_id').val();
            this.variants_products_id = $('#rm_v_products_id').val();
            $('#rm_quantity').val(this.quantity);

            // setting start date and end date for the rent products and exact start date
            this.rent_start = $('#exact-rent-start').val() || '';
            this.rent_end = $('#exact-rent-end').val() || '';
            if ($('#exact-rent-start').length > 0 && $('#exact-rent-end').length > 0) {
                if ($('#exact-rent-start').val() != '' || $('#exact-rent-end').val() != '') {
                    this.is_exact_date = true;
                }
            }
            this.recurring = false;
            if (typeof isEnduring != 'undefined'){
                this.recurring = (isEnduring && (isEnduring==1))?true:false;
            }

            // ends
        },
        load_configuration() {
            this.init_daterange_picker(this.config);
            this.init_exact_time_picker(this.config);
            this.checked_rent_buy_radio('', true);
            this.update_cart_topbar();
            this.delivery_option();
            $(document).ready(function(){
                $('.rentmy-custom-fields select:first').trigger('change');
            });
        },
        delivery_option: function (){
            this.delivery_flow = localStorage.getItem('deliveryFlow');
            if (this.delivery_flow){
                $('.rm-delivery-option ul li').attr('disabled', true);
                $('.rm-delivery-option ul li').addClass('rentmy-disable');
            }else{
               if ($('.rm-delivery-option').length){
                   $('.rm-delivery-option ul li:first').trigger('click');
               }
            }

        },

        get_price_value: function (isPriceUpdate=true, custom_fields=[]) {
            let exact_time = $("#is_exact_time").val();
            if (exact_time) {
                return;
            }
            let price_id = $("input[name='rental-price']:checked").attr('data-price_id');
            let data = {
                action: 'rentmy_options',
                action_type: 'get_price_value',
                data: {
                    product_id: this.product_id,
                    quantity: this.quantity,
                    variants_products_id: this.variants_products_id,
                    rent_start: this.rent_start,
                    rent_end: this.rent_end,
                    rental_type: this.rental_type,
                    price_id: price_id
                }
            };


            if (!custom_fields.length){

                custom_fields = $("select[name='customFields[]']")
                    .map(function(){
                        if ($(this).val() != ''){
                            let data = $(this).val().split("%rentmy%").join(" ");
                            return JSON.parse(data);
                        }

                    }).get();
            }

            if (custom_fields.length > 0){
                data.data['custom_fields'] = custom_fields;
                isPriceUpdate = true;
            }

            if (this.rental_type == 'buy') {
                data.data.rent_start = '';
                data.data.rent_end = '';

            } else {
                if (!data.data.rent_start){
                    if ($('#rm-date').length > 0) {
                        if ($('#rm-date').attr('disabled')) {
                            data.data.rent_start = moment($('#rm-date').attr('data-rent-start')).format('YYYY-MM-DD HH:mm');
                            data.data.rent_end = moment($('#rm-date').attr('data-rent-end')).format('YYYY-MM-DD HH:mm');
                        } else {
                            data.data.rent_start = $('#rm-date').data('daterangepicker').startDate.format('YYYY-MM-DD HH:mm');
                            data.data.rent_end = $('#rm-date').data('daterangepicker').endDate.format('YYYY-MM-DD HH:mm');
                        }
                    }
                }
            }
            jQuery.post(rentmy_ajax_object.ajaxurl, data, function (response) {
                if (response.status == 'OK') {
                    rm_single_product.available = response.result.available;
                    rm_single_product.price = response.result.data;
                    $('.availability-count').html(rm_single_product.available);
                    // $('div.price h6 .amount').html(rm_single_product.priceFormat(rm_single_product.price));
                    let symbolPrice = rm_single_product.config.currency_format.symbol;
                    let qty = rm_single_product.quantity?rm_single_product.quantity:1;
                    if (isPriceUpdate){
                        $('.price .rent h6').html(
                            $(
                                '<span class="pre">' + symbolPrice + '</span><span class="amount">' + rm_single_product.priceFormat(rm_single_product.price * qty) + '</span>'
                            )
                        );
                        $('.price .buy h6').html(
                            $(
                                '<span class="pre">' + symbolPrice + '</span><span class="amount">' + rm_single_product.priceFormat(rm_single_product.price * qty) + '</span>'
                            )
                        );

                    }

                    if (response.result.available > 0) {
                        $('#rentmy-rent-item').prop('disabled', false);
                        $('.rentmy-unavailability-msg').html('');
                    } else {
                        $('#rentmy-rent-item').prop('disabled', true);
                        $txt = config_labels?.product_details?.not_available_text??'This product is not available';
                        $('.rentmy-unavailability-msg').html($txt);

                    }


                    if (typeof response.result.start_date != 'undefined') {
                        rm_single_product.rent_start = response.result.start_date;
                    }
                    if (typeof response.result.end_date != 'undefined') {
                        rm_single_product.rent_end = response.result.end_date;
                    }

                    if (($('#rm-date').length > 0) && (typeof response.result.start_date != 'undefined') && (typeof response.result.end_date != 'undefined')) {
                        if ($('#rm-date').attr('disabled')) {
                            $('#rm-date').attr('data-rent-start', rm_single_product.rent_start);
                            $('#rm-date').attr('data-rent-end', rm_single_product.rent_end);
                        } else {
                            let dateRange = $('.daterange');
                            let dateFormat = rm_single_product.dateFormatInitial();
                            if (dateRange.length){
                                dateRange.data('daterangepicker').setStartDate(moment(rm_single_product.rent_start).format(dateFormat));
                                dateRange.data('daterangepicker').setEndDate(moment(rm_single_product.rent_end).format(dateFormat));
                            }

                        }
                    }


                }
                if (response.result.error || response.result.message) {
                    toastr.warning(response.result.error || response.result.message);
                }
            }).fail(function (response) {
                toastr.error(response);
            });
        },
        get_package_value: function () {
            var products = [];
            $('.package-items li').each(function (i, item) {
                //test
                var package_item = {
                    product_id: $(this).children('h6').attr('data-id'),
                    variants_products_id: $(this).find(".package_variant").val()
                };
                products.push(package_item)
            });
            let custom_fields = $("select[name='customFields[]']").map(function(){
                if ($(this).val() != ''){
                    let data = $(this).val().split("%rentmy%").join(" ");
                    return JSON.parse(data);
                }
                }).get();

            let price_id = $("input[name='rental-price']:checked").attr('data-price_id');
            let data = {
                action: 'rentmy_options',
                action_type: 'get_package_value',
                data: {
                    package_id: this.product_id,
                    quantity: this.quantity,
                    variants_products_id: this.variants_products_id,
                    rent_start: this.rent_start,
                    rent_end: this.rent_end,
                    products: products,
                    custom_fields: custom_fields,
                    price_id: price_id,
                    terms: this.terms,
                    rental_type: this.rental_type
                }
            };

            jQuery.post(rentmy_ajax_object.ajaxurl, data, function (response) {
                if (response.status == 'OK') {
                    rm_single_product.available = response.result.term;
                    rm_single_product.price = response.result.data * data.data.quantity;
                    $('.availability-count').html(rm_single_product.available);
                    // $('div.price h6 .amount').html(rm_single_product.priceFormat(rm_single_product.price));
                    let symbolPrice = rm_single_product.config.currency_format.symbol;
                    $('.price .rent h6').html(
                        $(
                            '<span class="pre">' + symbolPrice + '</span><span class="amount">' + rm_single_product.priceFormat(rm_single_product.price) + '</span>'
                        )
                    );

                    if (rm_single_product.available <= 0){
                        $txt = config_labels?.product_details?.not_available_package_text??'This product is not available';
                        $('.rentmy-unavailability-msg').html($txt);
                    }else{
                        $('.rentmy-unavailability-msg').html('');
                    }

                    if (typeof response.result.start_date != 'undefined') {
                        rm_single_product.rent_start = response.result.start_date;
                    }
                    if (typeof response.result.end_date != 'undefined') {
                        rm_single_product.rent_end = response.result.end_date;
                    }

                    if ($('#rm-date').length > 0) {
                        if ($('#rm-date').attr('disabled')) {
                            $('#rm-date').attr('data-rent-start', rm_single_product.rent_start);
                            $('#rm-date').attr('data-rent-end', rm_single_product.rent_end);
                        } else {
                            let dateRange = $('.daterange');
                            let dateFormat = rm_single_product.dateFormatInitial();
                            if (dateRange.length){
                                dateRange.data('daterangepicker').setStartDate(moment(rm_single_product.rent_start).format(dateFormat));
                                dateRange.data('daterangepicker').setEndDate(moment(rm_single_product.rent_end).format(dateFormat));
                            }

                        }
                    }
                }
                if (response.result.error || response.result.message) {
                    toastr.warning(response.result.error || response.result.message);
                }
            }).fail(function (response) {
                toastr.error(response);
            });
        },
        init_exact_time_picker: function (configurations) {
            if (configurations.datetime == null) {
                return;
            }
            let exact_time = $("#is_exact_time").val();
            if (exact_time) {
                let dateFormat = 'YYYY-MM-DD';
                let dateRange = $('.single-date-range');
                let dateConfigureObj = {
                    opens: 'right',
                    timePicker: false,
                    singleDatePicker: true,
                    locale: {
                        format: 'MM-DD-YYYY'
                    },
                };

                dateRange.daterangepicker(dateConfigureObj, function (start, end, label) {
                    //rm_single_product.rent_start = start.format(dateConfigureObj.locale.format);
                    rm_single_product.rent_start = start.format(dateFormat);
                    rm_single_product.get_exact_duration();
                });
            }
        },
        convertDateFormat: function (usDate, outputFormat) {
            let outPut = moment(usDate, 'MM-DD-YYYY hh:mm A').format(outputFormat);
            if (outPut == 'Invalid date') {
                moment(usDate).format(outputFormat);
            } else {
                return outPut;
            }
            return outPut;
        },
        get_exact_duration: function () {
            let dateFormat = 'YYYY-MM-DD';
            let data = {
                action: 'rentmy_options',
                action_type: 'get_exact_duration',
                data: {
                    //start_date: moment(this.rent_start).format(dateFormat),
                    start_date: this.rent_start,
                }
            };


            // firefox only date format fixes
            if (typeof ($.browser) != 'undefined' && $.browser.mozilla === true) {
                data.data.start_date = this.convertDateFormat(this.rent_start, dateFormat);
            }

            let singleDateRange = $('.single-date-range');
            let durationTime = $('.exact-date-wrapper #duration');
            let exactTime = $('.exact-date-wrapper #exact_time');
            let addToCartBtn = $('#rentmy-rent-item');

            let makePickerDisable = function (boolFlag) {
                if (boolFlag) {
                    singleDateRange.attr('disabled', boolFlag);
                    durationTime.attr('disabled', boolFlag);
                    if (exactTime.length > 0) {
                        exactTime.attr('disabled', boolFlag);
                    }
                    addToCartBtn.attr('disabled', boolFlag);
                } else {
                    singleDateRange.attr('disabled', boolFlag);
                    durationTime.attr('disabled', boolFlag);
                    if (exactTime.length > 0) {
                        exactTime.attr('disabled', boolFlag);
                    }
                    addToCartBtn.attr('disabled', boolFlag);
                }
            };

            let generateDynamicSelection = function (response) {
                if (response.durations.length > 0) {
                    let duration = response.durations;
                    durationTime.html($('<option value="">-Select-</option>'));

                    for (var key in duration) {
                        let chk_selected = '';
                        if (rm_single_product.selectedDuration != '') {
                            if (rm_single_product.selectedDuration == duration[key].value) {
                                chk_selected = 'selected';
                            }
                        } else {
                            chk_selected = '';
                        }
                        durationTime.append($('<option ' + chk_selected + ' data-type="' + duration[key].type + '" data-label="' + duration[key].label + '" value="' + duration[key].value + '">' + duration[key].label + '</option>'));
                    }
                }

                if (exactTime.length > 0) {
                    if (response.times.length > 0) {
                        let times = response.times;
                        exactTime.html($('<option value="">-Select-</option>'));
                        for (var key in times) {
                            exactTime.append($('<option value="' + times[key] + '">' + times[key] + '</option>'));
                        }
                    }
                }
            };

            makePickerDisable(true);
            jQuery.post(rentmy_ajax_object.ajaxurl, data, function (response) {
                generateDynamicSelection(response);
                makePickerDisable(false);

            }).fail(function (response) {
                toastr.error('Something went wrong. Please try again.');
                makePickerDisable(false);
            });
        },
        get_dates_from_duration: function () {
            let singleDateRange = $('.single-date-range');
            let durationTime = $('.exact-date-wrapper #duration');
            let exactTime = $('.exact-date-wrapper #exact_time');
            let addToCartBtn = $('#rentmy-rent-item');
            let dateFormat = 'YYYY-MM-DD';
            let symbolPrice = rm_single_product.config.currency_format.symbol;
            let timeSelection = $('.time-selection .timeButton:checked');
            if (timeSelection.length > 0) {
                exactTime = timeSelection;
            }
            let slotSelection = $('.slot-selection .slotButton:checked');
            if (slotSelection.length > 0) {
                durationTime = slotSelection;
            }
            if (durationTime.val() == '') {
                return;
            }
            if (exactTime.length > 0) {
                if (exactTime.val() == '') {
                    return;
                }
            }

            if (!this.product_id){
                this.product_id = $('#rm_pd_product_id').val();
            }

            if (!this.variants_products_id){
                this.variants_products_id = $('#rm_v_products_id').val();
            }

            if (this.rent_start == '') {
                if (typeof ($.browser) != 'undefined' && $.browser.mozilla === true) {
                    this.rent_start = this.convertDateFormat(singleDateRange.val(), dateFormat);
                } else {
                    this.rent_start = moment(singleDateRange.val()).format(dateFormat);
                }
            }

            if (this.rent_start == 'Invalid date') {
                this.rent_start = $('#single-date-range').val() || '';
            }

            let data = {
                action: 'rentmy_options',
                action_type: 'get_dates_from_duration',
                data: {
                    product_id: parseInt(this.product_id),
                    variants_products_id: parseInt(this.variants_products_id),
                    start_date: moment(this.rent_start).format(dateFormat),
                    duration: $.trim(durationTime.val()),
                    type: $.trim(durationTime.find('option:selected').attr('data-type'))
                }
            };
            if (durationTime.attr('data-type')) {
                data.data.type = durationTime.attr('data-type');
            }

            let custom_fields = $("select[name='customFields[]']")
                .map(function(){
                    if ($(this).val() != ''){
                        let data = $(this).val().split("%rentmy%").join(" ");
                        return JSON.parse(data);
                    }

                }).get();
            if (custom_fields){
                data.data.custom_fields = custom_fields;
            }

            var products = [];
            $('.package-items li').each(function (i, item) {
                //test
                var package_item = {
                    product_id: $(this).children('h6').attr('data-id'),
                    quantity: $(this).children('h6').attr('data-quantity'),
                    variants_products_id: $(this).find(".package_variant").val()
                };
                products.push(package_item)
            });
            data.data.products = products;

            // firefox only date format fixes
            if (typeof ($.browser) != 'undefined' && $.browser.mozilla === true) {
                data.data.start_date = this.convertDateFormat(this.rent_start, dateFormat);
            }

            if (exactTime.length > 0) {
                data.data.start_time = $.trim(exactTime.val());
            }

            let makePickerDisable = function (boolFlag) {
                if (boolFlag) {
                    singleDateRange.attr('disabled', boolFlag);
                    durationTime.attr('disabled', boolFlag);
                    if (exactTime.length > 0) {
                        exactTime.attr('disabled', boolFlag);
                    }
                    addToCartBtn.attr('disabled', boolFlag);
                } else {
                    singleDateRange.attr('disabled', boolFlag);
                    durationTime.attr('disabled', boolFlag);
                    if (exactTime.length > 0) {
                        exactTime.attr('disabled', boolFlag);
                    }
                    addToCartBtn.attr('disabled', boolFlag);
                }
            };

            makePickerDisable(true);
            jQuery.post(rentmy_ajax_object.ajaxurl, data, function (response) {
                if (typeof response.start_date != 'undefined') {
                    rm_single_product.rent_start = response.start_date;
                }
                if (typeof response.end_date != 'undefined') {
                    rm_single_product.rent_end = response.end_date;
                }
                if (typeof response.price != 'undefined') {
                    rm_single_product.price = response.price;
                    let price_v_quantity = response.price * rm_single_product.quantity;

                    let price_label = durationTime.find(':selected').attr('data-label');
                    if (typeof price_label != 'undefined') {
                        price_label = ' for ' + price_label;
                    } else {
                        price_label = '';
                    }

                    let priceString = '<span class="pre">' + symbolPrice + '</span><span class="amount">' + rm_single_product.priceFormat(price_v_quantity) + '</span>' + '<span>' + price_label + '</span>';
                    $('.price .rent h6').html($(priceString));
                    $('.price-pro .rent').html($(priceString));
                    $('.availability-count').html(response.available);
                }
                makePickerDisable(false);
                if (response.available > 0) {
                    addToCartBtn.show();
                    $('#not-available').hide();
                } else {
                    addToCartBtn.hide();
                    $('#not-available').show();
                }

                rm_single_product.selectedDuration = parseInt(durationTime.val());

            }).fail(function (response) {
                toastr.error('Something went wrong. Please try again.');
                makePickerDisable(false);
            });
        },
        init_daterange_picker: function (configurations) {
            let dateRange = $('.daterange');
            let thisPicker = $(".price-options input:checked");
            let minDate = dateRange.data('min_date');
            let startDate = dateRange.data('start_date');
            if (dateRange.attr('disabled') == 'disabled') {
                return;
            }


            // for using on other pages like cart details page set default date set or others
            // if the checkbox is not found then it will go for input having class daterange
            // we have used this logic for working this function on cart details page picker
            if (thisPicker.length == 0) {
                thisPicker = $("input.daterange");
            }

            let dateConfigureObj = {
                opens: 'right',
                timePicker: true,
                locale: {
                    format: this.dateFormatInitial()
                },
            };

            if (minDate != '' && typeof minDate != 'undefined') {
                minDate = minDate.split("-").join("/");
                dateConfigureObj.minDate = new Date(Date.parse(minDate));

            }
            if (startDate != '' && typeof startDate != 'undefined') {
                startDate = startDate.split("-").join("/");
                startDate = new Date(Date.parse(startDate));

            }
            let timePickerToggle = function (configurations) {
                if (typeof configurations.show_start_time != 'undefined') {
                    dateConfigureObj.timePicker = configurations.show_start_time == true ? true : false;
                } else {
                    dateConfigureObj.timePicker = false;
                }
                if (!dateConfigureObj.timePicker) {
                    dateConfigureObj.locale.format = rm_single_product.dateFormatInitial();
                }
            };

            try {

                if (typeof configurations.show_end_date != 'undefined') {
                    if (dateRange.attr('disabled') == 'disabled') {
                        dateConfigureObj.singleDatePicker = false;
                    } else {
                        dateConfigureObj.singleDatePicker = configurations.show_end_date == true ? false : true;
                    }
                    timePickerToggle(configurations);
                } else {
                    dateConfigureObj.singleDatePicker = false;
                    timePickerToggle(configurations);
                }

                if (isCartContainRecurring){
                    dateConfigureObj.singleDatePicker = true;
                }
            } catch (e) {

            }

            // rm_single_product.dateFormatInitial = dateConfigureObj.locale.format;
            if (dateConfigureObj.singleDatePicker) {
                formated_date = this.convertDateFormat(startDate, dateConfigureObj.locale.format);
                dateRange.val(formated_date);
            }
            dateRange.daterangepicker(dateConfigureObj, function (start, end, label) {
                rm_single_product.rent_start = start.format('YYYY-MM-DD HH:mm');
                rm_single_product.rent_end = end.format('YYYY-MM-DD HH:mm');

                // to check if we are on details page because this runs on details page if ($('.availability-count').length > 0) {
                if ($('.rentmy-product-details').length > 0) {
                    if ($('#rm_v_products_type').val() == 2) {
                        // on change start date and end selection and price generate.
                        let price_id = $("input[name='rental-price']:checked").attr('data-price_id');
                        if (price_id) {
                            rm_single_product.get_dates_package_value(price_id)
                        } else {
                            rm_single_product.get_package_value();
                        }

                    } else {
                        // new code goes here
                        let price_id = $("input[name='rental-price']:checked").attr('data-price_id');
                        if (price_id) {
                            rm_single_product.get_dates_price_value(price_id)
                        } else {
                            rm_single_product.get_price_value();
                        }
                        // ends
                        // rm_single_product.get_price_value();
                    }
                }
                // the cart update function or ajax calls will go here. particularly the cart details page where we will check the cart products are available or not.
                if ($('.date-range-selection-default').length > 0) {
                    $('.date-range-selection-default').show();
                $('.date-range-selection-active').hide();
                    rm_single_product.cartDetailsAvailability();
                }


            });

            try {
                if (dateRange.attr('disabled') == 'disabled') {
                    dateRange.attr('data-daterangepicker').setStartDate(dateRange.attr('data-rent-start'));
                    dateRange.attr('data-daterangepicker').setEndDate(dateRange.attr('data-rent-end'));
                } else {
                   // if (navigator.userAgent.indexOf("Firefox") > 0) {
                        let customFormat = dateConfigureObj.locale.format;
                        customFormat = customFormat.replace(/-/g, '/');
                        dateRange.data('daterangepicker').setStartDate(this.convertDateFormat(thisPicker.attr('data-start_date'),customFormat));
                        dateRange.data('daterangepicker').setEndDate(this.convertDateFormat(thisPicker.attr('data-end_date'),customFormat));
                  //  }else {
                     //   dateRange.data('daterangepicker').setStartDate(moment(thisPicker.attr('data-start_date')));
                     //   dateRange.data('daterangepicker').setEndDate(moment(thisPicker.attr('data-end_date')));
                  //  }
                   // dateRange.attr('data-daterangepicker').setStartDate(thisPicker.attr('data-start_date'));
                    //dateRange.attr('data-daterangepicker').setEndDate(thisPicker.attr('data-end_date'));
                }
            } catch (e) {

            }

            // to set the label of datepicker according to the format selected from backend
            // this feature is for cart details pages date picker
            try {

                if ($('.date-range-selection-default').length > 0) {
                    let customFormat = dateConfigureObj.locale.format;
                    customFormat = customFormat.replace(/-/g, '/');

                    // firefox only date format fixes
                   // if (navigator.userAgent.indexOf("Firefox") > 0) {
                    if (isCartContainRecurring){
                        $('.date-range-selection-default span').html(
                            this.engToFrenchDate(this.convertDateFormat(thisPicker.attr('data-start_date'), customFormat)) + '  '
                        );
                    }else{
                        $('.date-range-selection-default span').html(
                            this.engToFrenchDate('<span class="rm-start-date">' + this.convertDateFormat(thisPicker.attr('data-start_date'), customFormat) + '</span><span class="rm-end-date"> - ' + this.convertDateFormat(thisPicker.attr('data-end_date'), customFormat)) + '</span>  '
                        );
                    }
                   // } else {
                   //     $('.date-range-selection-default span').html(
                    //        moment(thisPicker.attr('data-start_date')).format(customFormat) + ' - ' + moment(thisPicker.attr('data-end_date')).format(customFormat) + '  '
                      //  );
                   // }
                }
            } catch (e) {
                // console.log(e);
            }
        },
        engToFrenchDate: function (str){
            if (rentmy_store_id && (rentmy_store_id != 2277))
                return str;

            const months = {
                Jan: "Jan",
                Feb: "Fév",
                March: "Mars",
                April: "Avril",
                May: "Peut",
                June: "Juin",
                July: "Juillet",
                Aug: "Août",
                Sept: "Septembre",
                Oct: "Oct",
                Nov: "Nov",
                Dec: "Déc"
            };
            for (eng in months){
                str = str.replaceAll(eng, months[eng])
            }


            return str;

        },
        update_cart_availability_range_selection: function (response) {
            console.log('this function fired ' + this.dateFormatInitial());
            let dateFormat = this.dateFormatInitial();
            let thisPicker = $("input.daterange");
            thisPicker.attr('data-start_date', moment(response.rent_start).format('MM-DD-YYYY 12:00 A'));
            thisPicker.attr('data-end_date', moment(response.rent_end).format('MM-DD-YYYY 12:00 A'));
            let newDateValue = moment(response.rent_start).format(dateFormat + ' 12:00 A') + ' - ' + moment(response.rent_end).format(dateFormat + ' 12:00 A');

            console.log(newDateValue);

            $('#rm-date').val(newDateValue);
            $('.date-range-selection-default span').html(newDateValue);
            this.init_daterange_picker(this.config);
        },
        trigger_selected_date_range: function () {
            var dateFromLabel = $('.date-range-selection-default span').text().trim();
            console.log(dateFromLabel);
            $('#rm-date').val(dateFromLabel);
            console.log(this.config);
            console.log($('#rm-date').val());
            this.init_daterange_picker(this.config);

        },
        cartDetailsAvailability: function () {
            let data = {
                action: 'rentmy_options',
                action_type: 'get_cart_availability',
                data: {
                    end_date: this.rent_end,
                    source: "online",
                    start_date: this.rent_start,
                    type: "cart",
                }
            };
            jQuery.post(rentmy_ajax_object.ajaxurl, data, function (response) {
                // console.log(response);
                if (response.status == 'OK') {
                    // all ok status goes here
                    toastr.success("Cart item and price update successfully");
                    // window.location.reload();

                    if ($('.date-range-selection-default').length > 0) {
                        rm_single_product.update_cart_availability_range_selection(response.result.data);
                    }

                    try {
                        if (rm_single_product.is_exact_date) {
                            rm_single_product.update_cart();

                            if ($('.rentmy-cart-form-sidebar').length > 0) {
                                rm_single_product.update_cart_summary();
                            }

                            if ($('.cart-body').length > 0) {
                                rm_single_product.update_cart_topbar();
                            }
                        }
                    } catch (e) {
                        console.log(e);
                    }
                }
                if (response.status == 'NOK') {
                    // if nok then some products are out of stock. take action here
                    toastr.warning("Not all products are available through this date range");
                }
                if (response.result.error || response.result.message) {
                    toastr.warning(response.result.error || response.result.message);
                }
            }).fail(function (response) {
                toastr.error(response);
            });
        },
        checked_rent_buy_radio: function (type, is_initial=false) {
            rm_single_product.min_addon_quantity_check($('.rentmy-add-on-products'));
            if (type == 'buy') {
                $('#rental_type_buy').attr('checked', true);
                $('.buy').show();
                $('.rent').hide();
                $('.price-options').hide();
                $('.rm-rental-daterange').hide();
                //this.load_default_values();
                this.quantity = 1;
                this.price = this.initial_buy_price;
                this.rental_type = 'buy';

                $('#rm_quantity').val(this.quantity);
                $('.price .buy .amount').text(this.priceFormat(this.price));
                if ($('#rentmy_available_for_sale').length > 0){
                    $('.availability-count').html(this.available_for_sale);
                }



                if ($(".exact-date-wrapper").length > 0) {
                    $(".exact-date-wrapper").hide();
                }

                // if availablility is at least 1 or it will be disabled by default
                if ($('#rentmy-rent-item').data('attr-default_availability') != false) {
                    $('#rentmy-rent-item').attr('disabled', false);
                }

            } else if (type == 'rent') {
                $('#rental_type_rent').attr('checked', true);
                $('.rent').show();
                $('.buy').hide();

                if ($('#rentmy_available').length > 0){
                    $('.availability-count').html(this.available);
                }

                if (this.config.rental_price_option && is_initial) {
                    $('.price-options').show();
                    $('.price-options .radio-container input.first-element-selection').trigger('click');

                } else {
                    $('.price-options').hide();
                }

                if ($("#duration").length > 0) {
                    $("#duration").val($("#duration option:first").val());
                }
                if ($("#exact_time").length > 0) {
                    $("#exact_time").val($("#exact_time option:first").val());
                }

                $('.rm-rental-daterange').show();
                //this.load_default_values();
                this.quantity = 1;
                this.price = this.initial_rent_price;
                this.rental_type = 'rent';
                $('#rm_quantity').val(this.quantity);
                $('.price .rent .amount').text(this.priceFormat(this.price));

                if ($(".exact-date-wrapper").length > 0) {
                    $(".exact-date-wrapper").show();
                }

                // swithcing of date value for not changing
                if (rm_single_product.selectedDuration != '') {
                    let countChild = 1; // use 2 for the second item to be choosed
                    $("#duration option").each(function () {
                        if ($(this).attr('value') == rm_single_product.selectedDuration) {
                            countChild = $(this).context.index + 1;
                        }
                    });

                    $('#duration')
                        .find('option:nth-child(' + countChild + ')')
                        .prop('selected', true)
                        .trigger('change'); // trigger a virtual click of that selected item

                    // if availablility is at least 1 or it will be disabled by default
                    if ($('#rentmy-rent-item').data('attr-default_availability') != false) {
                        $('#rentmy-rent-item').attr('disabled', false);
                    }
                } else {

                    if ($('#duration').length > 0) {
                        $('#duration')
                            .find('option:nth-child(1)')
                            .prop('selected', true)
                            .trigger('change'); // use 2 for the second item to be choosed

                        // if availablility is at least 1 or it will be disabled by default
                        if ($('#rentmy-rent-item').data('attr-default_availability') != false) {
                            $('#rentmy-rent-item').attr('disabled', true);
                        }
                    }

                }

            } else {
                if ($('#rental_type_buy').is(':visible')) {
                    $('#rental_type_buy').attr('checked', true);
                    $('.buy').show();
                    $('.rent').hide();
                    $('.price-options').hide();
                    $('.rm-rental-daterange').hide();

                    if ($(".exact-date-wrapper").length > 0) {
                        $(".exact-date-wrapper").hide();
                    }

                } else if ($('#rental_type_rent').is(':visible')) {
                    console.log('rent by visibility');
                    $('#rental_type_rent').attr('checked', true);
                    $('.rent').show();
                    $('.buy').hide();
                    if (this.config.rental_price_option) {
                        $('.price-options').show();
                        // $('.price-options .radio-container input:first-child').trigger('click');
                        // console.log('click happened');
                    } else {
                        $('.price-options').hide();
                    }
                    $('.rm-rental-daterange').show();

                    if ($(".exact-date-wrapper").length > 0) {
                        $(".exact-date-wrapper").show();
                    }

                    if ($("#duration").length > 0) {
                        $("#duration").val($("#duration option:first").val());
                    }
                    if ($("#exact_time").length > 0) {
                        $("#exact_time").val($("#exact_time option:first").val());
                    }

                    // swithcing of date value for not changing
                    if (rm_single_product.selectedDuration != '') {
                        let countChild = 2;
                        $("#duration option").each(function () {
                            if ($(this).attr('value') == rm_single_product.selectedDuration) {
                                countChild = $(this).context.index + 1;
                            }
                        });

                        $('#duration')
                            .find('option:nth-child(' + countChild + ')')
                            .prop('selected', true)
                            .trigger('change');
                    } else {
                        $('#duration')
                            .find('option:nth-child(2)')
                            .prop('selected', true)
                            .trigger('change');
                    }

                }
            }
        },
        bind_select_options: function (el_id, json) {
            if (json && json.length > 0) {
                $('#variantSet_' + el_id).empty().append('<option selected="selected" value="">--Select--</option>');
                for (var i = 0; i < json.length; i++) {
                    var option = $("<option>");
                    option.attr("value", json[i]['id']);
                    option.html(json[i]['name']);
                    $('#variantSet_' + el_id).append(option);
                }
            }
        },
        getImageLink: function (product_id, image, type = 'small'){
       let link = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mPkrQcAAJ8AjsW513IAAAAASUVORK5CYII=';
            if (image){
                link = rentmy_base_file_url + 'products' + '/' + rentmy_store_id + '/' + product_id+ '/' + image;
            }
            return link;
        },
        bind_images: function (images){
            let html = '';
            if (images.length > 0) {
                images.sort((a, b) => a.status < b.status ? 1 : -1);
                 html += `<div class="product-view-area">
                            <div class="image-list-area">
                                <ul class="image-list">`;
                let rm_counter = 1;
                for (index in images) {
                    html += `<li class="image-item">
                             <a class="view-image ${rm_counter == 1 ? 'active-viewimg' : ''}"
                               href="javascript:void(0)">
                                  <img data-targetsource="${this.getImageLink(images[index].product_id, images[index].image_large)}" src="${this.getImageLink(images[index].product_id, images[index].image_small)}">
                             </a>
                           </li>`;
                    rm_counter++;
                }
                html += `</ul></div>`;
                for (index in images) {
                    html += ` <div class="product-view-image">`;

                    html += `  <img class="active"
                     src="${this.getImageLink(images[index].product_id, images[index].image_large)}">    
            </div>`;
                    break;
                }

                html += '</div>';
            }else{
                html += `<img src=${this.getImageLink('', '')}`
            }

        $('.rentmy-product-details').find('.images').html(html);
        },
        bind_product_form_options: function (json) {

            if (json?.available)
                this.available = json.available


            $('.rm-rental-daterange').show();
            // bind price type
            var prices = this.formate_prices(json.prices);
            this.show_price_types(prices);
            // bind price
            this.show_price(prices);
            // bind price options
            prices['recurring_prices'] = json.recurring_prices??[]
            this.show_priceoptions(prices);
            this.bind_images(json.images);
            // bind availability
            // bind image
            // variants product id
            this.variants_products_id = json.variants_products_id;
        },
        formate_prices: function (data) {
            if (data.length > 0) {
                var prices = data[0];
                var obj = {
                    buy: {type: false, price: 0, id: null},
                    rent: {type: false, price: []}
                };
                var rent = ["hourly", "daily", "weekly", "monthly"];
                if (prices.base.price > 0) {
                    obj.buy["type"] = true;
                    obj.buy["price"] = prices.base.price;
                    obj.buy["id"] = prices.base.id;
                    obj.buy["html"] = prices.base.html;
                }
                let ren = [];
                const rentPrices = data[0];

                if (rentPrices.fixed) {
                    const fp = {
                        type: "",
                        price: rentPrices.fixed.price,
                        id: rentPrices.fixed.id,
                        label: "",
                        html: rentPrices.fixed.html,
                        rent_start: rentPrices.fixed.rent_start,
                        rent_end: rentPrices.fixed.rent_end
                    };
                    obj.rent["price"].push(fp);
                } else {
                    for (let c in rentPrices) {
                        for (let i = 0; i < rentPrices[c].length; i++) {
                            rentPrices[c][i]["type"] = rentPrices[c][i].label;
                            obj.rent["price"].push(rentPrices[c][i]);
                        }
                    }
                }
                if (obj.rent["price"].length > 0) obj.rent["type"] = true;
                return obj;
            }
            return data;
        },
        format_date: function (date) {

        },
        show_date: function () {

        },
        show_price: function (prices) { // when variants changed show main price
            if (prices.rent.type) { // rent price radio
                if (prices.rent.price.length > 0) {
                    this.price = this.initial_rent_price = prices.rent.price[0].price;
                    $('.price .rent').html(prices.rent.price[0].html);
                    if (this.rental_type == 'rent'){
                        rm_single_product.checked_rent_buy_radio('rent');
                    }
                }
            }
            if (prices.buy.type) { // buy price radio
                this.price = this.initial_buy_price = prices.buy.price;
                $('.price .buy').html(prices.buy.html);
                if (this.rental_type == 'buy'){
                    rm_single_product.checked_rent_buy_radio('buy');
                }
            }
        },
        show_price_types: function (prices) {
            $('.rental-type .buy_input').hide();
            $('.rental-type .rent_input').hide();
            if (prices.rent.type) {
                $('.rental-type .rent_input').show();
                $('.rental-type .rent_input').trigger('click');
            }
            if (prices.buy.type) {
                $('.rental-type .buy_input').show();
                if (!prices.rent.type){
                    $('.rental-type .buy_input').trigger('click');
                }
            }

        },
        show_priceoptions: function (prices) {
            if (prices.rent.type && !this.recurring) {
                var priceOptions = '';
                $.each(prices.rent.price, function (index, value) {
                    if (typeof value.price_options != 'undefined') {
                        priceOptions += value.price_options;
                    }
                });
                if (!this.is_exact_date){
                    $('.price-options').html($(priceOptions));
                }
                $('.price-options').find('input[name="rental-price"]:first').trigger('click');
            }else{
                var priceOptions = '';

                // let cart_without_recurring = $("#cart_without_recurring").val()
                let is_rental_type_match = false;
                $.each(prices.rent.price, function (index, rents) {

                    let per_txt = config_labels?.others?.product_list_per?config_labels?.others?.product_list_per:'per';
                    let for_txt = config_labels?.others?.product_list_for?config_labels?.others?.product_list_for:'for';
                    let symbol = rm_single_product.config.currency_format.symbol ? rm_single_product.config.currency_format.symbol : '$';
                    label_for_per = rents.duration<=1?per_txt: for_txt+ ' ' + rents.duration;
                    //duration_label = rents.duration?(config_labels?.product_details?.lbl_billing_at_a_rate_of?config_labels?.product_details?.lbl_billing_at_a_rate_of:' ') + symbol + rm_single_product.priceFormat(rents.price) +' ' + label_for_per + ' '+ rents.label:'';
                    duration_label =  symbol + rm_single_product.priceFormat(rents.price) + " /" + rents.duration + " " + rents.label;
                    if (cart_added_recurring.duration_type == rents.duration_type){
                        is_rental_type_match = true;
                    }
                    if (cart_without_recurring){
                        priceOptions += `<label class="radio-container">
                                         
                                                <input type="radio" name="rental-price" class="${index==0?'first-element-selection':''}"
                                                       data-price_id="${rents.id}"
                                                       data-price="${rents.price}"
                                                       data-duration="${rents.duration??''}"
                                                       data-label="${rents.label}"
                                                       data-start_date="${rents.rent_start}"
                                                       data-end_date="${rents.rent_end}"
                                                       value="${rents.id}" ${index==0?'checked':''}>
                                      
                                           ${duration_label}
                                           <span class="checkmark"></span>
                                    </label>
                    `;
                    }else{
                        priceOptions += `
                                           <label class="radio-container">
                                                <i class="fa fa-arrow-right"></i>
                                                ${duration_label}
                                                <br>
                                            </label>
                                <label class="radio-container" style="display: none">
                                         
                                                <input type="radio" name="rental-price" class="${index==0?'first-element-selection':''}"
                                                       data-price_id="${rents.id}"
                                                       data-price="${rents.price}"
                                                       data-duration="${rents.duration??''}"
                                                       data-label="${rents.label}"
                                                       data-start_date="${rents.rent_start}"
                                                       data-end_date="${rents.rent_end}"
                                                       value="${rents.id}" ${cart_added_recurring.duration_type == rents.duration_type?'checked':''}>
                                      
                                           ${duration_label}
                                           <span class="checkmark"></span>
                                    </label>
                                   
                    `;
                    }

                });

                if (!is_rental_type_match && !jQuery.isEmptyObject(cart_added_recurring)){
                    priceOptions+= '<p class="text-danger">This product is not available for selected rental Payment.</p>';

                }
                if (!this.is_exact_date){
                    $('.price-options').html($(priceOptions));
                    $('.price-options').css('display', 'block');
                }
                $('.price-options').find('input[name="rental-price"]:checked').trigger('click');

                if (this.available <= 0){
                    $('.add_to_cart_button').attr('disabled', true);
                }else if (cart_added_recurring?.duration_type && !is_rental_type_match){
                    $('.add_to_cart_button').attr('disabled', true);
                }else{
                    $('.add_to_cart_button').attr('disabled', false);
                }
                $('.availability .availability-count').html(this.available);
            }
        },
        show_availability: function () {

        },
        show_datechanges: function () {

        },
        set_price() {
            this.total_price = this.price * this.quantity;
            if ($('#rental_type_buy').is(':checked')) {
                $('.price .buy .amount').text(this.priceFormat(this.total_price));
            }
            if ($('#rental_type_rent').is(':checked')) {
                $('.price .rent .amount').text(this.priceFormat(this.total_price));
            }
        },
        update_quantity(type, counter) {
            let addOnProduct = $('.addon-product-parent-row');
            let minQuantity = addOnProduct.attr('data-min_quantity');
            let productId = addOnProduct.attr('data-product_id');
            let current_quantity;
            let cx_counter = 1;

            if (type == 'increase') {
                this.quantity = this.quantity + counter;
                $('#rm_quantity').val(this.quantity);

                current_quantity = minQuantity * this.quantity;
                addOnProduct.find('input').each(function () {
                    if (cx_counter == 1 || productId != $(this).attr('data-product_id')) {
                        $(this).val(current_quantity);
                    } else {
                        $(this).val(0);
                    }
                    cx_counter++;
                });
                addOnProduct.attr('data-updated_quantity', current_quantity);

            } else if (type == 'decrease') {
                if (this.quantity >= 2) {
                    this.quantity = this.quantity - counter;
                    $('#rm_quantity').val(this.quantity);

                    current_quantity = minQuantity * this.quantity;
                    addOnProduct.find('input').each(function () {
                        if (cx_counter == 1 || productId != $(this).attr('data-product_id')) {
                            $(this).val(current_quantity);
                        } else {
                            $(this).val(0);
                        }
                        cx_counter++;
                    });
                    addOnProduct.attr('data-updated_quantity', current_quantity);

                }
            } else if (type == 'input') {
                this.quantity = $('#rm_quantity').val();
            }

            this.set_price();
        },
        add_to_cart: function (event='') {
            if (event!=''){
                event.prop("disabled",true);
                event.find(".loading").css('display', '');
            }

            if (this.product_id == '') {
                rm_single_product.load_default_values();
            }
            let custom_fields = $("select[name='customFields[]']")
                .map(function(){
                    if ($(this).val() != ''){
                        let data = $(this).val().split("%rentmy%").join(" ");
                        return JSON.parse(data);
                    }

                }).get();
            let price_id = '';

            if (this.rental_type == 'rent'){
                price_id = $("input[name='rental-price']:checked").attr('data-price_id');
            }

            if (!price_id && ($("#pricingOptionRecurring").length > 0)){
                pricing = $("#pricingOptionRecurring").val()
                pricing = pricing.split("%rentmy%").join(" ");
                pricing = JSON.parse(pricing)
                price_id = pricing.id
            }
            var data = {
                action: 'rentmy_options',
                action_type: 'add_to_cart',
                data: {
                    product_id: this.product_id,
                    variants_products_id: this.variants_products_id,
                    deposit_amount: $("#rentmy_deposit_amount").val(),
                    quantity: this.quantity,
                    rental_type: this.rental_type,
                    rent_start: this.rent_start,
                    rent_end: this.rent_end,
                    term: this.term,
                    custom_fields: custom_fields,
                    recurring: this.recurring,
                    price_id: price_id,

                }
            };

            if (this.addon_product_data().length > 0) {
                data.data.required_addons = this.addon_product_data();
            }

            if (this.rental_type == 'buy') {
                data.data.rent_start = '';
                data.data.rent_end = '';

            } else {
                if ($('#rm-date').length > 0) {
                    if ($('#rm-date').attr('disabled')) {
                        data.data.rent_start = moment($('#rm-date').attr('data-rent-start')).format('YYYY-MM-DD HH:mm');
                        data.data.rent_end = moment($('#rm-date').attr('data-rent-end')).format('YYYY-MM-DD HH:mm');
                    } else {
                        data.data.rent_start = $('#rm-date').data('daterangepicker').startDate.format('YYYY-MM-DD HH:mm');
                        data.data.rent_end = $('#rm-date').data('daterangepicker').endDate.format('YYYY-MM-DD HH:mm');
                    }
                }

                if (typeof data.data.rent_start == 'undefined' || typeof data.data.rent_end == 'undefined') {
                    $('#rentmy-rent-item').attr('disabled', true);
                    toastr.warning('You must have to select a suitable date/duration.');
                    return;
                }

                if (data.data.rent_start == '' || data.data.rent_end == '') {
                    $('#rentmy-rent-item').attr('disabled', true);
                    toastr.warning('You must have to select a suitable date/duration.');
                    return;
                }
            }
            $.post(rentmy_ajax_object.ajaxurl, data, function (response) {
                try {
                    if (response.status == 'OK') {
                        if (rm_single_product.delivery_flow)
                            localStorage.setItem('deliveryFlow', rm_single_product.delivery_flow)

                        toastr.success(response, 'Item added to cart.');

                        //check for page is not a cart page then redirect to cart page
                        if (rentmy_cart_url.length > 0) {
                            let url = rentmy_cart_url + '?token=' + response.result.data.token +'&add-to-cart=true'
                            window.location.replace(url);
                            return;
                        }

                        rm_single_product.update_cart();
                        rm_single_product.update_cart_summary();
                        rm_single_product.update_cart_topbar();
                        // disable the date picker for the start end date lock
                        if (jQuery('.daterange').length > 0) {
                            jQuery('.daterange').css({
                                "opacity": "0.7",
                                "pointer-events": "none"
                            });
                        }
                        if (event!='') {
                            event.prop("disabled", false);
                            event.find(".loading").css('display', 'none');
                        }

                    } else {
                        if (event!='') {
                            event.prop("disabled", false);
                            event.find(".loading").css('display', 'none');
                        }
                        toastr.warning(response.result.error);
                    }
                } catch (e) {
                    if (event!='') {
                        event.prop("disabled", false);
                        event.find(".loading").css('display', 'none');
                    }
                    toastr.warning(e);
                }
            }).fail(function (response) {
                toastr.error(response);
            });

        },
        add_to_cart_package: function(event='') {
            if (event!='') {
                event.prop("disabled", true);
                event.find(".loading").css('display', '');
            }
            if (this.product_id == '') {
                rm_single_product.load_default_values();
            }
            let price_id = '';

            if (this.rental_type == 'rent'){
                price_id = $("input[name='rental-price']:checked").attr('data-price_id');
            }

            if (!price_id && ($("#pricingOptionRecurring").length > 0)){
                pricing = $("#pricingOptionRecurring").val()
                pricing = pricing.split("%rentmy%").join(" ");
                pricing = JSON.parse(pricing)
                price_id = pricing.id
            }
            let custom_fields = $("select[name='customFields[]']").map(function(){
                if ($(this).val() != ''){
                    let data = $(this).val().split("%rentmy%").join(" ");
                    return JSON.parse(data);
                }
            }).get();


            var data = {
                action: 'rentmy_options',
                action_type: 'add_to_cart_package',
                data: {
                    package_id: this.product_id,
                    variants_products_id: this.variants_products_id,
                    quantity: this.quantity,
                    deposit_amount: $("#rentmy_deposit_amount").val(),
                    rental_type: this.rental_type,
                    rent_start: this.rent_start,
                    rent_end: this.rent_end,
                    term: this.term,
                    recurring: this.recurring,
                    price_id: price_id,
                    custom_fields: custom_fields
                }
            };
            var products = [];
            $('.package-items li').each(function (i, item) {
                //test
                var package_item = {
                    product_id: $(this).children('h6').attr('data-id'),
                    quantity: $(this).children('h6').attr('data-quantity'),
                    variants_products_id: $(this).find(".package_variant").val()
                };
                products.push(package_item)
            });
            data.data.products = products;
            if (this.rental_type == 'buy') {
                data.data.rent_start = '';
                data.data.rent_end = '';

            } else {
               if (!data.data.rent_start || !data.data.rent_end){
                   if ($('#rm-date').length > 0) {
                       if ($('#rm-date').attr('disabled')) {
                           data.data.rent_start = moment($('#rm-date').attr('data-rent-start')).format('YYYY-MM-DD HH:mm');
                           data.data.rent_end = moment($('#rm-date').attr('data-rent-end')).format('YYYY-MM-DD HH:mm');
                       } else {
                           data.data.rent_start = $('#rm-date').data('daterangepicker').startDate.format('YYYY-MM-DD HH:mm');
                           data.data.rent_end = $('#rm-date').data('daterangepicker').endDate.format('YYYY-MM-DD HH:mm');
                       }
                   }
               }
            }
            $.post(rentmy_ajax_object.ajaxurl, data, function (response) {
                console.log(response);
                try {
                    if (response.status == 'OK') {
                        if (rm_single_product.delivery_flow)
                            localStorage.setItem('deliveryFlow', rm_single_product.delivery_flow)

                        toastr.success(response, 'Item added to cart.');
                        if (rentmy_cart_url.length > 0) {
                            let url = rentmy_cart_url + '?token=' + response.result.data.token +'&add-to-cart=true'
                            window.location.replace(url);
                            return;
                        }
                        rm_single_product.update_cart();
                        rm_single_product.update_cart_summary();
                        rm_single_product.update_cart_topbar();
                        if (event!='') {
                            event.prop("disabled", false);
                            event.find(".loading").css('display', 'none');
                        }
                    } else {
                        if (event!='') {
                            event.prop("disabled", false);
                            event.find(".loading").css('display', 'none');
                        }
                        toastr.warning(response.result.error);
                    }
                } catch (e) {
                    if (event!='') {
                        event.prop("disabled", false);
                        event.find(".loading").css('display', 'none');
                    }
                    toastr.warning(response.result.error);
                }
            }).fail(function (response) {
                toastr.error(response);
            });

        },
        update_package_availability: function () {
            var post_data = {
                action: 'rentmy_options',
                action_type: 'update_package_availability',
                data: {
                    product_id: this.product_id,
                    product_uid: $('#rm_pd_product_uid').val(),
                    variants_products_id: this.variants_products_id,
                    quantity: this.quantity,
                    rental_type: this.rental_type,
                    term: this.term,
                }
            };
            var products = [];
            $('.package-items li').each(function (i, item) {
                //test
                var package_item = {
                    product_id: $(this).children('h6').attr('data-id'),
                    variants_products_id: $(this).find(".package_variant").val()
                };
                products.push(package_item)
            });
            post_data.data.products = products;
            if (this.rental_type == 'buy') {
                post_data.data.rent_start = '';
                post_data.data.rent_end = '';

            } else {
                post_data.data.rent_start = $('#rm-date').data('daterangepicker').startDate.format('YYYY-MM-DD HH:mm');
                post_data.data.rent_end = $('#rm-date').data('daterangepicker').endDate.format('YYYY-MM-DD HH:mm');
            }
            $.post(rentmy_ajax_object.ajaxurl, post_data, function (response) {
                try {
                    if (response.status == 'OK') {

                        //rm_single_product.available = response.result.data;
                        //$('.availability-count').html(rm_single_product.available);
                    } else {
                        // toastr.warning(response.error || 'Problem occured finding package');
                    }
                } catch (e) {
                    toastr.warning(e);
                }
            }).fail(function (response) {
                toastr.error(response);
            });


        },
        remove_from_cart: function () {
            let data = {
                action: 'rentmy_options',
                action_type: 'remove_from_cart',
                data: {
                    product_id: this.product_id,
                    cart_item_id: this.cart_item_id
                }
            };
            let thisRefObj = this;
            $.post(rentmy_ajax_object.ajaxurl, data, function (response) {

                location.reload();
                if ($("#cart-row-" + rm_single_product.cart_item_id).length > 0) {
                    $("#cart-row-" + rm_single_product.cart_item_id).fadeOut(300, function () {
                        $(this).remove();
                        toastr.success('Item removed from cart');
                    });
                }
                if ($("#mini-cart-row-" + rm_single_product.cart_item_id).length > 0) {
                    $("#mini-cart-row-" + rm_single_product.cart_item_id).fadeOut(300, function () {
                        $(this).remove();
                        // toastr.success('Item removed from cart');
                    });
                }
                if ($(".cart-addon-row-" + rm_single_product.cart_item_id).length > 0) {
                    $(".cart-addon-row-" + rm_single_product.cart_item_id).fadeOut(300, function () {
                        $(this).remove();
                    });
                }

                thisRefObj.update_cart();
                rm_single_product.update_cart_summary();
                rm_single_product.update_cart_topbar();
            })
                .fail(function (response) {
                    toastr.error(response);
                })
                .done(function () {
                    // thisBtn.attr("disabled", false);
                });
        },
        priceFormat: function (amount, withSymbol=false) {
            // return parseFloat(priceVal).toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,');
            amount = parseFloat(amount);
            if (isNaN(amount)){
                return;
            }
            amount = new Number(amount);
            var myObj = { style: 'currency', currency: 'USD' };
            let currency_config = this.config.currency_format;
            let symbol = currency_config.symbol?currency_config.symbol:'$';

            let locale = currency_config.locale;
            let amountStr = parseFloat(amount).toFixed(2);
            if (locale){
                amountStr = Number(amount).toLocaleString(locale, myObj);
                amountStr = amountStr.replace('US', '');
                amountStr = amountStr.replace('$', '');
            }
            if (withSymbol){
                amountStr = '<span class="pre"'+symbol + amountStr +'<span>';

                if (currency_config.post){
                    amountStr = '<span class="post"'+ amountStr +symbol+'<span>';
                }
            }

            return amountStr;
        },
        update_cart_summary: function () {
            let data = {
                action: 'rentmy_order_details'
            };
            $.post(rentmy_ajax_object.ajaxurl, data, function (response) {
                $('form#rentmy-cart-form-sidebar').remove();
                $('.rentmy-cart-form-sidebar').html(response);
            })
                .fail(function (response) {
                    toastr.error(response);
                });
        },
        update_cart_topbar: function () {
            let data = {
                action: 'rentmy_cart_topbar'
            };

            $.post(rentmy_ajax_object.ajaxurl, data, function (response) {
                if (!response) {
                    localStorage.removeItem('deliveryFlow');
                    return;
                }

                if (response.cart_items.length == 0) {
                    localStorage.removeItem('deliveryFlow');
                    $('.carthome-total').hide();
                    $('.cart-item-total-count-topbar').html(0);
                    $('.inner-cart-body-topbar').html('<i style="margin: 30px auto; display: table;" class="fa fa-smile-o fa-5x" aria-hidden="true"></i><p class="text-center">No Products in cart</p>');
                    $('.cart-item-total-topbar').html(rm_single_product.config?.currency_format?.symbol + '0.00');
                } else {
                    $('.carthome-total').show();
                    $('.cart-item-total-count-topbar').html(response.cart_items.length);
                    $('.cart-item-total-topbar').html(rm_single_product.config?.currency_format?.symbol + response.total);
                    var cart_template = '';
                    for (var key in response.cart_items) {
                        try {
                            var imageLink = rentmy_base_file_url + 'products' + '/' + response.cart_items[key].store_id + '/' + response.cart_items[key].product_id + '/' + response.cart_items[key].product.images[0].image_small;
                        } catch (e) {
                            var imageLink = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mPkrQcAAJ8AjsW513IAAAAASUVORK5CYII=';
                        }

                        cart_template += '<div id="mini-cart-row-' + response.cart_items[key].id + '" class="cart-list">' +
                            '<img src="' + imageLink + '" class="img-fluid">' +
                            '<div class="cart-name-price">' +
                            '<h4>' + response.cart_items[key].product.name + ' <a href="javascript:void(0)"><i data-cart_item_id="' + response.cart_items[key].id + '" data-product_id="' + response.cart_items[key].product.id + '" class="remove remove_from_cart fa fa-trash"></i></a></h4>' +
                            '<span>' + rm_single_product.config?.currency_format?.symbol + response.cart_items[key].total + ' x ' + response.cart_items[key].quantity + '</span>' +
                            '</div>' +
                            '</div>';
                    }

                    jQuery('.inner-cart-body-topbar').html($(cart_template))
                        .on('click', ".remove_from_cart", function () {
                            rm_single_product.product_id = $(this).attr('data-product_id');
                            rm_single_product.cart_item_id = $(this).attr('data-cart_item_id');
                            rm_single_product.remove_from_cart();
                        });
                }
            })
                .fail(function (response) {
                    toastr.error(response);
                });
        },
        update_cart: function () {
            let data = {
                action: 'rentmy_options',
                action_type: 'update_cart',
                data: {}
            };
            let thisRefObj = this;
            $.post(rentmy_ajax_object.ajaxurl, data, function (response) {

                let cartResponse = response.data;
                let cartItems = cartResponse.cart_items;

                if (cartItems.length == 0) {
                    localStorage.removeItem('deliveryFlow');
                    window.location.href = rentmy_cart_url +'?add-to-cart=true'
                    // if ($('#rentmy-cart-form').length > 0) {
                    //     $('#rentmy-cart-form').html('<div class="rentmy-plugin-manincontent"> <div div class="col-md-12 text-center" > <h3 style="margin-top: 70px;margin-bottom: 70px !important;">Your cart is empty</h3> <div class="procces-contiue-checkout" style="margin-bottom: 70px;"> <a style="background-color: #1786c5;padding: 20px; border-radius: 17px; color: #fff; font-size: 16px;margin-bottom: 75px;" href="' + rentmy_home_url + '/rentmy-products-list"> Continue Shopping </a> </div></div></div >');
                    // }
                    // if ($('#rentmy-cart-form-sidebar').length > 0) {
                    //     $('#rentmy-cart-form-sidebar').html('<span class="rentmy-errore-msg">No items found in cart</span>');
                    // }
                    // if ($('.cart-related-producst-list').length > 0) {
                    //     $('.cart-related-producst-list').remove();
                    // }
                    return;

                } else {

                    for (keys in cartItems) {

                        // check for negative value in cart and remove that
                        if (cartItems[keys].quantity < 1) {
                            this.product_id = cartItems[keys].product_id;
                            this.cart_item_id = cartItems[keys].id;
                            rm_single_product.remove_from_cart();
                            return;
                        }
                        // ends

                        $('.rentmy-cart-row-price-' + cartItems[keys].id + ' p span.amount').html(thisRefObj.priceFormat(cartItems[keys].price));

                        if (cartItems[keys].cart_product_options && cartItems[keys].cart_product_options.length > 0){
                            cartItems[keys].cart_product_options.forEach(function (option){
                                $('.rentmy-cart-row-quantity-' + option.id).html(option.quantity);
                            });
                        }else{
                            $('.rentmy-cart-row-quantity-' + cartItems[keys].id).html(cartItems[keys].quantity);
                        }
                        var discount = cartItems[keys].discount;
                        var subtotal = '';
                        var symbolPrice = this.config?.currency_format?.symbol??'$';
                        if (discount.coupon_amount > 0){
                            subtotal = symbolPrice + thisRefObj.priceFormat(discount.coupon_sub_total) + '(' + symbolPrice + thisRefObj.priceFormat(discount.coupon_amount)+' Coupon Applied)';
                        }else{
                            subtotal = symbolPrice + thisRefObj.priceFormat(cartItems[keys].sub_total);
                        }

                        $('.rentmy-cart-row-sub_total-' + cartItems[keys].id).html('');
                        $('.rentmy-cart-row-sub_total-' + cartItems[keys].id).html(subtotal);

                        // addon item manipulation
                        if (cartItems[keys].products.length > 0) {
                            let add_on_products = cartItems[keys].products;
                            let cart_id = cartItems[keys].id;
                            for (keys in add_on_products) {
                                $('#addon-item-quantity-' + add_on_products[keys].id + '-parent-' + cart_id).html(add_on_products[keys].quantity);
                            }
                        }
                        // ends
                    //    product options rentmy-cart-product-option-
                        if (cartItems[keys].cart_product_options.length > 0){
                            let values = '';
                            cartItems[keys].cart_product_options.forEach(function (fields){

                                if (fields.options.length > 0){
                                    fields.options.forEach(function (option, key){
                                        let com = ((fields.options.length - 1) == key)?'':',';
                                        values += option.label +': '+option.value + com
                                    });
                                }
                                let operator = '';
                                if (fields.price >=0){
                                    operator = '+';
                                }else{
                                    operator = '-';
                                }
                                values += ' (qty: '+ fields.quantity+', '+operator+symbolPrice+thisRefObj.priceFormat(Math.abs(fields.price))+')'+'<br>';
                            });

                            $('.rentmy-cart-product-option-' + cartItems[keys].id).html('');
                            $('.rentmy-cart-product-option-' + cartItems[keys].id).html(values);
                        }

                    }
                }
                if (cartResponse.off_amount > 0){
                    $('.rentmy-item-discount').css('display', '');
                    $('.rentmy-cart-off_amount').html(thisRefObj.priceFormat(cartResponse.off_amount));
                }

                if (cartResponse.coupon_amount > 0){
                    $('.rentmy-coupon-discount').css('display', '');
                    $('.rentmy-cart-coupon_discount').html(thisRefObj.priceFormat(cartResponse.coupon_amount));
                }
                $('.rentmy-cart-sub_total').html(thisRefObj.priceFormat(cartResponse.sub_total));
                $('.rentmy-cart-tax').html(thisRefObj.priceFormat(cartResponse.tax));
                $('.rentmy-cart-deposit_amount').html(thisRefObj.priceFormat(cartResponse.deposit_amount));
                $('.rentmy-cart-total').html(thisRefObj.priceFormat(cartResponse.total));
            })
                .fail(function (response) {
                    toastr.error(response);
                })
                .done(function () {
                    // thisBtn.attr("disabled", false);
                });
        },
        apply_coupon: function () {
            let data = {
                action: 'rentmy_options',
                action_type: 'apply_coupon',
                data: {
                    coupon: this.coupon_code
                }
            };
            let thisRefObj = this;
            $.post(rentmy_ajax_object.ajaxurl, data, function (response) {
                if (typeof response.error != 'undefined') {
                    toastr.warning(response.error);
                } else {
                    toastr.success(response.message);
                    thisRefObj.update_cart();
                    rm_single_product.update_cart_summary();
                    rm_single_product.update_cart_topbar();
                }
            })
                .fail(function (response) {
                    toastr.error(response);
                })
                .done(function () {
                    // thisBtn.attr("disabled", false);
                });
        },
        update_cart_item: function () {
            let data = {
                action: 'rentmy_options',
                action_type: 'update_cart_item',
                data: {
                    id: this.cart_item_id,
                    increment: this.cart_item_quantity,
                    price: this.cart_item_price,
                    option_id: this.cart_item_option_id
                }
            };
            let thisRefObj = this;
            $.post(rentmy_ajax_object.ajaxurl, data, function (response) {
                thisRefObj.update_cart();
                rm_single_product.update_cart_summary();
                rm_single_product.update_cart_topbar();

                if (typeof response.error != 'undefined') {
                    toastr.error(response.error);
                } else {
                    toastr.success('Quantity updated');
                }
            })
                .fail(function (response) {
                    toastr.error(response);
                })
                .done(function () {
                    // thisBtn.attr("disabled", false);
                });
        },
        min_addon_quantity_check: function (params) {

            $('.addon-product-parent-row').each(function () {
                let product_id = $(this).attr('data-product_id');
                let min_quantity = $(this).attr('data-updated_quantity');
                let quantity_val = [];

                $(this).find('input').each(function () {
                    quantity_val.push(parseInt($(this).val()));
                });

                let total_quantity = quantity_val.reduce(function (a, b) {
                    return a + b
                }, 0);

                if (total_quantity > min_quantity) {
                    $(this).find('input').val(0);
                    toastr.warning('minimum quantity exceeded');
                }
            });
        },
        addon_product_data: function () {
            let addon_data = [];
            $('.addon-product-parent-row').find('input').each(function () {
                addon_data.push({
                    product_id: parseInt($(this).attr('data-product_id')),
                    quantity: parseInt($(this).val()),
                    quantity_id: parseInt($(this).attr('data-quantity_id')),
                    variants_products_id: parseInt($(this).attr('data-variants_products_id')),
                });
            });
            return addon_data;
        },
        get_dates_price_duration: function (price_id) {
            let data = {
                action: 'rentmy_options',
                action_type: 'get_dates_price_duration',
                data: {
                    price_id: price_id,
                    start_date: rm_single_product.rent_start
                }
            };
            $('#rentmy-rent-item').attr('disabled', true);
            jQuery.post(rentmy_ajax_object.ajaxurl, data, function (response) {

                rm_single_product.rent_start = response.start_date;
                rm_single_product.rent_end = response.end_date;
                let dateRange = $('.daterange');
                let dateFormat = rm_single_product.dateFormatInitial();
                let updatedDate = moment(response.start_date).format(dateFormat) + ' - ' + moment(response.end_date).format(dateFormat);
                dateRange.val(updatedDate);
                try {
                    dateRange.data('daterangepicker').setStartDate(moment(response.start_date).format(dateFormat));
                    dateRange.data('daterangepicker').setEndDate(moment(response.end_date).format(dateFormat));
                    if ($('#rm_v_products_type').val() == 2){
                        rm_single_product.get_package_value();
                    }else{
                        rm_single_product.get_price_value();
                    }
                    $('#rentmy-rent-item').attr('disabled', false);
                } catch (e) {

                }
            });
        },
        get_calculated_start_end_date: function (params) {
            if ($('#rm-date').length > 0) {
                rm_single_product.rent_start = $('#rm-date').data('daterangepicker').startDate.format('YYYY-MM-DD HH:mm');
                rm_single_product.rent_end = $('#rm-date').data('daterangepicker').endDate.format('YYYY-MM-DD HH:mm');
            }

            // on change start date and end selection and price generate.
            let price_id = params.attr('data-price_id');
            rm_single_product.get_dates_price_duration(price_id);
            // ends

            rm_single_product.quantity = 1;
            $('#rm_quantity').val(1);

            //set the checkbox price here to track the current rent price
            rm_single_product.price = params.attr('data-price');

            //changing price label to checkbox changing over time to time
            let targetPriceDiv = $(".price .rent");
            let price = params.attr('data-price');
            let duration = params.attr('data-duration');
            let duration_label = params.attr('data-label');
            let symbolPrice = rm_single_product.config.currency_format.symbol;
            let forText = 'for';
            if ((typeof config_labels != 'undefined') && config_labels.others.product_list_for){
                forText = config_labels.others.product_list_for;
            }
            let duration_format = '';
                if(duration){
                    duration_format = forText +' '+ duration + ' ' + duration_label;
                }
            if (targetPriceDiv.css('visibility') != 'hidden') {
                setTimeout(function () {
                    targetPriceDiv.html(
                        $(
                            '<h6><span class="pre">' + symbolPrice + '</span><span class="amount">' + rm_single_product.priceFormat(price) + '</span> '+ duration_format + '</h6>'
                        )
                    );
                }, 100);

            }

        },
        get_dates_package_value: function (price_id) {

            if (this.config.show_end_date == true) {
                this.get_package_value();
                return;
            }
            console.log("action_type: 'get_dates_price_duration' called for package");
            let data = {
                action: 'rentmy_options',
                action_type: 'get_dates_price_duration',
                data: {
                    price_id: price_id,
                    start_date: rm_single_product.rent_start
                }
            };
            $('#rentmy-rent-item').attr('disabled', true);
            jQuery.post(rentmy_ajax_object.ajaxurl, data, function (response) {
                rm_single_product.rent_start = response.start_date;
                rm_single_product.rent_end = response.end_date;
                let dateRange = $('.daterange');
                let dateFormat = rm_single_product.dateFormatInitial();
                dateRange.val(moment(response.start_date).format(dateFormat) + ' - ' + moment(response.end_date).format(dateFormat));
                try {
                    dateRange.data('daterangepicker').setStartDate(moment(response.start_date).format(dateFormat));
                    dateRange.data('daterangepicker').setEndDate(moment(response.end_date).format(dateFormat));
                    rm_single_product.get_package_value();
                    $('#rentmy-rent-item').attr('disabled', true);
                } catch (e) {

                }
            });
        },
        get_dates_price_value: function (price_id) {

            if (this.config.show_end_date == true) {
                this.get_price_value();
                return;
            }
            console.log('action fired -->> get_dates_price_duration');
            let data = {
                action: 'rentmy_options',
                action_type: 'get_dates_price_duration',
                data: {
                    price_id: price_id,
                    start_date: rm_single_product.rent_start
                }
            };
            $('#rentmy-rent-item').attr('disabled', true);
            jQuery.post(rentmy_ajax_object.ajaxurl, data, function (response) {
                rm_single_product.rent_start = response.start_date;

                // the condiiton is to satisfy the end date calculation for rental selection
                if (rm_single_product.is_exact_date == false && rm_single_product.config.show_end_date == false && rm_single_product.config.show_end_time == false) {
                    rm_single_product.rent_end = response.end_date;
                }

                let dateRange = $('.daterange');
                let dateFormat = rm_single_product.dateFormatInitial();

                if (rm_single_product.is_exact_date == false && rm_single_product.config.show_end_date == false && rm_single_product.config.show_end_time == false) {
                    dateRange.val(moment(response.start_date).format(dateFormat) + ' - ' + moment(response.end_date).format(dateFormat));
                } else {
                    dateRange.val(moment(response.start_date).format(dateFormat) + ' - ' + moment(rm_single_product.rent_end).format(dateFormat));
                }

                try {
                    dateRange.data('daterangepicker').setStartDate(moment(response.start_date).format(dateFormat));

                    if (rm_single_product.is_exact_date == false && rm_single_product.config.show_end_date == false && rm_single_product.config.show_end_time == false) {
                        dateRange.data('daterangepicker').setEndDate(moment(response.end_date).format(dateFormat));
                    } else {
                        dateRange.data('daterangepicker').setEndDate(moment(rm_single_product.rent_end).format(dateFormat));
                    }

                    rm_single_product.get_price_value();
                    $('#rentmy-rent-item').attr('disabled', false);
                } catch (e) {

                }
            });
        }
    };

    $('body')
        .on("click", ".rental-type label", function () {
            type = $(this).children('input').val();
            rm_single_product.checked_rent_buy_radio(type, true);
        })
        .on('change', ".variantSets select", function () {

            index = $(this).attr('data-index');
            total = $(this).attr('data-total');
            if (index != total) {
                current_set_id = $(this).attr('data-id');
                next_set_id = $(this).attr('data-next-id');
                var data = {
                    action: 'rentmy_options',
                    action_type: 'get_variant_chain',
                    data: {
                        product_id: $('#rm_pd_product_id').val(),
                        variant_id: this.value
                    }
                };
                jQuery.post(rentmy_ajax_object.ajaxurl, data, function (response) {
                    rm_single_product.bind_select_options(next_set_id, response);
                });
            } else { // last item of the chain
                prev_set_id = $(this).attr('data-prev-id');
                var data = {
                    action: 'rentmy_options',
                    action_type: 'get_last_variant',
                    data: {
                        product_id: $('#rm_pd_product_id').val(),
                        variant_id: this.value,
                        chain_id: $('#variantSet_' + prev_set_id).val()
                    }
                };
                jQuery.post(rentmy_ajax_object.ajaxurl, data, function (response) {
                    rm_single_product.bind_product_form_options(response);
                });
            }
        })
        .on('change', ".rentmy-custom-fields select", function () {
            let custom_fields = [];
            $(".rentmy-custom-fields select[name='customFields[]']").each(function() {
                if ($(this).val() != ''){
                    let data = $(this).val();
                    data = data.split("%rentmy%").join(" ");
                    custom_fields.push(JSON.parse(data));
                }
            });
            rm_single_product.get_price_value(true, custom_fields)

        })
        .on('click', ".quantity .increase", function () {
            rm_single_product.update_quantity('increase', 1)
        })
        .on('click', ".quantity .decrease", function () {
            rm_single_product.update_quantity('decrease', 1)
        })
        .on('keypress', ".quantity .decrease", function (e) {
            var keycode = e.which;
            if (keycode == '13') {
                // alert('You pressed a &quot;enter&quot; key in textbox');
            }
        })
        // .on('change', ".price-options input", function(){
        //     console.log('change triggered from dom auto');
        //     rm_single_product.get_calculated_start_end_date($(this));
        // })
        .on('click', ".price-options input", function () {
            console.log('click triggered from dom auto');
            rm_single_product.get_calculated_start_end_date($(this));
        })
        .on('click', ".add_to_cart_button", function async() {
            rm_single_product.add_to_cart($(this));
            // rm_single_product.update_cart_summary();
            // rm_single_product.update_cart_topbar();
        })
        .on('click', ".add_to_cart_package_button", function async() {

            rm_single_product.add_to_cart_package($(this));
            // rm_single_product.update_cart_summary();
            // rm_single_product.update_cart_topbar();
        })
        .on('change', ".package_variant", function () { // update availability on package product variant changes
            rm_single_product.update_package_availability();
        })
        .on('click', ".add_to_cart_button_list", function () {
            rm_single_product.product_id = $(this).attr('data-product_id');
            rm_single_product.variants_products_id = $(this).attr('data-variants_products_id');
            rm_single_product.add_to_cart();
        })
        .on('click', ".remove_from_cart", function () {
            rm_single_product.product_id = $(this).attr('data-product_id');
            rm_single_product.cart_item_id = $(this).attr('data-cart_item_id');
            rm_single_product.remove_from_cart();
        })
        .on('click', ".rentmy_coupon_btn", function () {
            let cart_coupon = $('.rentmy_coupon_text').val().trim();
            if (cart_coupon != '') {
                rm_single_product.coupon_code = cart_coupon;
                rm_single_product.apply_coupon();
            }
        })
        .on('click', ".rentmy_item_quantity_update", function () {
            rm_single_product.cart_item_id = $(this).attr('data-cart_item_id');
            rm_single_product.cart_item_quantity = $(this).attr('data-increment');
            rm_single_product.cart_item_price = $(this).attr('data-cart_item_price');
            rm_single_product.cart_item_option_id = $(this).attr('data-option_id');
            rm_single_product.update_cart_item();
        })
        .on('click', ".rentmy-parent-category", function () {
            let category_item = $(this).attr('data-id');
            $(".rentmy-list-children-wrapper").slideToggle();
        })
        .on('click', ".rentmy-control-input", function () {
            if (!$(this).is(':checked')) {
                $(this).attr('checked', false);
            } else {
                $(this).attr('checked', true);
            }
        })
        .on('click', '.clear-price-filter', function () {
            $('.value-price-filter').val('');
        })
        .on('keypress', '.value-price-filter', function (e) {
            if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
                return false;
            }
        })
        .on('click', "#rentmy-tag-filter-form", function () {
            let tags = [];
            let filterForm = $("#rentmy-tag-filter-form");
            filterForm.find('.rentmy-control-input:checked').each(function () {
                tags.push($(this).val());
            });
            if (tags.length > 0) {
                filterForm.find('.rentmy-tags').val(tags.join(','));
            } else {
                filterForm.find('.rentmy-tags').val('');
            }

            filterForm.on('click', '.tag-filter', function () {
                setTimeout(function () {
                    filterForm.submit();
                }, 100);
            });
        })
        .on('keyup', ".rentmy-add-on-products", function () {
            rm_single_product.min_addon_quantity_check($(this));
        })
        .on('change', ".rentmy-add-on-products", function () {
            rm_single_product.min_addon_quantity_check($(this));
        })
        .on('change', ".rentmy-duration-component", function () {
            rm_single_product.get_dates_from_duration();
        })
        .on("click", ".date-range-selection-change", function () {
            $('.date-range-selection-default').hide();
            $('.date-range-selection-active').show();
            rm_single_product.trigger_selected_date_range();
        })
        .on("click", ".date-range-selection-cancel", function () {
            $('.date-range-selection-default').show();
            $('.date-range-selection-active').hide();
        })
        .on("click", "a.cart-bar", function () {
            $('div.cart-body').toggle('fast');
        }).on("click", "a.rentmy-toggle-menu", function () {
        var targetLink = $(this);
        targetLink.next('ul.rentmy-child-wrapper').slideToggle('fast', function () {
            targetLink.find('span.dashicons').toggleClass('dashicons-arrow-down-alt2 dashicons-arrow-up-alt2');
        });
    }).on("change", "#duration", function () {

        // if availablility is at least 1 or it will be disabled by default
        if ($('#rentmy-rent-item').data('attr-default_availability') != false) {
            if ($(this).val() == '') {
                $('#rentmy-rent-item').attr('disabled', true);
            }
        }

        var exactTime = $('#exact_time');
        if (exactTime.length === 0) {
            return;
        }
        var is_selected = $(this).find('option:selected').attr('data-times');

        if (is_selected != undefined) {
            var targetOptionTime = $(this).find('option:selected').attr('data-times').trim();
            targetOptionTime = targetOptionTime.split(',');
            if (targetOptionTime.length > 0 && exactTime.length > 0) {
                exactTime.html($('<option value="">-Select-</option>'));
                for (var key in targetOptionTime) {
                    exactTime.append($('<option value="' + targetOptionTime[key] + '">' + targetOptionTime[key] + '</option>'));
                }
            }
        }
    })
        .on('click', '.rm-delivery-option ul li', function (){
            let flow = localStorage.getItem('deliveryFlow');
            if (flow){
                return;
            }
            let config = $(this).attr('data-delivery_flow');
            config = config.split("%rentmy%").join(" ");
            $(".rm-delivery-option ul li").removeClass("delivery-option-active");
            $(this).attr('class', 'delivery-option-active');
            rm_single_product.delivery_flow = config;

        });

    $(document).ready(function () {
        rm_single_product.init();
    })
    moment.suppressDeprecationWarnings = true;
});
