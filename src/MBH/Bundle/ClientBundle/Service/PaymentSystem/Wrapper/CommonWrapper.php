<?php
/**
 * Created by PhpStorm.
 * Date: 21.08.18
 */

namespace MBH\Bundle\ClientBundle\Service\PaymentSystem\Wrapper;

use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\ClientBundle\Document\ClientConfig;
use MBH\Bundle\ClientBundle\Lib\PaymentSystemCommonDocument;
use MBH\Bundle\ClientBundle\Lib\PaymentSystemInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class CommonWrapper implements PaymentSystemInterface
{
    protected $entity;

    protected $container;

    /**
     * Common constructor.
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function setPaymentSystemDocument(PaymentSystemCommonDocument $document): void
    {
        $this->entity = $document;
    }

    public function getPreFormData(ClientConfig $config, CashDocument $cashDocument, $url = null, $checkUrl = null)
    {
        $url = $url ?? $config->getSuccessUrl();
        if ($cashDocument->getOperation() != CashDocument::OPERATION_IN
            || $cashDocument->getMethod() != CashDocument::METHOD_ELECTRONIC
            || $cashDocument->getIsPaid()
        ) {
            return [];
        }

        return $this->getFormData($cashDocument, $url);
    }
}