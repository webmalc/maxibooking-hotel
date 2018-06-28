<?php
/**
 * Created by PhpStorm.
 * Date: 21.05.18
 */

namespace Bundle\ClientBundle\Service\DocumentSerialize;


use MBH\Bundle\BaseBundle\Lib\Test\UnitTestCase;
use MBH\Bundle\ClientBundle\Lib\Test\TraitCommonSerialize;
use MBH\Bundle\ClientBundle\Lib\Test\TraitExcludedMethodTest;
use MBH\Bundle\PackageBundle\Component\PackageServiceGroupByService;
use MBH\Bundle\PriceBundle\Document\Service;

class ServiceGroupTest extends UnitTestCase
{
    use TraitCommonSerialize;
    use TraitExcludedMethodTest;

    private const SERVICE_ID = 'MBH\Bundle\ClientBundle\Service\DocumentSerialize\ServiceGroup';

    public function testGetParentMethod()
    {
        $methodsTarget = $this->entity->methods();

        $srcEntity = $this->getSourceClass();

        $exclude = $this->getExcludedMethods();

        $source = new \ReflectionClass($this->getSourceClassName());
        $methodsSource = [];
        foreach ($source->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            if (strpos($method->getName(), 'get') === 0) {
                $methodsSource[] = $method->getName();
            }
        }

        $parentMethods = array_diff($methodsSource, $methodsTarget, $exclude);

        if ($parentMethods === []) {
            /** т.к. в классе источнике все методы переопределены в serialize классе */
            return;
        }

        $methodGet = end($parentMethods);

        $value = $srcEntity->$methodGet();

        $serialize = $this->entity->newInstance($srcEntity);

        $this->assertEquals($value, $serialize->$methodGet());
    }

    /**
     * @return PackageServiceGroupByService
     */
    private function getSourceClass()
    {
        $service = new Service();

        return new PackageServiceGroupByService($service,'10');
    }
}