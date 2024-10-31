


let pickTodayTomorrowDate = true;
let selectStartDate = false;
let selectEndDate = false;
function handleRentBuyChange() {
    var rental_type_checked = jQuery('.rental-type input[name=rental_type]:checked').val();
    if( rental_type_checked == 'buy' ) {
        jQuery('.end-date-box').hide();
    } else if( rental_type_checked == 'rent' ) {
        jQuery('.end-date-box').show();
    }
}

function handleDaterangeEnd() {
    if (selectEndDate){
        setStartEndDates();
        rm_single_product.get_price_value();
    }
}

function handleDaterangeStart() {
    setStartEndDates();
    var priceOption = jQuery('.price-options input:checked');
    let priceId = priceOption.attr('data-price_id');

    console.log("pickTodayTomorrowDate")
    console.log(pickTodayTomorrowDate)
    if (!pickTodayTomorrowDate)
        rm_single_product.get_dates_price_duration(priceId);
}


function setStartEndDates() {

    let dateFormat = rm_single_product.dateFormatInitial();
    let daterangeStart = jQuery('.daterangeStart').val();
    let daterangeEnd = jQuery('.daterangeEnd').val();
    /*
    if( moment(daterangeStart, dateFormat).unix() > moment(daterangeEnd, dateFormat).unix() ) {
        daterangeEnd = moment(daterangeStart, dateFormat).add(1, 'hour').format(dateFormat);
        jQuery('.daterangeEnd').val( daterangeEnd );
    }
    */
    if (selectEndDate || !pickTodayTomorrowDate){
        rm_single_product.rent_start = moment(daterangeStart).format('YYYY-MM-DD HH:mm');
        rm_single_product.rent_end = moment(daterangeEnd).format('YYYY-MM-DD HH:mm');
    }

    var rmDate = jQuery('#rm-date');

    rmDate.data('daterangepicker').setStartDate(moment(daterangeStart).format(dateFormat));
    rmDate.data('daterangepicker').setEndDate(moment(daterangeEnd).format(dateFormat));

    rmDate.val(daterangeStart + " - " + daterangeEnd).data('start_date', daterangeStart).data('end_date', daterangeEnd);

    jQuery('#rent-start-date-text').html( daterangeStart );
    jQuery('#rent-end-date-text').html( daterangeEnd );
    // pickTodayTomorrowDate = false;
    // selectStartDate = false;
    // selectEndDate = false;
}

function getStartEndDates() {

    let dateFormat = rm_single_product.dateFormatInitial();
    let rmDate = jQuery('#rm-date');
    let startDate = rmDate.data('daterangepicker').startDate._i;

    let endDate = rmDate.data('daterangepicker').endDate._i;
    //endDate = moment(endDate).format(dateFormat);

    jQuery('.daterangeStart').val( startDate ).data('daterangepicker').setStartDate( startDate );
    jQuery('.daterangeStart').data('daterangepicker').setStartDate( startDate );
    jQuery('.daterangeStart').data('daterangepicker').setEndDate( startDate );


    jQuery('.daterangeEnd').val( endDate );
    jQuery('.daterangeEnd').data('daterangepicker').setStartDate( endDate );
    jQuery('.daterangeEnd').data('daterangepicker').setEndDate( endDate );

    jQuery('#rent-start-date-text').html( startDate );
    jQuery('#rent-end-date-text').html( endDate );

}

function handleRentalPriceChange() {

/*
    var priceOption = jQuery('.price-options input:checked');

console.log('77:priceOption', priceOption);

    if( priceOption ) {

       var priceOptionDuration = priceOption.data('duration');
       var priceOptionLabel = priceOption.data('label');

console.log('84:priceOptionLabel', priceOptionLabel);

       if(priceOptionLabel != 'hours') {
           var rent_end = moment( jQuery('#rm-date-end').data('daterangepicker').startDate, 'YYYY-MM-DD hh:mm A' ).format('YYYY-MM-DD') + moment(jQuery('#rm-date-start').data('daterangepicker').startDate, 'MM-DD-YYYY hh:mm A').format(' hh:mm A');

           rent_end = moment(rent_end,  'YYYY-MM-DD hh:mm A').format('YYYY-MM-DD');

           jQuery('#rm-date-end').data('daterangepicker').setStartDate(rent_end);

console.log('88:rent_end', rent_end);


       }


    }
*/


}



