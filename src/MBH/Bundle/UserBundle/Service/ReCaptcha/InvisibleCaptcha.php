<?php

namespace MBH\Bundle\UserBundle\Service\ReCaptcha;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use MBH\Bundle\UserBundle\Lib\Exception\InvisibleCaptchaException;
use Symfony\Component\Translation\TranslatorInterface;

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
     * @var TranslatorInterface
     */
    private $tr;

    /**
     * @param string $secretKey
     * @param TranslatorInterface $translator
     */
    public function __construct(string $secretKey, TranslatorInterface $translator)
    {
        $this->client = new GuzzleClient();
        $this->secretKey = $secretKey;
        $this->tr = $translator;
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
                        'secret' => $this->secretKey,
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
        if (!isset($response['success'])) {
            return;
        }
        if (!$response['success']) {
            throw new InvisibleCaptchaException($this->tr->trans('captcha.error'));
        }

        return;
    }
}
