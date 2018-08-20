<?php
/**
 * Created by PhpStorm.
 * Date: 11.07.18
 */

namespace MBH\Bundle\ClientBundle\Service\DocumentSerialize;

/**
 * Class HotelOrganization
 * @package MBH\Bundle\ClientBundle\Service\DocumentSerialize
 */
class HotelOrganization extends Common implements AdvancedAddressInterface
{
    use TraitAddress;

    protected const METHOD = [
        'getShortName',
        'getDirectorFio',
        'getAccountantFio',
        'getPhone',
        'getEmail',
        'getKpp',
        'getLocation',
        'getRegistrationDate|date',
        'getRegistrationNumber',
        'getActivityCode',
        'getOkpoCode',
        'getWriterFio',
        'getReason',
        'getBank',
        'getBankBik',
        'getBankAddress',
        'getCorrespondentAccount',
        'getCheckingAccount',
    ];

    protected function getSourceClassName()
    {
        return \MBH\Bundle\PackageBundle\Document\Organization::class;
    }
}