jQuery('.rentmy-plugin-manincontent .rentmy-product-details .radio-container input').on('change', function() {
    var me = this
    jQuery(me).parent('.radio-container').siblings('.radio-container').removeClass('active');
    jQuery(me).parent('.radio-container').addClass('active');
    return false;
})

function addActiveToParent(e) {
    //console.log('addActiveToParent()')
    //console.log('e', e);
    jQuery(e).parent('.radio-container').siblings('.radio-container').removeClass('active');
    jQuery(e).parent('.radio-container').addClass('active');
    return false;
}


function handleStartDateType() {

    let dateFormat = rm_single_product.dateFormatInitial();

    var checkedVal = jQuery('.rental_type_rent:checked').val() || false;

    if( !checkedVal ) return false;

    jQuery('.show_earliest_start_date_box label.rent_input').removeClass('active');
    jQuery('.show_start_date_box label.rent_input').removeClass('active');

    if( checkedVal == 'Custom' ) {

        jQuery('.show_start_date_box input[value="Custom"]').parent('label.rent_input').addClass('active');
        pickTodayTomorrowDate = false;
        selectStartDate = true;
        selectEndDate = false;
    } else if( checkedVal == 'Tomorrow' ) {

        jQuery('.show_earliest_start_date_box input[value="Tomorrow"]').parent('label.rent_input').addClass('active');

        //jQuery('#rm-date-start').val( moment().add(1, 'days').format(dateFormat) ).trigger('change');
        //jQuery('#rent-start-date-text').html( moment().add(1, 'days').format(dateFormat) );

        var dateStart = moment().add(1, 'days').format(rm_single_product.config.date_format);
        var timeStart = moment(rm_single_product.rent_start, dateFormat).format(' hh:mm A');

        if( timeStart != 'Invalid date' ) {
            dateStart = dateStart + timeStart;
        }

        jQuery('#rm-date-start').val( dateStart ).trigger('change');
        jQuery('#rent-start-date-text').html( dateStart );

        jQuery('.price-options input[name=rental-price]:checked').trigger('click');
        handleRentalPriceChange();

        pickTodayTomorrowDate = true;
        selectStartDate = true;
        selectEndDate = false;
    } else if( checkedVal == 'Today' ) { // Today

        jQuery('.show_earliest_start_date_box input[value="Today"]').parent('label.rent_input').addClass('active');

        //jQuery('#rm-date-start').val( moment().format(dateFormat) ).trigger('change');
        //jQuery('#rent-start-date-text').html( moment().format(dateFormat) );

        var dateStart = moment().format(rm_single_product.config.date_format);
        var timeStart = moment(rm_single_product.rent_start, dateFormat).format(' hh:mm A');

        if( timeStart != 'Invalid date' ) {
            dateStart = dateStart + timeStart;
        }

        jQuery('#rm-date-start').val( dateStart ).trigger('change');
        jQuery('#rent-start-date-text').html( dateStart );

        jQuery('.price-options input[name=rental-price]:checked').trigger('click');
        handleRentalPriceChange();
        pickTodayTomorrowDate = true;
        selectStartDate = true;
        selectEndDate = false;
    }



}



