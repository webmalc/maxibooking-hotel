<?php
/**
 * Created by PhpStorm.
 * Date: 03.05.18
 */

namespace MBH\Bundle\ClientBundle\Service\DocumentSerialize;

use MBH\Bundle\PackageBundle\Lib\DataOfMortalInterface;


/**
 * Trait DataOfMortalSerialize
 *
 * @property DataOfMortalInterface $entity
 *
 * @package MBH\Bundle\ClientBundle\Service\DocumentSerialize
 */
trait TraitDataOfMortal
{
    /**
     * @return string
     */
    public function getFullName(): string
    {
        return $this->entity->getFullName() ?? '';
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->entity->getLastName() ?? '';
    }

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->entity->getFirstName() ?? '';
    }

    /**
     * @return string
     */
    public function getBirthday(): string
    {
        return $this->entity->getBirthday() !== null ? $this->entity->getBirthday()->format('d.m.Y') : '';
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->entity->getEmail() ?? '';
    }

    /**
     * @return string
     */
    public function getShortName(): string
    {
        return $this->entity->getShortName() ?? '';
    }

    /**
     * @return string
     */
    public function getPatronymic(): string
    {
        return $this->entity->getPatronymic() ?? '';
    }
}