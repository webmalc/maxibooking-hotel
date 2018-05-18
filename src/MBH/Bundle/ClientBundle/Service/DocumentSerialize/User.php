<?php
/**
 * Created by PhpStorm.
 * Date: 03.05.18
 */

namespace MBH\Bundle\ClientBundle\Service\DocumentSerialize;


use MBH\Bundle\PackageBundle\Lib\DataOfMortalInterface;


class User extends Common implements DataOfMortalInterface
{
    use TraitDataOfMortal;

    protected function getSourceClassName()
    {
        return \MBH\Bundle\UserBundle\Document\User::class;
    }
}