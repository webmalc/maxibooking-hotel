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
class Organization extends Common implements AdvancedAddressInterface
{
    use TraitAddress;

    protected const METHOD = [
        'getName',
        'getShortName',
    ];

    protected function getSourceClassName()
    {
        return OrganizationBase::class;
    }
}