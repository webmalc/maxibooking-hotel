<?php
/**
 * Created by PhpStorm.
 * Date: 03.05.18
 */

namespace MBH\Bundle\ClientBundle\Service\Document;


use MBH\Bundle\PackageBundle\Document\Package;

/**
 * Class PackageSerialize
 *
 * @property Package $entity
 *
 * @package MBH\Bundle\ClientBundle\Service\Document
 */

class PackageSerialize extends CommonSerialize
{
    public function allTourists()
    {
        $return = [];
        $mortalSerialize = $this->container->get('MBH\Bundle\ClientBundle\Service\Document\MortalSerialize');
        foreach ($this->entity->getTourists() as $tourist){
            $return[] = (clone $mortalSerialize)->newInstance($tourist);
        }
        return $return;
    }

    public function getNumber(): string
    {
        return $this->entity->getNumber() ?? '';
    }

    public function getNumberWithPrefix(): string
    {
        return $this->entity->getNumberWithPrefix()?? '';
    }

    public function getDateBegin(): string
    {
        return $this->entity->getBegin()
            ? $this->entity->getBegin()->format('d.m.Y')
            : '';
    }

    public function getDateEnd():string
    {
        return $this->entity->getEnd()
            ? $this->entity->getEnd()->format('d.m.Y')
            : '';
    }

    public function getAdults(): string
    {
        return $this->entity->getAdults() ?? '';
    }

    public function getChildren(): string
    {
        return $this->entity->getChildren() ?? '';
    }
}