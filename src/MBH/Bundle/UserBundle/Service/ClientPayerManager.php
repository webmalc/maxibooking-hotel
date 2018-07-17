<?php

namespace MBH\Bundle\UserBundle\Service;

use MBH\Bundle\BillingBundle\Lib\Model\Client;
use MBH\Bundle\BillingBundle\Lib\Model\ClientPayer;
use MBH\Bundle\BillingBundle\Lib\Model\Company;
use MBH\Bundle\BillingBundle\Lib\Model\Result;
use MBH\Bundle\BillingBundle\Service\BillingApi;
use MBH\Bundle\BillingBundle\Service\BillingPayerFormHandler;
use MBH\Bundle\BillingBundle\Service\BillingResponseHandler;
use MBH\Bundle\ClientBundle\Service\ClientManager;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Serializer;

class ClientPayerManager
{
    private $payerFormHandler;
    private $clientManager;
    private $serializer;
    private $billingApi;
    private $responseHandler;

    private $clientPayerCompany;
    private $isClientPayerCompanyInit = false;
    private $clientPayer;
    private $isClientPayerInit = false;
    private $clientRuCompany;
    private $isClientRuCompanyInit = false;
    private $clientWorldCompany;
    private $isClientWorldCompanyInit = false;

    public function __construct(
        BillingPayerFormHandler $payerFormHandler,
        ClientManager $clientManager,
        Serializer $serializer,
        BillingApi $billingApi,
        BillingResponseHandler $responseHandler
    ) {
        $this->payerFormHandler = $payerFormHandler;
        $this->clientManager = $clientManager;
        $this->serializer = $serializer;
        $this->billingApi = $billingApi;
        $this->responseHandler = $responseHandler;
    }

    /**
     * @param array $formPayerData
     * @return array
     * @throws \Exception
     */
    public function saveClientPayerAndReturnErrors(array $formPayerData): array
    {
        $this->payerFormHandler->setInitData($formPayerData);
        if ($this->payerFormHandler->hasErrors()) {
            return $this->payerFormHandler->getErrors();
        }

        $client = $this->clientManager->getClient();
        $payerDataByBillingKeys = $this->payerFormHandler->getRequestPayerData();

        if ($this->payerFormHandler->isNaturalEntityPayer()) {
            if ($this->payerFormHandler->isRussianPayer()) {
                $clientPayer = $this->getClientPayer();
                $this->serializer->denormalize(
                    $payerDataByBillingKeys,
                    ClientPayer::class,
                    null,
                    [AbstractNormalizer::OBJECT_TO_POPULATE => $clientPayer]
                );
                $requestResult = $this->billingApi->updateBillingEntity($clientPayer, BillingApi::CLIENT_PAYER_ENDPOINT_SETTINGS, $client);

                $client->setAddress($payerDataByBillingKeys['address']);
                unset($payerDataByBillingKeys['address']);
            } else {
                $this->serializer->denormalize(
                    $payerDataByBillingKeys,
                    Client::class,
                    null,
                    [AbstractNormalizer::OBJECT_TO_POPULATE => $client]
                );
            }
            if (!isset($requestResult) || $requestResult->isSuccessful()) {
                $requestResult = $this->clientManager->updateClient($client);
            }
        } else {
            /** @var Company $company */
            $company = $this->getClientCompany();
            $companyId = !is_null($company) ? $company->getId() : null;
            $company = $this->serializer->denormalize($payerDataByBillingKeys, Company::class, $company);
            $company->setId($companyId);
            $company->setClient($client->getLogin());

            $requestResult = !is_null($companyId)
                ? $this->updateClientPayerCompany($company)
                : $this->createClientPayerCompany($company);
            if ($requestResult->isSuccessful()) {
                $company = $requestResult->getData();
                if ($this->payerFormHandler->isRussianPayer()) {
                    $payerCompany = $this->getClientRuCompany();
                    $endpointSettings = BillingApi::RU_PAYER_COMPANY;
                } else {
                    $payerCompany = $this->getClientWorldCompany();
                    $endpointSettings = BillingApi::WORLD_PAYER_COMPANY;
                }

                $payerCompany = $this->serializer->denormalize(
                    $payerDataByBillingKeys,
                    $endpointSettings['model'],
                    null,
                    [AbstractNormalizer::OBJECT_TO_POPULATE => $payerCompany]
                );
                $payerCompany->setCompany($company->getId());

                $requestResult = !is_null($payerCompany->getId())
                    ? $this->billingApi->updateBillingEntity($payerCompany, $endpointSettings, $company->getId())
                    : $this->billingApi->createBillingEntityBySettings($payerCompany, $endpointSettings);
            }
        }

        $billingFieldsByFormFields = array_flip($this->payerFormHandler->getBillingFieldsByFormFields());
        $requestErrors = $this->responseHandler->getErrorsByRequestResult($requestResult);
        if (empty($requestErrors) && $formPayerData['country'] != $client->getCountry()) {
            $client->setCountry($formPayerData['country']);
            $requestResult = $this->clientManager->updateClient($client);
            if (!$requestResult->isSuccessful()) {
                $requestErrors = [
                    BillingResponseHandler::NON_FIELD_ERRORS => [
                        $this->responseHandler->getUnexpectedErrorText(),
                    ],
                ];
            }
        }

        return $this->payerFormHandler->fillArrayByKeys(
            $requestErrors,
            $billingFieldsByFormFields,
            [BillingResponseHandler::NON_FIELD_ERRORS]
        );
    }

