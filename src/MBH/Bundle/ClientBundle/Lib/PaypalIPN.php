<?php

namespace MBH\Bundle\ClientBundle\Lib;

class PaypalIPN
{
    const STATUS_KEYWORD_VERIFIED = 'VERIFIED';
    const STATUS_KEYWORD_INVALID = 'INVALID';

    /**
     * @param $data
     * @param $mode
     * @return string
     */
    public function checkPayment($data, $mode)
    {

        $httpClient = new \GuzzleHttp\Client();

        $mode = $mode ? 'https://www.sandbox.paypal.com/cgi-bin/webscr' : 'https://ipnpb.paypal.com/cgi-bin/webscr';

        $requestBody = array_merge(
            ['cmd' => '_notify-validate'],
            $data
        );

        try {
            $response = $httpClient->post(
                $mode,
                array('form_params' => $requestBody)
            );

        } catch (\Exception $e) {
            return 'ERROR';
        }

        $result = $this->verify((string)$response->getBody());
        if ($result) {
            return self::STATUS_KEYWORD_VERIFIED;
        } else {
            return self::STATUS_KEYWORD_INVALID;
        }

    }

    /**
     * @param $message
     * @return bool
     */
    public function verify($message)
    {
        $pattern = sprintf('/(%s|%s)/', self::STATUS_KEYWORD_VERIFIED, self::STATUS_KEYWORD_INVALID);

        if (!preg_match($pattern, $message)) {
            throw new \UnexpectedValueException(sprintf('Unexpected verification status encountered: %s', $message));
        }

        return $message === self::STATUS_KEYWORD_VERIFIED;
    }

}