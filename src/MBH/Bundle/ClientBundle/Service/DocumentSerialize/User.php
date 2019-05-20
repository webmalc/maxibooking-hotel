<?php

namespace MBH\Bundle\ClientBundle\Service\DocumentSerialize;


use MBH\Bundle\PackageBundle\Lib\DataOfMortalInterface;
use MBH\Bundle\PackageBundle\Lib\DocumentRelationOfMortalInterface;


class User extends Common implements DataOfMortalInterface, DocumentRelationOfMortalInterface
{
    use TraitDataOfMortal;
    use TraitDocumentRelation;

    protected function getSourceClassName()
    {
        return \MBH\Bundle\UserBundle\Document\User::class;
    }
}
