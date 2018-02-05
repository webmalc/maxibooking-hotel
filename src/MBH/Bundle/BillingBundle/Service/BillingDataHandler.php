<?php

namespace MBH\Bundle\BillingBundle\Service;

use MBH\Bundle\BaseBundle\Service\FormDataHandler;
use MBH\Bundle\BillingBundle\Lib\Model\BillingCheckableInterface;
use MBH\Bundle\BillingBundle\Lib\Model\BillingClientRelatedInterface;
use MBH\Bundle\BillingBundle\Lib\Model\BillingEnablableInterface;
use MBH\Bundle\ClientBundle\Service\ClientManager;
use MBH\Bundle\OnlineBundle\Services\ApiResponseCompiler;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Serializer\Serializer;

class BillingDataHandler
{
    private $billingApi;
    private $serializer;
    private $clientManager;
    private $formDataHandler;

    public function __construct(BillingApi $billingApi, Serializer $serializer, ClientManager $clientManager, FormDataHandler $formDataHandler) {
        $this->billingApi = $billingApi;
        $this->serializer = $serializer;
        $this->clientManager = $clientManager;
        $this->formDataHandler = $formDataHandler;
    }

    /**
     * @param FormInterface $form
     * @param ApiResponseCompiler $responseCompiler
     * @param array $endpointSettings
     * @param callable|null $updateBeforeCreationFunc
     * @return ApiResponseCompiler|\Symfony\Component\HttpFoundation\JsonResponse
     */
    public function handleNewEntityForm(FormInterface $form, ApiResponseCompiler $responseCompiler, array $endpointSettings, callable $updateBeforeCreationFunc = null)
    {
        if ($form->isSubmitted() && $form->isValid()) {
            $entity = $form->getData();
            $prospectiveEntity = $this->tryGetExistingEntity($entity->getName(), $endpointSettings);
            if (!is_null($prospectiveEntity)) {
                $responseCompiler->setData($this->serializer->normalize($prospectiveEntity));
            }
            if ($entity instanceof BillingClientRelatedInterface) {
                //TODO: Поменять если будет логин вместо ID
                $entity->setRequest_client($this->clientManager->getClient()->getId());
            }
            if ($entity instanceof BillingEnablableInterface && $entity instanceof BillingCheckableInterface) {
                $entity->setIs_enabled(false);
                $entity->setIs_checked(false);
            }
            if (!is_null($updateBeforeCreationFunc)) {
                $updateBeforeCreationFunc($entity);
            }
            $response = $this->billingApi->createBillingEntity($endpointSettings, $entity);
            $decodedResponse = json_decode($response->getBody(), true);
            if (!isset($decodedResponse['id'])) {
                $responseCompiler->setIsSuccessful(false);
                $this->formDataHandler->fillFormByBillingErrors($form, $decodedResponse);
            } else {
                $responseCompiler->setData($decodedResponse);
            }
        } else {
            $responseCompiler->setIsSuccessful(false);
        }

        return $responseCompiler;
    }

    /**
     * @param $entityName
     * @param $endpointSettings
     * @return null|mixed
     */
    private function tryGetExistingEntity($entityName, $endpointSettings)
    {
        $possibleOptionsOfExtraLetters = ['', ' ', 'г.', 'г', 'обл.', 'обл'];
        foreach ($possibleOptionsOfExtraLetters as $letters) {
            if ($letters === '' || strpos(substr($entityName, 0, strlen($letters) + 2), $letters) !== false) {
                $fixedEntityName = str_replace($letters, '', $entityName);
                $entities = $this->billingApi->getBillingEntitiesByQuery($endpointSettings,
                    [BillingApi::BILLING_QUERY_PARAM_NAME => $fixedEntityName]
                );

                if (count($entities) > 0) {
                    return current($entities);
                }
            }
        }

        return null;
    }
}