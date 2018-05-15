<?php
/**
 * Created by PhpStorm.
 * Date: 28.04.18
 */

namespace MBH\Bundle\ClientBundle\Service\DocumentSerialize;
use MBH\Bundle\PackageBundle\Document\Organization as OrganizationBase;

/**
 * Class Organization
 *
 * @property OrganizationBase $entity
 *
 * @package MBH\Bundle\ClientBundle\Service\DocumentSerialize
 */

class Organization extends Common
{
    use TraitAddress;

    public function getName(): string
    {
        return $this->entity->getName() ?? '';
    }

    public function getShortName(): string
    {
        return $this->entity->getShortName() ?? '';
    }
}