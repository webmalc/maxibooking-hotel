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
    public function getFullName(): string
    {
        return $this->entity->getFullName() ?? '';
    }

    public function getLastName(): string
    {
        return $this->entity->getLastName() ?? '';
    }

    public function getFirstName(): string
    {
        return $this->entity->getFirstName() ?? '';
    }

    public function getBirthday(): string
    {
        return $this->entity->getBirthday() !== null ? $this->entity->getBirthday()->format('d.m.Y') : '';
    }

    public function getEmail(): string
    {
        return $this->entity->getEmail() ?? '';
    }

    public function getShortName(): string
    {
        return $this->entity->getShortName() ?? '';
    }

    public function getPatronymic(): string
    {
        return $this->entity->getPatronymic() ?? '';
    }
}