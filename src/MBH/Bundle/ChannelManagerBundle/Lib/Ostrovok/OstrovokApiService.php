<?php

/**
 * Created by Zavalyuk Alexandr (Zalex).
 * email: zalex@zalex.com.ua
 * Date: 13.12.16
 * Time: 18:47
 */
class OstrovokApiService
{
    protected $auth_token;
    protected $private_token;
    private $apiUrl;

    public function __construct(string $endpoint, string $auth_token, string $private_token)
    {
        $this->apiUrl = $endpoint;
        $this->auth_token = $auth_token;
        $this->private_token = $private_token;
    }

    private function createSignatureString($data)
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
                $tmp[] = array($this->createSignatureString($key), $this->createSignatureString($data[$key]));
            }
            $result = array();
            foreach ($tmp as $key => $value) {
                $result[] = implode("=", $value);
            }
            return implode(";", $result);
        } elseif (is_array($data) && $is_list) {
            $result = array();
            foreach ($data as $value) {
                $result[] = $this->createSignatureString($value);
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
        return md5($this->createSignatureString($data));
    }

    private function __callGET($api_method, array $data)
    {
        $data["token"] = $this->auth_token;
        $data["sign"] = $this->getSignature($data, $this->private_token);
        $final_url = $this->apiUrl . $api_method . "?" . http_build_query($data) . "&";
        return file_get_contents($final_url);
    }

    private function __callPUT($api_method, array $data, array $get_data = array())
    {
        return $this->makeCall("PUT", $api_method, $data, $get_data)
    }

    private function __callPOST($api_method, array $data, array $get_data = array())
    {
        return $this->makeCall("POST", $api_method, $data, $get_data);
    }

    private function makeCall(string $type, string $api_method, array $data, array $get_data = array())
    {
        $signature_data = $data;
        $signature_data["token"] = $this->auth_token;
        $get_data["token"] = $this->auth_token;
        $get_data["sign"] = $this->getSignature($signature_data, $this->private_token);

        $final_url = $this->apiUrl . $api_method . "?" . http_build_query($get_data);
        $curl = curl_init($final_url);

        $data_json = json_encode($data);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $type);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_json);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data_json),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    public function getHotels(array $data = array())
    {
        return $this->__callGET("hotels/", $data);
    }

    public function getRoomCategories(array $data = array())
    {
        return $this->__callGET("room_categories/", $data);
    }

    public function getMealPlans(array $data = array())
    {
        return $this->__callGET("meal_plans/", $data);
    }

    public function getOrders(array $data = array())
    {
        return $this->__callGET("orders/", $data);
    }

    public function getBookings(array $data = array())
    {
        return $this->__callGET("bookings/", $data);
    }

    public function getRNA($plan_date_start_at, $plan_date_end_at, array $data = array())
    {
        $data["plan_date_start_at"] = $plan_date_start_at;
        $data["plan_date_end_at"] = $plan_date_end_at;
        return $this->__callGET("rna/", $data);
    }

    public function getRatePlans(array $data = array())
    {
        return $this->__callGET("rate_plans/", $data);
    }

    public function updateRNA(array $data = array())
    {
        return $this->__callPUT("rna/", $data);
    }

    public function createRNA(array $data = array())
    {
        return $this->__callPOST("rna/", $data);
    }

    public function createRatePlan($hotel = null, $room_category = null, array $rate_plan_params)
    {
        $get_data = array();
        if (!is_null($hotel)) {
            $get_data["hotel"] = $hotel;
        }
        if (!is_null($room_category)) {
            $get_data["room_category"] = $room_category;
        }
        return $this->__callPOST("rate_plans/", $rate_plan_params, $get_data);
    }

    public function updateRatePlan($id, $hotel = null, $room_category = null, array $rate_plan_params = array())
    {
        $get_data = array();
        $get_data["id"] = $id;
        if (!is_null($hotel)) {
            $get_data["hotel"] = $hotel;
        }
        if (!is_null($room_category)) {
            $get_data["room_category"] = $room_category;
        }
        return $this->__callPUT("rate_plans/", $rate_plan_params, $get_data);
    }
}




$auth_token = '722ac50470d8af33d509c069ccb83443';
$private_token = 'edfdfba3f6902eb63aa254935e9a8a36';

$api_client = new OstrovokApiService("http://extrota-sandbox.ostrovok.ru/echannel/api/v0.1/", $auth_token, $private_token);

$hotels =  $api_client->getHotels();
exit;