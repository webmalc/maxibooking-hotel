<?php
/**
 * Created by PhpStorm.
 * Date: 21.05.18
 */

namespace Tests\Bundle\ClientBundle\Service\DocumentSerialize;


use MBH\Bundle\ClientBundle\Lib\Test\TraitCommonSerialize;
use Symfony\Component\DependencyInjection\Container;

class MortalTest extends \MBH\Bundle\BaseBundle\Lib\Test\UnitTestCase
{
    use TraitCommonSerialize;

    private const SERVICE_ID = 'MBH\Bundle\ClientBundle\Service\DocumentSerialize\Mortal';
}