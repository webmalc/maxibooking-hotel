<?php

namespace MBH\Bundle\OnlineBundle\Services;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Translation\TranslatorInterface;

class ApiResponseCompiler
{
    /** @var  TranslatorInterface */
    private $translator;
    private $domains;

    private $errors = [];
    private $data = [];
    private $isSuccess = true;
    private $headers = [
        'Access-Control-Allow-Methods' => 'GET, POST, OPTIONS, PUT, DELETE, PATCH',
        'Access-Control-Allow-Headers' => 'Content-Type, *'
    ];

    const COMMON_ERRORS_FIELD_NAME = 'common';
    const FORM_CONFIG_NOT_EXISTS = 'external_api_controller.online_form_config.not_found';
    const FORM_CONFIG_NOT_ENABLED = 'external_api_controller.online_form_config.not_enabled';
    const MANDATORY_FIELD_MISSING = 'external_api_controller.missing_fields.error';
    const FIELD_MUST_BE_TYPE_OF_ARRAY = 'external_api_controller.field_must_be_type_of_array';
    const HOTEL_WITH_SPECIFIED_ID_NOT_EXISTS = 'external_api_controller.error.hotel_with_specified_id_not_exists';
    const ROOM_TYPE_WITH_SPECIFIED_ID_NOT_EXISTS = 'external_api_controller.error.room_type_with_specified_id_not_exists';
    const FORM_CONFIG_NOT_CONTAINS_SPECIFIED_ROOM_TYPE = 'external_api_controller.error.specified_room_type_not_contains_in_form_config';
    const FORM_CONFIG_NOT_CONTAINS_SPECIFIED_HOTEL = 'external_api_controller.error.specified_hotel_not_contains_in_form_config';
    const ORDER_WITH_SPECIFIED_ID_TO_EXISTS = 'external_api_controller.error.order_not_exists';
    const TARIFF_WITH_SPECIFIED_ID_NOT_EXISTS = 'external_api_controller.error.tariff_with_specified_id_not_exists';

    public function __construct(TranslatorInterface $translator, array $domains)
    {
        $this->translator = $translator;
        $this->domains = $domains;
    }

    /**
     * @param $text
     * @param null $fieldName
     * @param array $params
     * @return ApiResponseCompiler
     */
    public function addErrorMessage($text, $fieldName = null, $params = [])
    {
        $errorText = $this->translator->trans($text, $params);
        $fieldName = $fieldName ?? self::COMMON_ERRORS_FIELD_NAME;
        $this->errors[$fieldName] = $errorText;
        $this->isSuccess = false;

        return $this;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @param bool $isSuccess
     * @return ApiResponseCompiler
     */
    public function setIsSuccessful(bool $isSuccess)
    {
        $this->isSuccess = $isSuccess;

        return $this;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function setData(array $data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return bool
     */
    public function isSuccessful()
    {
        return $this->isSuccess;
    }

    /**
     * @return array
     */
    public function getHeaders(): ?array
    {
        return [];
    }

    /**
     * @param array $headers
     * @return ApiResponseCompiler
     */
    public function setHeaders(array $headers): ApiResponseCompiler
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * @param $key
     * @param $value
     * @return ApiResponseCompiler
     */
    public function addHeader($key, $value)
    {
        $this->headers[$key] = $value;

        return $this;
    }

    /**
     * @param int $status
     * @return JsonResponse
     */
    public function getResponse($status = 200)
    {
        $response = ['success' => $this->isSuccess];
        $response['data'] = $this->data;
        if (!$this->isSuccess) {
            $response['errors'] = $this->errors;
        }
        foreach ($this->domains as $domain) {
            $this->addHeader('Access-Control-Allow-Origin',  $domain);
        }

        return new JsonResponse($response, $status, $this->getHeaders());
    }
}