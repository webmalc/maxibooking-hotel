<?php
/**
 * Created by PhpStorm.
 * Date: 03.05.18
 */

namespace MBH\Bundle\ClientBundle\Service\DocumentSerialize;


use MBH\Bundle\PackageBundle\Document\Package as PackageBase;

/**
 * Class Package
 *
 * @property PackageBase $entity
 *
 * @package MBH\Bundle\ClientBundle\Service\DocumentSerialize
 */
class Package extends Common
{
    protected const METHOD = [
        'getNumber',
        'getNumberWithPrefix',
        'getAdults',
        'getChildren',
        'getNights',
        'getArrivalTime',
        'getDepartureTime',
        'getPrice|money',
        'getServicesPrice|money',
    ];

    public function getRoomName(): string
    {
        $roomType = $this->entity->getRoomType();

        return $roomType !== null
            ? $roomType->getName() ?? ''
            : '';
    }

    public function getRoomNameInternational(): string
    {
        $roomType = $this->entity->getRoomType();

        return $roomType !== null
            ? $roomType->getInternationalTitle() ?? ''
            : '';
    }

    public function getNightsExtra(): string
    {
        $nights = $this->entity->getNights();
        if ($nights === null) {
            return '';
        }

        $translator = $this->container->get('translator');

        return $nights . ' ' . $translator->transChoice('nights', $nights);
    }

    public function getPriceToString(): string
    {
        $string = $this->entity->getPrice();

        return $string !== null
            ? $this->container->get('mbh.helper')->num2str($string)
            : '';
    }

    public function getPackagePrice(): string
    {
        $price = $this->entity->getPackagePrice(true);

        return $price !== null
            ? Helper::numFormat($price)
            : '';
    }

    /**
     * @return string
     */
    public function getAmountOfDiscount(): string
    {
        if ($this->entity->getDiscount() !== null) {
            if ($this->entity->getIsPercentDiscount()) {
                $msg = $this->entity->getDiscount(true) . '%';
            } else {
                $currency = $this->container->get('mbh.currency')->info();
                $translate = $this->container->get('translator')->trans($currency['text']);
                $msg = Helper::numFormat($this->entity->getDiscount()) . ' ' . $translate;
            }

            return $msg;
        }

        return '';
    }

    /**
     * @return array
     */
    public function allTourists(): array
    {
        $return = [];
        $mortalSerialize = $this->container->get('MBH\Bundle\ClientBundle\Service\DocumentSerialize\Mortal');
        foreach ($this->entity->getTourists() as $tourist) {
            $return[] = (clone $mortalSerialize)->newInstance($tourist);
        }

        return $return;
    }

    /**
     * @return string
     */
    public function getDateBegin(): string
    {
        return $this->entity->getBegin()
            ? $this->entity->getBegin()->format('d.m.Y')
            : '';
    }

    /**
     * @return string
     */
    public function getDateEnd(): string
    {
        return $this->entity->getEnd()
            ? $this->entity->getEnd()->format('d.m.Y')
            : '';
    }

    protected function getSourceClassName()
    {
        return PackageBase::class;
    }
}