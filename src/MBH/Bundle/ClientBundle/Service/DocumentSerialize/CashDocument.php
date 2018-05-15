<?php
/**
 * Created by PhpStorm.
 * Date: 03.05.18
 */

namespace MBH\Bundle\ClientBundle\Service\DocumentSerialize;


use MBH\Bundle\CashBundle\Document\CashDocument as CashDocumentBase;

/**
 * Class CashDocument
 *
 * @property CashDocumentBase $entity
 *
 * @package MBH\Bundle\ClientBundle\Service\DocumentSerialize
 */

class CashDocument extends Common
{

    public function getPaidDate(): string
    {
        return $this->entity->getPaidDate()
            ? $this->entity->getPaidDate()->format('d.m.Y')
            : '';
    }

    public function getTotal(): string
    {
        return $this->entity->getTotal() !== null ? Helper::numFormat($this->entity->getTotal()) : '';
    }

    public function getMethod(): string
    {
        return $this->entity->getMethod() !== null
            ? $this->container->get('translator')
                ->trans('cashDocument.method.'. $this->entity->getMethod(),[],'MBHCashBundle')
            : '';
    }

    public function getTotalWithSigned(): string
    {
        return $this->getSigned() . ' ' . $this->getTotal();
    }

    public function getSigned(): string
    {
        return in_array($this->entity->getOperation(),['fee', 'out']) ? '-' : '+';
    }
}