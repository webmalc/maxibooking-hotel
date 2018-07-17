<?php

namespace MBH\Bundle\BillingBundle\Service;

use MBH\Bundle\BaseBundle\Service\FormDataHandler;
use MBH\Bundle\BillingBundle\Lib\Model\BillingCheckableInterface;
use MBH\Bundle\BillingBundle\Lib\Model\BillingClientRelatedInterface;
use MBH\Bundle\BillingBundle\Lib\Model\BillingEnablableInterface;
use MBH\Bundle\OnlineBundle\Services\ApiResponseCompiler;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Serializer\Serializer;

class BillingDataHandler
{
    private $billingApi;
    private $serializer;
    private $formDataHandler;
    /** @var \AppKernel $kernel */
    private $kernel;

    public function __construct(BillingApi $billingApi, Serializer $serializer, FormDataHandler $formDataHandler, KernelInterface $kernel) {
        $this->billingApi = $billingApi;
        $this->serializer = $serializer;
        $this->formDataHandler = $formDataHandler;
        $this->kernel = $kernel;
    }

    /**
     * @param FormInterface $form
     * @param ApiResponseCompiler $responseCompiler
     * @param array $endpointSettings
     * @return ApiResponseCompiler|\Symfony\Component\HttpFoundation\JsonResponse
     */
    public function handleNewEntityForm(FormInterface $form, ApiResponseCompiler $responseCompiler, array $endpointSettings)
    {
        if ($form->isSubmitted() && $form->isValid()) {
            $entity = $form->getData();
            $prospectiveEntity = $this->tryGetExistingEntity($entity->getName(), $endpointSettings);
            if (!is_null($prospectiveEntity)) {
                $responseCompiler->setData($this->serializer->normalize($prospectiveEntity));
            }
            if ($entity instanceof BillingClientRelatedInterface) {
                //TODO: Поменять если будет логин вместо ID
                $entity->setRequest_client($this->kernel->getClient());
            }
            if ($entity instanceof BillingEnablableInterface && $entity instanceof BillingCheckableInterface) {
                $entity->setIs_enabled(false);
                $entity->setIs_checked(false);
            }

            $response = $this->billingApi->createBillingEntity($endpointSettings, $entity);

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