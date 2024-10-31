<?php

class RentMy_Customer extends RentMy{

    private $customerToken = '';
    function __construct() {
        if(!headers_sent() && '' == session_id())
            session_start();

        if (!empty($_SESSION['customer_info'])){
            $this->customerToken = $_SESSION['customer_info']['token'];
        }else{
            if(!empty($_COOKIE['rentmy_customer_info'])){
                $customerInfo = json_decode($_COOKIE['rentmy_customer_info'], true);
                $this->customerToken = $customerInfo['token'];
            }
        }
    }

    public function login($params){

        try{
                $response = self::rentmy_fetch(
                    '/customers/login',
                    get_option('rentmy_accessToken'),
                    $params,
                    null
                );

                if($response['status'] == 'OK'){
                    $this->setCustomer($response['result']['data']);
                    $redirect_url = (!empty($params['from_shortcode'])) ? 
                                    home_url() . '/' . get_post(get_option('rentmy.page_url.profile'))->post_name : '';

                    $credentials = [
                        'user_login'    => $params['email'],
                        'user_password' => $params['password'],
                        'remember'      => true
                    ];
                    self::wp_user_login($credentials);

                    return [
                        'status'=>'OK',
                        'result'=> [
                            'data' => $response['result']['data'],
                        ],
                        'redirect_to_profile' => $redirect_url
                    ];


                }else{
                    $response = [
                    'status'=>'NOK',
                    'result'=> [
                        'message' => 'Credentials do not match!'
                    ]
                ];



                return $response;

                }

//            }

        }catch(Exception $e){

        }
    }


    /*
    *
    *Customer Registation
    *@params
    *
    */
    public function register($params){
        try{
            $first_name = $params['first_name'];
            $last_name = $params['last_name'];
            $mobile = $params['contact'];
            $address_line1 = $params['address_line1'];
            $country = $params['country'];
            $city = $params['city'];
            $state = $params['state'];
            $zipcode = $params['zipcode'];
            $email = $params['email'];
            $user_name = $params['username'];
            $password = $params['password'];
            $confirm_password = $params['confirm_password'];
            $data = $params;

            if($first_name==''){
                $error[] = 'First name is required';
            }
            if($email==''){
                $error[] = 'Email is required';
            }
            if($user_name==''){
                $error[] = 'Username is required';
            }
            if($mobile == ''){
                $error[] = 'Mobile is required';
            }
            if($address_line1 == ''){
                $error[] = 'Address Line 1 is required';
            }
            if($country == ''){
                $error[] = 'Country is required';
            }
            if($city == ''){
                $error[] = 'City is required';
            }
            if($state == ''){
                $error[] = 'State is required';
            }
            if($zipcode == ''){
                $error[] = 'Zipcode is required';
            }
            if($password==''){
                $error[] = 'Password is required';

            }else{
                if($confirm_password==''){
                    $error[] = 'Confirm password is required';
                }else{
                    if($confirm_password != $password){
                        $error[] = "Password doesn't match";
                    }
                }
            }

            if(!empty($error)){
                $response = [
                    'status'=>'NOK',
                    'result'=> [
                        'message' => $error
                    ]
                ];
                return $response;
            }

            $userdata = array(
                'first_name' => $first_name,
                'user_login' => $user_name,
                'user_pass'	=> $password,
                'user_email' => $email,
                'role'		=> 'customer'
            );

            $user_id = wp_insert_user($userdata);
            if(!$user_id){
                return ;
            }
            $response = $this->register_in_rentmy($params);

            if($response['status'] == 'OK'){
                $credentials = [
                    'user_login'    => $email,
                    'user_password' => $password,
                    'remember'      => true
                ];
                self::wp_user_login($credentials);

                $this->setCustomer($response['result']['data']);
            }

            return $response;
        }catch(Exception $e){

        }
    }

    public function register_in_rentmy($params){
        $response = self::rentmy_fetch(
            '/customers/register',
            get_option('rentmy_accessToken'),
            $params,
            null
        );
        return $response;
    }
    /*
    *
    *Customer profile
    *@params
    *
    */
    public function profile(){
        try{

            $response = self::fetch(
                '/customers/profile',
                [
                    'token' => $this->customerToken,
                    'location' => get_option('rentmy_locationId')
                ]
            );
            return $response;
        }catch(Exception $e){

        }
    }