    /**
     * @param Company $company
     * @return Result
     */
    public function createClientPayerCompany(Company $company)
    {
        return $this->billingApi->createClientPayerCompany($company);
    }

    /**
     * @param Company $company
     * @return Result
     */
    public function updateClientPayerCompany(Company $company)
    {
        return $this->billingApi->updateBillingEntity($company, BillingApi::PAYER_COMPANY_ENDPOINT_SETTINGS);
    }

    /**
     * @return Company|null
     * @throws \Exception
     */
    public function getClientCompany()
    {
        if (!$this->isClientPayerCompanyInit) {
            $client = $this->clientManager->getClient();
            $clientCompanies = $this->billingApi->getClientCompanies($client);

            $this->clientPayerCompany = empty($clientCompanies) ? null : current($clientCompanies);
            $this->isClientPayerCompanyInit = true;
        }

        return $this->clientPayerCompany;
    }

    /**
     * @return null|object
     */
    public function getClientRuCompany()
    {
        if (!$this->isClientRuCompanyInit) {
            $company = $this->getClientCompany();
            if (!is_null($company)) {
                $this->clientRuCompany = $this->billingApi->getClientPayerCompany($company);
            }

            $this->isClientRuCompanyInit = true;
        }

        return $this->clientRuCompany;
    }

    /**
     * @return null|object
     */
    public function getClientWorldCompany()
    {
        if (!$this->isClientWorldCompanyInit) {
            $company = $this->getClientCompany();
            if (!is_null($company)) {
                $this->clientWorldCompany = $this->billingApi->getClientPayerCompany($company, false);
            }
            $this->isClientWorldCompanyInit = true;
        }

        return $this->clientWorldCompany;
    }

    /**
     * @return null|object
     */
    public function getClientPayer()
    {
        if (!$this->isClientPayerInit) {
            $client = $this->clientManager->getClient();
            $this->clientPayer = $this->billingApi->getClientPayer($client);
            $this->isClientPayerInit = true;
        }

        return $this->clientPayer;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getErrorsCausedByUnfilledDataForPayment()
    {
        $errors = [];
        $client = $this->clientManager->getClient();

        if (empty($client->getPhone())) {
            $errors[] = 'form.client_contacts_type.phone.label';
        }

        $clientCompany = $this->getClientCompany();
        $isRussianClient = $client->getCountry() === 'ru';

        if (is_null($clientCompany)) {
            if (($isRussianClient && empty($client->getRu()))
                || (!$isRussianClient && (empty($client->getPostal_code()) || empty($client->getAddress())))) {
                $errors[] = 'client_payer_manager.payer_data';
            }
        }

        return $errors;
    }
}