<?php
/**
 * Created by PhpStorm.
 * Date: 03.05.18
 */

namespace MBH\Bundle\ClientBundle\Service\Document;


use MBH\Bundle\PackageBundle\Document\Package;

class PackageSerialize extends CommonSerialize
{
    public function __construct(Package $package)
    {
        $this->entity = $package;
    }

    public function allTourists()
    {
        $return = [];
        foreach ($this->entity->getTourists() as $tourist){
            $return[] = new MortalSerialize($tourist);
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