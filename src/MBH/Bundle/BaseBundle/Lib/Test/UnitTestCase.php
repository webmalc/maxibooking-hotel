<?php
/**
 * Created by PhpStorm.
 * User: mb3
 * Date: 06.04.18
 * Time: 13:25
 */

namespace MBH\Bundle\BaseBundle\Lib\Test;


use MBH\Bundle\BaseBundle\Lib\Test\Traits\FixturesTestTrait;

abstract class UnitTestCase extends \Symfony\Bundle\FrameworkBundle\Test\KernelTestCase
{
    use FixturesTestTrait;
}