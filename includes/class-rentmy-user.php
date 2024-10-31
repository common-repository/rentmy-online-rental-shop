<?php

class RentMy_User extends RentMy{

 public function register($params)
 {

     try{

         if($params['user_type_id'] == 1){
             $params['type'] = $params['user_type_id'];
             $response = self::rentmy_fetch(
                 '/customers/register',
                 get_option('rentmy_accessToken'),
                 $params,
                 null
             );

         }else{
             $response = self::rentmy_fetch(
                 '/stores/user',
                 get_option('rentmy_accessToken'),
                 $params,
                 null
             );


         }
         return $response;
     }catch(Exception $e){

     }
    try{
        $response = self::rentmy_fetch(
            '/stores/user',
            get_option('rentmy_accessToken'),
            $params,
            null
        );

        return $response;
    }catch(Exception $e){

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
            $_SESSION['RentMy']['user']['token'] = $response['result']['data']['token'];
             $this->setCustomer($response['result']['data']);
         }
         return $response;
     }catch(Exception $e){

     }
 }

function setCustomer($data)
{
    $_SESSION['customer_info'] = $data;
    $this->setCookie($data);
}

}