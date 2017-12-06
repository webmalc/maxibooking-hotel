<?php

namespace MBH\Bundle\UserBundle\Service;


use MBH\Bundle\BaseBundle\Lib\CallbacksLibrary;
use MBH\Bundle\BaseBundle\Lib\Exception;
use MBH\Bundle\BaseBundle\Service\FormDataHandler;
use MBH\Bundle\BillingBundle\Lib\Model\Client;
use MBH\Bundle\BillingBundle\Service\BillingApi;
use MBH\Bundle\ClientBundle\Service\ClientManager;
use MBH\Bundle\PackageBundle\Models\Billing\Country;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Translation\TranslatorInterface;

class ClientPayerManager
{
    const NATURAL_ENTITY_ID = 'natural';
    const LEGAL_ENTITY_ID = 'legal';
    const FIELD_NOT_FILLED_MESSAGE_ID = 'form.payer_type.field_not_filled';
    const BILLING_FIELDS_BY_FORM_FIELDS = [
        'ru_natural' => [
            'series' => 'passport_serial',
            'number' => 'passport_number',
            'issueDate' => 'passport_date',
            'issuedBy' => 'passport_issued_by',
            'financeInn' => 'inn'
        ],
        'foreign_natural' => [
            'address' => 'address',
            'city' => 'city',
            'state' => 'region',
            'postalCode' => 'postal_code'
        ],
    ];

    private $formDataHandler;
    private $clientManager;
    private $serializer;
    private $translator;

    public function __construct(FormDataHandler $formDataHandler, ClientManager $clientManager, Serializer $serializer, TranslatorInterface $translator)
    {
        $this->formDataHandler = $formDataHandler;
        $this->clientManager = $clientManager;
        $this->serializer = $serializer;
        $this->translator = $translator;
    }

    /**
     * @param array $formPayerData
     * @return array
     * @throws Exception
     */
    public function saveClientPayerAndReturnErrors(array $formPayerData): array
    {
        $payerType = $formPayerData['payerType'];
        $isRussianPayer = $formPayerData['country'] === Country::RUSSIA_TLD;

        $relationshipsAbbrString = ($isRussianPayer ? 'ru' : 'foreign') . '_' . $payerType;
        $billingFieldsByFormFields = self::BILLING_FIELDS_BY_FORM_FIELDS[$relationshipsAbbrString];

        if ($payerType !== self::NATURAL_ENTITY_ID && $payerType !== self::LEGAL_ENTITY_ID) {
            throw new Exception('Incorrect payer type "' . $payerType . '"!');
        }

        if (!empty($unsetFields = $this->formDataHandler->getUnsetFields($formPayerData, array_keys($billingFieldsByFormFields)))) {
            return $this->getUnsetFieldsErrors($unsetFields);
        }

        $fieldCallbacks = [
            'issueDate' => CallbacksLibrary::getDateTimeFormCallback(BillingApi::BILLING_DATETIME_FORMAT)
        ];
        $payerData = $this->formDataHandler->fillArrayByKeys($formPayerData, $billingFieldsByFormFields, $fieldCallbacks);

        if ($payerType === self::NATURAL_ENTITY_ID) {
            $client = $this->clientManager->getClient();

            if ($isRussianPayer) {
                $client->setRu($ruNaturalPayerData);
            } else {
                $this->serializer->denormalize($payerData, Client::class, null, [AbstractNormalizer::OBJECT_TO_POPULATE => $client]);
            }

            $requestResult = $this->clientManager->updateClient($client);
        } else {
            if ($isRussianPayer) {

            } else {

            }
        }

        return $this->handleRequestResult($requestResult);
    }

    /**
     * @param $responseResult
     * @return array
     * @throws Exception
     */
    private function handleErrorBillingRequestResult($responseResult): array
    {
        if (isset($responseResult->getErrors()['ru'])) {
            $errors = $responseResult->getErrors()['ru'];

            return $this->formDataHandler->fillArrayByKeys($errors, array_flip(self::RU_NATURAL_PAYER_FIELDS_BY_FORM_FIELDS));
        }

        throw new Exception('Billing return error by incorrect fields!. Errors: ' . json_encode($responseResult->getErrors()));
    }

    /**
     * @param $requestResult
     * @return array
     */
    private function handleRequestResult($requestResult): array
    {
        if (!$requestResult->isSuccessful()) {
            return $this->handleErrorBillingRequestResult($requestResult);
        }

        return [];
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
}