<?php

namespace MBH\Bundle\BaseBundle\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

class YandexTranslator
{
    private $apiKey;
    const YANDEX_TRANSLATE_API_URL = 'https://translate.yandex.net/api/v1.5/tr.json/translate';
    const RUSSIAN_TO_ENGLISH_TRANSLATION_DIRECTION = 'ru-en';
    const ENGLISH_TO_RUSSIAN_TRANSLATION_DIRECTION = 'en-ru';

    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * @param $translatableText
     * @param $languageCombination
     * @return mixed
     */
    public function translate($translatableText, $languageCombination = self::RUSSIAN_TO_ENGLISH_TRANSLATION_DIRECTION)
    {
        $requestOptions = [
            'key' => $this->apiKey,
            'text' => $translatableText,
            'lang' => $languageCombination
        ];

        $client = $client = new Client();
        $request = new Request('POST', self::YANDEX_TRANSLATE_API_URL, [
            'form_params' => $requestOptions,
        ]);
        $jsonResponse = $client->send($request);
        $response = json_decode($jsonResponse->getBody(), true);

        return $response;
    }

}