function setInputFilter(textbox, inputFilter, errMsg) {
  ["input", "keydown", "keyup", "mousedown", "mouseup", "select", "contextmenu", "drop", "focusout"].forEach(function(event) {
    textbox.addEventListener(event, function(e) {
      if (inputFilter(this.value)) {
        // Accepted value
        if (["keydown","mousedown","focusout"].indexOf(e.type) >= 0){
          this.classList.remove("input-error");
          this.setCustomValidity("");
        }
        this.oldValue = this.value;
        this.oldSelectionStart = this.selectionStart;
        this.oldSelectionEnd = this.selectionEnd;
      } else if (this.hasOwnProperty("oldValue")) {
        // Rejected value - restore the previous one
        this.classList.add("input-error");
        this.setCustomValidity(errMsg);
        this.reportValidity();
        this.value = this.oldValue;
        this.setSelectionRange(this.oldSelectionStart, this.oldSelectionEnd);
      } else {
        // Rejected value - nothing to restore
        this.value = "";
      }
    });
  });
}


function handleVariantSetsClick() {

    if( jQuery('.buy_input').hasClass('active') ) {
        if( jQuery('#togBtn').prop('checked') != false ) {
            jQuery('#togBtn').prop('checked', false);
        }
    } else if( jQuery('.rent_input').hasClass('active') ) {
        if( jQuery('#togBtn').prop('checked') != true ) {
            jQuery('#togBtn').prop('checked', true);
        }
    }
}


