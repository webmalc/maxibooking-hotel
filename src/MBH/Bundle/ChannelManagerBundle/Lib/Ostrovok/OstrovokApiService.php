<?php

/**
 * Created by Zavalyuk Alexandr (Zalex).
 * email: zalex@zalex.com.ua
 * Date: 13.12.16
 * Time: 18:47
 */
class OstrovokApiService
{
    protected $_auth_token;
    protected $_private_token;

    public function __construct(string $endpoint, string $auth_token, string $private_token)
    {
        $this->_endpoint = $endpoint;
        $this->_auth_token = $auth_token;
        $this->_private_token = $private_token;
    }

    private function createSignature($data)
    {
        $is_list = false;
        if (is_array($data)) {
            if (count($data) > 0) {
                if (is_int($data[0])) {
                    $is_list = true;
                }
            }
        }

        if (is_array($data) && !$is_list) {
            ksort($data);
            $tmp = array();
            foreach ($data as $key => $value) {
                $tmp[] = array($this->createSignature($key), $this->createSignature($data[$key]));
            }
            $result = array();
            foreach ($tmp as $key => $value) {
                $result[] = implode("=", $value);
            }
            return implode(";", $result);
        } elseif (is_array($data) && $is_list) {
            $result = array();
            foreach ($data as $value) {
                $result[] = $this->createSignature($value);
            }
            $result = implode(";", $result);
            if (count($data) > 1) {
                $result = ("[" . $result . "]");
            }
            return $result;
        } elseif (is_bool($data)) {
            return $data ? "true" : "false";
        }

        return (string)$data;
    }

    private function getSignature(array $data, $private)
    {
        $data['private'] = $private;
        return md5($this->createSignature($data));
    }

    private function __callGET($method_url, array $params)
    {
        $params["token"] = $this->_auth_token;
        $params["sign"] = $this->getSignature($params, $this->_private_token);
        $final_url = $this->_endpoint . $method_url . "?" . http_build_query($params) . "&";
        return file_get_contents($final_url);
    }

    private function __callPUT($method_url, array $params, array $GET_params = array())
    {
        $sign_params = $params;
        $sign_params["token"] = $this->_auth_token;
        $GET_params["token"] = $this->_auth_token;
        $GET_params["sign"] = $this->getSignature($sign_params, $this->_private_token);

        $final_url = $this->_endpoint . $method_url . "?" . http_build_query($GET_params);
        $curl = curl_init($final_url);

        $data_json = json_encode($params);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_json);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data_json),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    private function __callPOST($method_url, array $params, array $GET_params = array())
    {
        $sign_params = $params;
        $sign_params["token"] = $this->_auth_token;
        $GET_params["token"] = $this->_auth_token;
        $GET_params["sign"] = $this->getSignature($sign_params, $this->_private_token);

        $final_url = $this->_endpoint . $method_url . "?" . http_build_query($GET_params);
        $curl = curl_init($final_url);

        $data_json = json_encode($params);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_json);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data_json),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    public function getHotels(array $params = array())
    {
        return $this->__callGET("hotels/", $params);
    }

    public function getRoomCategories(array $params = array())
    {
        return $this->__callGET("room_categories/", $params);
    }

    public function getMealPlans(array $params = array())
    {
        return $this->__callGET("meal_plans/", $params);
    }

    public function getOrders(array $params = array())
    {
        return $this->__callGET("orders/", $params);
    }

    public function getBookings(array $params = array())
    {
        return $this->__callGET("bookings/", $params);
    }

    public function getRNA($plan_date_start_at, $plan_date_end_at, array $params = array())
    {
        $params["plan_date_start_at"] = $plan_date_start_at;
        $params["plan_date_end_at"] = $plan_date_end_at;
        return $this->__callGET("rna/", $params);
    }

    public function getRatePlans(array $params = array())
    {
        return $this->__callGET("rate_plans/", $params);
    }

    public function updateRNA(array $params = array())
    {
        return $this->__callPUT("rna/", $params);
    }

    public function createRNA(array $params = array())
    {
        return $this->__callPOST("rna/", $params);
    }

    public function createRatePlan($hotel = null, $room_category = null, array $rate_plan_params)
    {
        $GET_params = array();
        if (!is_null($hotel)) {
            $GET_params["hotel"] = $hotel;
        }
        if (!is_null($room_category)) {
            $GET_params["room_category"] = $room_category;
        }
        return $this->__callPOST("rate_plans/", $rate_plan_params, $GET_params);
    }

    public function updateRatePlan($id, $hotel = null, $room_category = null, array $rate_plan_params = array())
    {
        $GET_params = array();
        $GET_params["id"] = $id;
        if (!is_null($hotel)) {
            $GET_params["hotel"] = $hotel;
        }
        if (!is_null($room_category)) {
            $GET_params["room_category"] = $room_category;
        }
        return $this->__callPUT("rate_plans/", $rate_plan_params, $GET_params);
    }
}




$auth_token = '722ac50470d8af33d509c069ccb83443';
$private_token = 'edfdfba3f6902eb63aa254935e9a8a36';

$api_client = new OstrovokApiService("http://extrota-sandbox.ostrovok.ru/echannel/api/v0.1/", $auth_token, $private_token);

$hotels =  $api_client->getHotels();
exit;