<?php
/**
 * Created by PhpStorm.
 * Date: 28.04.18
 */

namespace MBH\Bundle\ClientBundle\Service\Document;


use MBH\Bundle\PackageBundle\Document\Organization;

class OrganizationSerialize extends CommonSerialize
{
    use AddressSerialize;

    /**
     * @var Organization
     */
    protected $entity;

    public function __construct(Organization $organization)
    {
        $this->entity = $organization;
        $this->setAddress($this->entity);
    }

    public function getName(): string
    {
        return $this->entity->getName() ?? '';
    }

    public function getShortName(): string
    {
        return $this->entity->getShortName() ?? '';
    }
}