function disableAddToCartAndHideCalendar() {


    if( rm_single_product.config.show_earliest_start_date == 0
        && rm_single_product.config.show_start_date == true
        && rm_single_product.rental_type == 'rent' ) {
        jQuery('#rentmy-rent-item').prop('disabled', true);
        /*
        for (let i = 0; i < 11; i++) {
console.log('disable ADD TO CART');
          setTimeout(function() { jQuery('#rentmy-rent-item').prop('disabled', true); }, i * 1000);
        }
        */
        jQuery('.rm-date-start-box').hide();
        jQuery('.rm-date-end-box').hide();
    }

}

    jQuery(document).ready(function() {
        disableAddToCartAndHideCalendar();
        let dateFormat = rm_single_product.dateFormatInitial();

        var minDate = jQuery('.daterange').data('min_date');

        if( minDate == undefined ) {
            var minDate = jQuery('#rm-date').data('min_date');
        }

        var show_start_time = rm_single_product.config.show_start_time;
        if( jQuery('#is_exact_time').val() == 1 ) {
            dateFormat = dateFormat.replace(' hh:mm A', '');
            show_start_time = false;
        }


        jQuery('.rental-type input[name=rental_type]').on('change', handleRentBuyChange);
        handleRentBuyChange();

// 11-12-2022 08:00 am
        jQuery('.daterangeStart').daterangepicker({
          singleDatePicker: true,
          minDate: moment(minDate, 'MM-DD-YYYY h:m A').format(dateFormat),
          startDate: moment(minDate, 'MM-DD-YYYY h:m A').format(dateFormat), // Set default date for prevent "invalid date" isset
          timePicker: show_start_time,
          locale: {
            format: dateFormat
          }
        }, function(start, end, label) {

        });

        jQuery('.daterangeEnd').daterangepicker({
            singleDatePicker: true,
            minDate: moment(minDate, 'MM-DD-YYYY h:m A').format(dateFormat),
            startDate: moment(minDate, 'MM-DD-YYYY h:m A').format(dateFormat),
            timePicker: rm_single_product.config.show_end_time,
            locale: {
              format: dateFormat
            }
        }, function(start, end, label) {
        });


        jQuery('.daterangeEnd').on('change', handleDaterangeEnd);
        jQuery('.daterangeStart').on('change', handleDaterangeStart);
        jQuery('.price-options input[name=rental-price]').on('change', handleRentalPriceChange);

        if( rm_single_product.config.show_end_date == false ) {
            jQuery('.end-date-box').hide();
        }

        if( rm_single_product.config.show_start_date == false ) {
            jQuery('.show_start_date_box').hide();
        }


        if( rm_single_product.config.show_earliest_start_date == 0 ) {
            jQuery('.show_earliest_start_date_box').hide();
        }

/*
        var minDateU = moment(minDate, 'MM-DD-YYYY h:m A').format('X')
        var todayU = moment().format('X')
        var tomorrowU = moment().add(1, 'day').format('X')

console.log('minDateU - todayU', (minDateU - todayU) / 86400 );
console.log('minDateU - tomorrowU', (minDateU - tomorrowU) / 86400 );

        if( minDateU > todayU ) {
            jQuery('.show_earliest_start_date_box label').eq(0).hide();
        }

        if( minDateU > tomorrowU ) {
            jQuery('.show_earliest_start_date_box label').eq(1).hide();
        }
*/

        var lead_time = parseInt(rm_single_product.config.order.lead_time_value);

        if( lead_time && lead_time >= 1 ) {
            jQuery('.show_earliest_start_date_box label').eq(0).hide(); // Hide Today
        }

        if( lead_time && lead_time >= 2 ) {
            jQuery('.show_earliest_start_date_box label').eq(1).hide(); // Hide Tomorrow
        }


        jQuery('#rm-date').hide();

        jQuery('.rental_type_rent').on('change', function() {
            var checkedVal = jQuery('.rental_type_rent:checked').val() || false;
            if (checkedVal == 'Today' || checkedVal == 'Tomorrow'){
                pickTodayTomorrowDate = true;
            }
            selectStartDate = true;
            selectEndDate = false;
            handleStartDateType();
        });

        //jQuery('.rental_type_rent').first().trigger('click');

        jQuery('.rental_type_rent[value=Custom]').on('click', function() {
            jQuery('.rm-date-start-box').show();
            jQuery('.rm-date-end-box').show();
            jQuery('#rentmy-rent-item').prop('disabled', false);
            jQuery('#rm-date-start').click();
        });

        jQuery('.pick-end-date').on('click', function() {
            pickTodayTomorrowDate = false;
            selectStartDate = false;
            selectEndDate = true;
            jQuery('.rm-date-end-box').show();
            jQuery('#rm-date-end').click();
        });

        setInputFilter(document.getElementById("rm_quantity"), function(value) {
          return /^\d*$/.test(value); // Allow digits and '.' only, using a RegExp
        }, "Only digits are allowed");




        jQuery('.rental-type input[type="radio"]').click(function () {

            var inputValue = jQuery(this).attr("value");

            if (inputValue == "buy") {
              jQuery('#toggleswitch').addClass("toggleon");
              jQuery('#toggleswitch').removeClass("toggleoff");
              jQuery('#togBtn').prop('checked', false);
            }
            if (inputValue == "rent") {
              jQuery('#toggleswitch').addClass("toggleoff");
              jQuery('#toggleswitch').removeClass("toggleon");
              jQuery('#togBtn').prop('checked', true);
            }
            jQuery('.daterangepicker').hide();

        });

        jQuery('.rental-type #togBtn').click(function () {
            if( jQuery(this).prop('checked') ) {
                setTimeout(function(){
                    jQuery('label.rent_input').trigger('click');
                }, 100);

            } else {
                jQuery('label.buy_input').trigger('click');
            }
            jQuery('.daterangepicker').hide();
        });


/*
        jQuery('.variantSets input[type=radio]').on('click', function() {
            setTimeout(handleVariantSetsClick, 1000);
            setTimeout(handleVariantSetsClick, 2000);
            setTimeout(handleVariantSetsClick, 4000);
            setTimeout(handleVariantSetsClick, 6000);
            setTimeout(handleVariantSetsClick, 8000);
            setTimeout(handleVariantSetsClick, 10000);
        })
*/

        //setInterval(handleVariantSetsClick, 1000);


         jQuery('.duration_box input[type="radio"]').on('click', function (e) {

            let id = jQuery(e.target).data('id');
            jQuery('.time-selection').hide();
            jQuery('.time-selection-' + id ).show();
            jQuery('.timeButton').prop('checked', false).parent('label.radio-container').removeClass('active');
            jQuery('.time-selection-' + id).find('.timeButton').first().prop('checked', true).parent('label.radio-container').addClass('active');

            rm_single_product.get_dates_from_duration();

          });


//console.log('rm_single_product.config', rm_single_product.config);

    });
