<?php
/**
 * Created by PhpStorm.
 * Date: 03.05.18
 */

namespace MBH\Bundle\ClientBundle\Service\Document;


use MBH\Bundle\PackageBundle\Lib\DataOfMortalInterface;
use MBH\Bundle\UserBundle\Document\User;

class UserSerialize extends CommonSerialize implements DataOfMortalInterface
{
    use DataOfMortalSerialize;
}