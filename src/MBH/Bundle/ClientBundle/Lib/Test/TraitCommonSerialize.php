<?php
/**
 * Created by PhpStorm.
 * Date: 21.05.18
 */


namespace MBH\Bundle\ClientBundle\Lib\Test;



use Symfony\Component\DependencyInjection\Container;

trait TraitCommonSerialize
{

    /**
     * @var Container
     */
    private $container;

    private $entity;

    public function setUp()
    {
        parent::setUp();

        self::bootKernel();
        $this->container = self::getContainerStat();

        $this->entity = $this->container->get(self::SERVICE_ID);
    }

    public function testService()
    {
        $this->assertInstanceOf(self::SERVICE_ID, $this->entity);
    }

    /**
     * @expectedException \TypeError
     */
    public function testNewInstanceException()
    {
        $this->entity->newInstance($this->entity);
    }


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

        extract($this->getMethodData($parentMethods, $source), EXTR_PREFIX_ALL, 'method');

        $srcEntity->$method_set($method_value);

        $serialize = $this->entity->newInstance($srcEntity);

        $this->assertEquals($method_value, $serialize->$method_get());
    }

    /**
     * Возращает имена геттера и сеттера и значение (для записи)
     *
     * @param array $methods
     * @param $src
     * @return array
     */
    private function getMethodData(array $methods, $src): array
    {
        if (empty($methods)) {
            /** сюда могли попасть, в том числе, если $doc === false  */
            throw new \LogicException('empty array with methods');
        }

        $value = 100500;

        $key = array_rand($methods);
        $methodGet = $methods[$key];
        unset($methods[$key]);

        $doc = $src->getMethod($methodGet)->getDocComment();

        if ($doc === false || preg_match('/\@return\s(.+?)(\s\$.+?)?$/m', $doc, $match) !== 1) {
            return $this->getMethodData($methods, $src);
        }

        switch ($match[1]) {
            case 'string':
                $value = (string)$value;
                break;
            case 'int':
                $value = (int)$value;
                break;
            case 'float':
                $value = (float)$value;
                break;
            default:
                return $this->getMethodData($methods, $src);
        }

        $methodSet = str_replace('get', 'set', $methodGet);

        try {
            $src->getMethod($methodSet);
        } catch (\ReflectionException $e) {
            return $this->getMethodData($methods, $src);
        }

        return [
            'set'   => $methodSet,
            'get'   => $methodGet,
            'value' => $value,
        ];
    }

    /**
     * @return object
     * @throws \ReflectionException
     */
    private function getSourceClass()
    {
        $className = $this->getSourceClassName();

        return new $className();
    }

    /**
     * Методы класса источника, к которым закрыт доступ
     *
     * @return array
     * @throws \ReflectionException
     */
    private function getExcludedMethods(): array
    {
        $serializeClass = new \ReflectionClass(self::SERVICE_ID);

        return $serializeClass->getConstant('EXCLUDED_METHOD');
    }

    /**
     * Наименование класса источника
     *
     * @return string
     * @throws \ReflectionException
     */
    private function getSourceClassName(): string
    {
        $method = new \ReflectionMethod(self::SERVICE_ID, 'getSourceClassName');
        $method->setAccessible(true);

        return $method->invoke($this->entity);
    }
}