jQuery(function ($) {
    
    var rm_customer = {
        store_config: rm_store_config,
        is_sso: false,
        is_loggedIn: false,
        init: function (){
            this.is_sso = (this.store_config && this.store_config.customer && this.store_config.customer.wp && this.store_config.customer.wp.sso)?this.store_config.customer.wp.sso:false;
            this.checkUserLogin();
        },
        checkUserLogin: function(){
            let ref = this;
            data = {
                action_type: 'customer_login_check',
                action: 'rentmy_options'
            };
            jQuery.post(rentmy_ajax_object.ajaxurl, data, function(response) {
                ref.is_loggedIn = response.is_login;
                console.log(ref.is_loggedIn);
                if (!response.is_login && ref.is_sso){
                    ref.customerLoginModalShow();
                }
            });

        },
        login: function(data){
            let ref = this;
            data.action_type = "customer_login";
            data.action =  "rentmy_options";
            ref.loading(true);
            jQuery.post(rentmy_ajax_object.ajaxurl, data, function(response){
                ref.loading(false);
                $('#rm_customer_login_form .rm-customer-login-error').html('');
                if(response.status == 'OK'){
                    if(response.redirect_to_profile){
                        window.location.replace(response.redirect_to_profile);
                    }else{
                        //location.reload();
                        var url = window.location.href;
                        if (url.indexOf('?') > -1){
                           url += '&add-to-cart=true'
                        }else{
                           url += '?add-to-cart=true'
                        }
                        window.location.href = url;
                    }                    
                }else{
                    $('#rm_customer_login_form .rm-customer-login-error').html(response.result.message);
                }
            })
        },
        loading: function (loading = false){
            if(loading == true){
                $('.rentmy-customer-login-modal .login span').html('<i class="fa fa-spinner fa-spin"></i>');
                $('.rentmy-customer-login-modal .login').attr('disabled', true);
            }else{
                $('.rentmy-customer-login-modal .login span').html('');
                $('.rentmy-customer-login-modal .login').attr('disabled', false);
            }
        },
        register: function(data){
            let ref = this;
            data.action_type = "customer_register";
            data.action =  "rentmy_options";
            ref.loading(true);
            $('#rm_customer_signup_submit').html('SIGN UP <i class="fa fa-spinner fa-spin"></i>');
            jQuery.post(rentmy_ajax_object.ajaxurl, data, function(response){
                $('#rm_customer_register_form .rm-customer-lregister-error').html('');
                $('#rm_customer_register_form .rm-customer-lregister-success').html('');

                ref.loading(false);
                $('#rm_customer_signup_submit').html('SIGN UP');
                if(response.status == 'OK'){
                    $('#rm_customer_register_form .rm-customer-lregister-success').html("Registration Successful");
                    location.reload();
                }else{                    
                    if(response.result.message[0] > 1){
                        let html = '<ul class="rentmy-error-wrapper">';
                        response.result.message.forEach(function (error){
                            html += `<li>${error}</li>`
                        })
                        html += '</ul>';
                    }else{
                        html = '<ul><li>'+ response.result.message +'</li></ul><br>';
                    }
                    
                    $('#rm_customer_register_form .rm-customer-lregister-error').html(html);
                }
            })
        },
        customerLoginModalShow: function (){
            $('#rentmy-customer-login-modal').modal('show').on('hide.bs.modal', function (e) {
                e.preventDefault();
            })
        },
        customerLoginModalHide: function (){
            $('#rentmy-customer-login-modal').removeClass('show');
        }
    };

    $('body').on('submit', '#rm_customer_login_form', function(e){
        e.preventDefault();
        let data = $(this).serializeArray().reduce(function(obj, item) {
            obj[item.name] = item.value;
            return obj;
        }, {});
        rm_customer.login(data);
    }).on('submit', '#rm_customer_register_form', function(e){
        e.preventDefault();
        let data = $(this).serializeArray().reduce(function(obj, item) {
            obj[item.name] = item.value;
            return obj;
        }, {});

        rm_customer.register(data);
    });
    var url = window.location.href;
    if(
        url.includes('rentmy-products-list') ||
        url.includes('rentmy-cart') ||
        url.includes('customer-profile') ||
        url.includes('rentmy-checkout') ||
        url.includes('rentmy-package-details') ||
        url.includes('rentmy-product-details') ||
        $('.rentmy-product-list').length > 0
    )
    {
        rm_customer.init();
    }

});
