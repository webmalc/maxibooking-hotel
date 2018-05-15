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
    /**
     * @return array
     */
    public function allTourists()
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
    public function getNumber(): string
    {
        return $this->entity->getNumber() ?? '';
    }

    /**
     * @return string
     */
    public function getNumberWithPrefix(): string
    {
        return $this->entity->getNumberWithPrefix() ?? '';
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

    /**
     * @return string
     */
    public function getAdults(): string
    {
        return $this->entity->getAdults() ?? '';
    }

    /**
     * @return string
     */
    public function getChildren(): string
    {
        return $this->entity->getChildren() ?? '';
    }
}