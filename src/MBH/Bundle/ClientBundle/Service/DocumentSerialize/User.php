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
}