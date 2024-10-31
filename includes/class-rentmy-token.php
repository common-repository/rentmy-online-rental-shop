<?php

Class RentMy_Token extends RentMy
{
    public $apiKey;
    public $apiSecret;

    public function __construct()
    {
        $this->apiKey = get_option('rentmy_apiKey');
        $this->apiSecret = get_option('rentmy_secretKey');
    }


    /**
     * Get AccessToken
     * @return mixed
     * @todo check domain name
     */
    public function getToken()
    {
        if (empty($this->apiKey) || empty($this->apiSecret))
            return;

        try {
            $response = $this->fetch(
                '/apps/access-token',
                null,
                [
                    'api_key' => $this->apiKey,
                    'api_secret' => $this->apiSecret,
                    'source'=> 'WP Plugin'
                ]
            );
            if (!empty($response['data']['token'])) {
                update_option('rentmy_accessToken', $response['data']['token'] );
                update_option('rentmy_refreshToken', $response['data']['refresh_token'] );
                update_option('rentmy_storeId', $response['data']['store_id'] );
                update_option('rentmy_storeName', $response['data']['store_name'] );
                update_option('rentmy_locationId', $response['data']['location_id'] );
                update_option('rentmy_storeCountry', $response['data']['country'] );
                update_option("rentMyLocationWiseToken", true);
            }
        } catch (Exception $e) {

        }

    }
    /**
     * Get Store token when store name from url
     * @param $type
     * @return mixed
     */
    public function getTokenFromStoreName($name, $params=[])
    {
        try {
            $disable_default_time = $params['disable_default_time'] ?? false;
            $response = self::rentmy_fetch(
                '/apps/access-token',
                null,
                [
                    'store_name' => $name,
                    'disable_default_time' => $disable_default_time
                ]
            );
            $response = $response['result'];
            if ($response['data']['token']){
                update_option('rentmy_accessToken', $response['data']['token'] );
                update_option('rentmy_refreshToken', $response['data']['refresh_token'] );
                update_option('rentmy_storeId', $response['data']['store_id'] );
                update_option('rentmy_storeName', $response['data']['store_name'] );
                update_option('rentmy_locationId', $response['data']['location_id'] );
//                update_option('rentmy_storeCountry', $response['data']['country'] );
            }
        } catch (Exception $e) {

        }
    }

}
