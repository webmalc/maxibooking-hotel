<?php

namespace MBH\Bundle\BaseBundle\Service;

use GuzzleHttp\Client;

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
     * @param array $translatableText
     * @param $languageCombination
     * @return array
     */
    public function translate(array $translatableText, $languageCombination = self::RUSSIAN_TO_ENGLISH_TRANSLATION_DIRECTION)
    {
        $requestOptions = [
            'key' => $this->apiKey,
            'lang' => $languageCombination
        ];

        $url = self::YANDEX_TRANSLATE_API_URL . '?' . http_build_query($requestOptions);
        $textString = join('&text=', $translatableText);

        $jsonResponse = (new Client())->request('POST', $url . '&text=' . $textString);
        $response = json_decode($jsonResponse->getBody(), true);

        return $response['text'];
    }

}