<?php

namespace MBH\Bundle\BillingBundle\Service;

use MBH\Bundle\BaseBundle\Lib\CallbacksLibrary;
use MBH\Bundle\BaseBundle\Service\FormDataHandler;
use MBH\Bundle\BillingBundle\Lib\Model\Country;

class BillingPayerFormHandler extends FormDataHandler
{
    const NATURAL_ENTITY_ID = 'natural';
    const LEGAL_ENTITY_ID = 'legal';
    const FIELD_NOT_FILLED_MESSAGE_ID = 'form.payer_type.field_not_filled';

    const FIELDS_BY_CATEGORIES = [
        'ru_natural' => [
            'series' => 'passport_serial',
            'number' => 'passport_number',
            'issueDate' => 'passport_date',
            'issuedBy' => 'passport_issued_by',
            'financeInn' => 'inn',
            'registration_address' => 'address'
        ],
        'foreign_natural' => [
            'address' => 'address',
            'city' => 'city',
            'state' => 'region',
            'postalCode' => 'postal_code',
        ],
        'ru_legal' => [
            'organizationName' => 'name',
            'form' => 'form',
            'orgCity' => 'city',
            'orgState' => 'region',
            'orgAddress' => 'address',
            'orgPostalCode' => 'postal_code',
            'inn' => 'inn',
            'ogrn' => 'ogrn',
            'kpp' => 'kpp',
//        'position' => 'corr_account',
            'surname' => 'boss_lastname',
            'name' => 'boss_firstname',
            'patronymic' => 'boss_patronymic',
            'base' => 'boss_operation_base',
            'proxy' => 'proxy_number',
            'proxyDate' => 'proxy_date',
            'checkingAccount' => 'account_number',
            'bank_name' => 'bank',
            'bik' => 'bik',
            'correspondentAccount' => 'corr_account',
        ],
        'foreign_legal' => [
            'organizationName' => 'name',
            'orgCity' => 'city',
            'orgState' => 'region',
            'orgAddress' => 'address',
            'orgPostalCode' => 'postal_code',
            'checkingAccount' => 'account_number',
            'bank_name' => 'bank',
            'swift' => 'swift'
        ]
    ];

    private $isRussianPayer;
    private $payerDataWithoutExtraFields;
    private $payerType;
    private $errors = [];

    /**
     * @param $payerData
     * @return BillingPayerFormHandler
     * @throws \Exception
     */
    public function setInitData($payerData)
    {
        $this->isRussianPayer = $payerData['country'] === Country::RUSSIA_TLD;
        $this->payerType = $payerData['payerType'];

        $this->handleInitData($payerData);

        return $this;
    }

    /**
     * @return bool
     */
    public function hasErrors()
    {
        return !empty($this->errors);
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    public function getRequestPayerData()
    {
        $billingFieldsByFormFields = $this->getBillingFieldsByFormFields();
        $payerDataByBillingKeys = $this->fillArrayByKeys($this->payerDataWithoutExtraFields, $billingFieldsByFormFields);

        return $payerDataByBillingKeys;
    }

    /**
     * @return bool
     */
    public function isNaturalEntityPayer()
    {
        return $this->payerType === BillingPayerFormHandler::NATURAL_ENTITY_ID;
    }

    /**
     * @return bool
     */
    public function isRussianPayer()
    {
        return $this->isRussianPayer;
    }

    /**
     * Return errors for unfilled fields
     *
     * @param $unsetFields
     * @return array
     */
    private function getUnsetFieldsErrors($unsetFields): array
    {
        $formErrors = [];
        foreach ($unsetFields as $unsetField) {
            $formErrors[$unsetField] = [$this->translator->trans(self::FIELD_NOT_FILLED_MESSAGE_ID)];
        }

        return $formErrors;
    }

    /**
     * @param $payerData
     * @return mixed
     */
    private function getMandatoryFields($payerData)
    {
        if ($this->isRussianPayer && $this->payerType === self::NATURAL_ENTITY_ID) {
            return [];
        }

        $fieldsByCategories = array_keys($this->getBillingFieldsByFormFields());

        if ($this->IsRussianLegalPayerWithCharterBase($payerData)) {
            $fieldsByCategories = array_diff($fieldsByCategories, ['proxy', 'proxyDate']);
        }
        if ($this->isRussianPayer && $this->payerType === self::LEGAL_ENTITY_ID) {
            $fieldsByCategories = array_diff($fieldsByCategories, ['kpp']);
        }

        return $fieldsByCategories;
    }

    /**
     * @param $payerData
     * @return array
     */
    private function getPayerDataWithoutExtraFields($payerData): array
    {
        $billingFieldsByFormFields = $this->getBillingFieldsByFormFields();
        $extraFields = array_diff(array_keys($payerData), array_keys($billingFieldsByFormFields));

        $extraFields = array_merge($extraFields, ['country', 'payerType']);

        if ($this->IsRussianLegalPayerWithCharterBase($payerData)) {
            $extraFields = array_merge($extraFields, ['proxy', 'proxyDate']);
        }


        return array_diff_key($payerData, array_flip($extraFields));
    }

    /**
     * @param $payerData
     * @return bool
     */
    private function IsRussianLegalPayerWithCharterBase($payerData): bool
    {
        return $this->isRussianPayer && $this->payerType === self::LEGAL_ENTITY_ID && $payerData['base'] !== 'proxy';
    }

    /**
     * @param bool $byPayerType
     * @return array
     */
    public function getBillingFieldsByFormFields($byPayerType = false): array
    {
        if ($byPayerType) {
            $result = [];
            foreach (self::FIELDS_BY_CATEGORIES as $categoryAbbr => $fieldsByCategory) {
                if (strpos($categoryAbbr, $this->payerType) !== false) {
                    $result = array_merge($result, $fieldsByCategory);
                }
            }
        } else {
            $result = self::FIELDS_BY_CATEGORIES[($this->isRussianPayer ? 'ru' : 'foreign') . '_' . $this->payerType];
        }


        return $result;
    }

    /**
     * @param array $payerData
     * @throws \Exception
     */
    public function handleInitData($payerData): void
    {
        if (!$this->isNaturalEntityPayer() && $this->payerType !== self::LEGAL_ENTITY_ID) {
            throw new \Exception('Incorrect payer type "' . $this->payerType . '"!');
        }

        $fieldCallbacks = [
            'issueDate' => CallbacksLibrary::getDateTimeFormatCallback(BillingApi::BILLING_DATETIME_FORMAT),
            'proxyDate' => CallbacksLibrary::getDateTimeFormatCallback(BillingApi::BILLING_DATETIME_FORMAT),
        ];

        $convertedPayerData = $this->convertArrayDataByCallbacks($payerData, $fieldCallbacks);
        $this->payerDataWithoutExtraFields = $this->getPayerDataWithoutExtraFields($convertedPayerData);

        $mandatoryFields = $this->getMandatoryFields($payerData);
        if (!empty($unsetFields = $this->getUnsetFields($this->payerDataWithoutExtraFields, $mandatoryFields))) {
            $this->errors = $this->getUnsetFieldsErrors($unsetFields);
        }
    }
}