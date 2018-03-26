<?php

namespace MBH\Bundle\UserBundle\Service;

use MBH\Bundle\BaseBundle\Lib\Exception;
use MBH\Bundle\BillingBundle\Lib\Model\Client;
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

    public function __construct(BillingPayerFormHandler $payerFormHandler, ClientManager $clientManager, Serializer $serializer, BillingApi $billingApi, BillingResponseHandler $responseHandler)
    {
        $this->payerFormHandler = $payerFormHandler;
        $this->clientManager = $clientManager;
        $this->serializer = $serializer;
        $this->billingApi = $billingApi;
        $this->responseHandler = $responseHandler;
    }

    /**
     * @param array $formPayerData
     * @return array
     * @throws Exception
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
                $client->setRu($payerDataByBillingKeys);
            } else {
                $this->serializer->denormalize($payerDataByBillingKeys, Client::class, null, [AbstractNormalizer::OBJECT_TO_POPULATE => $client]);
            }

            $requestResult = $this->clientManager->updateClient($client);
        } else {
            /** @var Company $company */
            $company = $this->getClientPayerCompany();
            $companyId = !is_null($company) ? $company->getId() : null;
            $company = $this->serializer->denormalize($payerDataByBillingKeys, Company::class, $company);
            $company->setClient($client->getLogin());
            if ($this->payerFormHandler->isRussianPayer()) {
                $company->setRu(array_intersect_key($payerDataByBillingKeys, array_flip(Company::getRuPaymentFields())));
            } else {
                $company->setWorld(array_intersect_key($payerDataByBillingKeys, array_flip(Company::getWorldPaymentFields())));
            }

            $company->setId($companyId);
            $requestResult = !is_null($companyId) ? $this->updateClientPayerCompany($company) : $this->createClientPayerCompany($company);
        }

        $billingFieldsByFormFields = array_flip($this->payerFormHandler->getBillingFieldsByFormFields());
        $requestErrors = $this->responseHandler->getErrorsByRequestResult($requestResult);
        if (empty($requestErrors) && $formPayerData['country'] != $client->getCountry()) {
            $client->setCountry($formPayerData['country']);
            $requestResult = $this->clientManager->updateClient($client);
            if (!$requestResult->isSuccessful()) {
                $requestErrors = [BillingResponseHandler::NON_FIELD_ERRORS => [$this->responseHandler->getUnexpectedErrorText()]];
            }
        }

        return $this->payerFormHandler->fillArrayByKeys($requestErrors, $billingFieldsByFormFields, [BillingResponseHandler::NON_FIELD_ERRORS]);
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
        return $this->billingApi->updateClientPayerCompany($company);
    }

    /**
     * @return Company|null
     */
    public function getClientPayerCompany()
    {
        $client = $this->clientManager->getClient();
        $clientCompanies = $this->billingApi->getClientCompanies($client);

        return empty($clientCompanies) ? null : current($clientCompanies);
    }
}