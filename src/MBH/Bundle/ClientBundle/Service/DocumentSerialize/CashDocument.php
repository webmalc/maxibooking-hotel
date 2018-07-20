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
    /**
     * @return string
     */
    public function getPaidDate(): string
    {
        return $this->entity->getPaidDate()
            ? $this->entity->getPaidDate()->format('d.m.Y')
            : '';
    }

    /**
     * @return string
     */
    public function getTotal(): string
    {
        return $this->entity->getTotal() !== null ? Helper::numFormat($this->entity->getTotal()) : '';
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->entity->getMethod() !== null
            ? $this->container->get('translator')
                ->trans('cashDocument.method.' . $this->entity->getMethod(), [], 'MBHCashBundle')
            : '';
    }

    /**
     * @return string
     */
    public function getTotalWithSigned(): string
    {
        return $this->getSigned() . ' ' . $this->getTotal();
    }

    /**
     * @return string
     */
    public function getSigned(): string
    {
        return in_array($this->entity->getOperation(), ['fee', 'out']) ? '-' : '+';
    }

    protected function getSourceClassName()
    {
        return CashDocumentBase::class;
    }
}