    public function updateProfile($params){
        try{
            $response = self::rentmy_fetch(
                '/customers/profile',
                $this->customerToken,
                $params
            );
            return $response;
        }catch(Exception $e){

        }
    }

    public function change_avatar($params){
        try{
            $response = self::rentmy_fetch(
                '/customers/change-avatar',
                $this->customerToken,
                $params
            );
            return $response;
        }catch(Exception $e){

        }
    }
    /*
    *
    *Customer logout - destroy session
    *
    *
    */
    // public function logout(){
    //     $_SESSION['customer_info'] = [];
    // }


    public function address(){

        try{

            $response = self::fetch(
                '/customers/address?type=Primary',
                [
                    'token' => $this->customerToken,
                    'location' => get_option('rentmy_locationId')
                ]
            );
            return $response;
        }catch(Exception $e){

        }
    }


    public function new_address($params){

        try{
            $type = isset($params['type'])?$params['type']:'Primary';
            $response = self::rentmy_fetch(
                '/customers/address?type='. $type,
               $this->customerToken,
               $params,
               null

            );
            return $response;
        }catch(Exception $e){

        }
    }

    public function updated_address($params){

        try{
            $type = isset($params['type'])?$params['type']:'Primary';
            $response = self::rentmy_fetch(
                '/customers/address/'. $params['id'] .'?type='. $type,
               $this->customerToken,
               $params,
               null

            );
            return $response;
        }catch(Exception $e){

        }
    }




    public function delete_address($params){
        try{
            $response = self::http_delete(
                '/customers/address/'. $params['id'],
               $this->customerToken,
               $params,
               null

            );
            return $response;
        }catch(Exception $e){

        }
    }

    /*
    *
    *Customer order
    *@params
    *
    */
    public function orders($params){
        try{

            $response = self::fetch(
                '/customers/orders?page_no=' . $params['page_no'] .'&limit='.$params['limit'],
                [
                    'token' => $this->customerToken,
                    'location' => get_option('rentmy_locationId')
                ]
            );
            return $response;
        }catch(Exception $e){

        }
    }

    public function order_details($data){
        try{

            $response = self::fetch(
                '/customers/orders/'. $data['order_id'],
                [
                    'token' => $this->customerToken,
                    'location' => get_option('rentmy_locationId')
                ]
            );
            return $response;
        }catch(Exception $e){

        }
    }

    public function change_order_status($data){
        try{

            $response = self::fetch(
                '/customers/orders/'. $data['order_id'] .'/status/'. $data['status'],
                [
                    'token' => $this->customerToken,
                    'location' => get_option('rentmy_locationId')
                ]
            );
            return $response;
        }catch(Exception $e){

        }
    }


    public function change_password($params){
        try{

            $response = self::rentmy_fetch(
                '/customers/change-password',
                $this->customerToken,
                $params
            );
            return $response;
        }catch(Exception $e){

        }
    }


    public function forgot_password($params){
        try{

            $response = self::rentmy_fetch(
                '/customers/forgot-password',
                get_option('rentmy_accessToken'),
                $params
            );
            return $response;
        }catch(Exception $e){

        }
    }

    public function reset_password($params){
        try{

            $response = self::rentmy_fetch(
                '/customers/reset-password/' . $params['activation_key'],
                get_option('rentmy_accessToken'),
                $params
            );
            return $response;
        }catch(Exception $e){

        }
    }


    public function sendUserMessage($params){
        try{
            $response = self::rentmy_fetch(
                '/send-feedback',
            $this->customerToken,
                $params
            );
            return $response;
        }catch(Exception $e){

        }
    }

    /**
     * Get Customer info from session
     */
    function getCustomer()
    {
        if(!empty($_SESSION['customer_info'])){
           return $_SESSION['customer_info'];
        }else{
           return json_decode(@$_COOKIE['rentmy_customer_info'], true);
        }
    }

    /** Set customer info to Session */
    function setCustomer($data)
    {
        $_SESSION['customer_info'] = $data;
        $this->setCookie($data);
    }

}
