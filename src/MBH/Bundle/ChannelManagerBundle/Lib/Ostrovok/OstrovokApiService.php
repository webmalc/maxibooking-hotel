<?php

namespace MBH\Bundle\ChannelManagerBundle\Lib\Ostrovok;

class OstrovokApiService
{

    const API_URL = 'http://extrota-sandbox.ostrovok.ru/echannel/api/v0.1/';
//    const API_URL = 'https://echannel.ostrovok.ru/echannel/api/v0.1/';

    protected $auth_token;

    protected $private_token;

    public function __construct(array $config)
    {
        $this->auth_token = $config['ostrovok']['username'];
        $this->private_token = $config['ostrovok']['password'];
    }

    private function createSignatureString($data)
    {
        $is_list = false;
        if (is_array($data) && count($data)) {
            if ($data[0]??false) {
                $is_list = true;
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
        } elseif (is_null($data)) {
            return 'None';
        }

        return (string)$data;
    }

    private function getSignature(array $data, $private)
    {
        $data['private'] = $private;
        $signatureString = $this->createSignatureString($data);
        return md5($signatureString);
    }

    private function callGet($api_method, array $data)
    {
        $data["token"] = $this->auth_token;
        $data["sign"] = $this->getSignature($data, $this->private_token);
        $request = self::API_URL . $api_method . "?" . http_build_query($data) . "&";

        //TODO: Переделать на курл нормальный
        $response = file_get_contents($request);
        if (!$response) {
            throw new OstrovokApiServiceException('No returned request in callGet Method '.get_class($this));
        }
        $response = json_decode($response, true);
        $this->checkErrors($response);
        return $response;
    }

    private function __callPUT($api_method, array $data, array $get_data = array())
    {
        return $this->makeCall("PUT", $api_method, $data, $get_data);
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

        $final_url = self::API_URL . $api_method . "?" . http_build_query($get_data);
        $curl = curl_init($final_url);

        $data_json = json_encode($data);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $type);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_json);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data_json),
        ));

        $response = json_decode(curl_exec($curl), true);
        curl_close($curl);
        $this->checkErrors($response);

        return $response;
    }

    private function checkErrors($response)
    {
        if (!empty($response['error'])) {
            throw new OstrovokApiServiceException(
                is_array($response['error']) ? http_build_query($response['error']) : $response['error']
            );
        };
    }

    public function getHotels(array $data = array())
    {
        return $this->callGet("hotels/", $data);
    }

    public function getRoomCategories(array $data = array())
    {
        $response = $this->callGet("room_categories/", $data);

        return $response['room_categories'];
    }

    public function getMealPlans(array $data = array())
    {
        return $this->callGet("meal_plans/", $data);
    }

    public function getOrders(array $data = array())
    {
        return $this->callGet("orders/", $data);
    }

    public function getBookings(array $data = array())
    {
        return $this->callGet("bookings/", $data);
    }

    public function getRNA($plan_date_start_at, $plan_date_end_at, array $data = array())
    {
        $data["plan_date_start_at"] = $plan_date_start_at;
        $data["plan_date_end_at"] = $plan_date_end_at;
        return $this->callGet("rna/", $data);
    }

    public function getOccupancies(array $data = array(), bool $byKey = false)
    {
        $result = [];
        $response = $this->callGet("occupancies/", $data);
        $data = $response['occupancies'];
        if ($byKey) {
            foreach ($data as $occupancy) {
                $result[$occupancy['id']] = $occupancy;
            }
        } else {
            $result = $data;
        }

        return $result;
    }

    public function getRatePlans(array $data = array(), $isShowDeleted = false)
    {
        $response = $this->callGet("rate_plans/", $data);

        $rate_plans = [];
        foreach ($response['rate_plans'] as $rate) {
            if($rate['status'] === 'X' && !$isShowDeleted) continue;
            $rate_plans[] = $rate;
        }

        return $rate_plans;

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