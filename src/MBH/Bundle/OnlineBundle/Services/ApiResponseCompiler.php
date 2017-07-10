<?php

namespace MBH\Bundle\OnlineBundle\Services;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Created by PhpStorm.
 * User: danya
 * Date: 10.07.17
 * Time: 10:11
 */
class ApiResponseCompiler
{
    /** @var  TranslatorInterface */
    private $translator;
    private $errors = [];
    private $data = [];
    private $isSuccess = true;

    const FORM_CONFIG_NOT_EXISTS = 'external_api_controller.online_form_config.not_found';
    const FORM_CONFIG_NOT_ENABLED = 'external_api_controller.online_form_config.not_enabled';
    const MANDATORY_FIELD_MISSING = 'external_api_controller.missing_fields.error';
    const FIELD_MUST_BE_TYPE_OF_ARRAY = 'external_api_controller.field_must_be_type_of_array';
    const HOTEL_WITH_SPECIFIED_ID_NOT_EXISTS = 'external_api_controller.error.hotel_with_specified_id_not_exists';
    const ROOM_TYPE_WITH_SPECIFIED_ID_NOT_EXISTS = 'external_api_controller.error.room_type_with_specified_id_not_exists';
    const FORM_CONFIG_NOT_CONTAINS_SPECIFIED_ROOM_TYPE = 'external_api_controller.error.specified_room_type_not_contains_in_form_config';
    const FORM_CONFIG_NOT_CONTAINS_SPECIFIED_HOTEL = 'external_api_controller.error.specified_hotel_not_contains_in_form_config';

    public function __construct(TranslatorInterface $translator) {
        $this->translator = $translator;
    }

    /**
     * @param $text
     * @param $params
     * @return ApiResponseCompiler
     */
    public function addErrorMessage($text, $params = [])
    {
        $this->errors[] = $this->translator->trans($text, $params);
        $this->isSuccess = false;

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
    public function isSuccessFull()
    {
        return $this->isSuccess;
    }

    /**
     * @return JsonResponse
     */
    public function getResponse()
    {
        $response = ['success' => $this->isSuccess];
        if ($this->isSuccess) {
            $response['data'] = $this->data;
        } else {
            $response['errors'] = $this->errors;
        }

        return new JsonResponse($response);
    }
}