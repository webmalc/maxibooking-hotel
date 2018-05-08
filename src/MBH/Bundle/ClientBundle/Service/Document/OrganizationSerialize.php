<?php
/**
 * Created by PhpStorm.
 * Date: 28.04.18
 */

namespace MBH\Bundle\ClientBundle\Service\Document;
use MBH\Bundle\PackageBundle\Document\Organization;

/**
 * Class OrganizationSerialize
 *
 * @property Organization $entity
 *
 * @package MBH\Bundle\ClientBundle\Service\Document
 */

class OrganizationSerialize extends CommonSerialize
{
    use AddressSerialize;

    public function getName(): string
    {
        return $this->entity->getName() ?? '';
    }

    public function getShortName(): string
    {
        return $this->entity->getShortName() ?? '';
    }
}