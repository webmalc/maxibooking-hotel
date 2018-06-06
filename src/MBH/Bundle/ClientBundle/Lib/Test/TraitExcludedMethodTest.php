<?php
/**
 * Created by PhpStorm.
 * Date: 21.05.18
 */

namespace MBH\Bundle\ClientBundle\Lib\Test;

use MBH\Bundle\BaseBundle\Lib\Test\UnitTestCase;


trait TraitExcludedMethodTest
{
    public function testExludedMethod()
    {
        $exclude = $this->getExcludedMethods();

        if ($exclude === []) {
            /** класс не имеет исключенных методов */
            return;
        }

        $methodGet = $exclude[0];

        $instance = $this->entity->newInstance($this->getSourceClass());

        $this->assertNull($instance->$methodGet());
    }
}