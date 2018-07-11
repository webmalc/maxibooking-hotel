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
class HotelOrganization extends Common
{
    use TraitAddress;

    protected const METHOD = [
        'getShortName',
        'getName',
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