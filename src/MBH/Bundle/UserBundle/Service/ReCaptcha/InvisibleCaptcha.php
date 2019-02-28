<?php

namespace MBH\Bundle\UserBundle\Service\ReCaptcha;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use MBH\Bundle\UserBundle\Lib\Exception\InvisibleCaptchaException;

class InvisibleCaptcha
{
    private const GOOGLE_CAPTCHA_API_URL = 'https://www.google.com/recaptcha/api/siteverify';

    /**
     * @var \GuzzleHttp\Client
     */
    private $client;

    /**
     * @var string
     */
    private $secretKey;

    /**
     * @param string $secretKey
     */
    public function __construct(string $secretKey)
    {
        $this->client = new GuzzleClient();
        $this->secretKey = $secretKey;
    }

    /**
     * @param string $reToken
     * @return void
     * @throws InvisibleCaptchaException
     */
    public function validate(string $reToken): void
    {
        $guzzleResponse = null;
        try {
            $guzzleResponse = $this->client->request(
                'POST',
                self::GOOGLE_CAPTCHA_API_URL,
                [
                    'form_params' => [
                        'secret' =>  $this->secretKey,
                        'response' => $reToken
                    ]
                ]);
        } catch (GuzzleException $e) {
            return;
        }

        $response = json_decode($guzzleResponse->getBody(), true);

        if ($response === null) {
            return;
        }
        if (!isset ($response['success'])) {
            return;
        }
        if (!$response['success']) {
            throw new InvisibleCaptchaException('captcha error');
        }

        return;
    }
}