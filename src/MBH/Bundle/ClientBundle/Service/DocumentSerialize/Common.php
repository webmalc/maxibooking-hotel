<?php
/**
 * Created by PhpStorm.
 * Date: 28.04.18
 */

namespace MBH\Bundle\ClientBundle\Service\DocumentSerialize;


use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class Common
{
    /**
     * В наследуемых классах перечасляются методы которые доступны у классов источников
     *
     * @var array
     */
    protected const METHOD = [];

    /**
     * @var array
     */
    protected const EXCLUDED_METHOD = [];

    protected $entity;

    /**
     * @var array
     */
    private $methodsClear;

    /**
     * @var bool
     */
    private $isMethodsClearInit = false;

    /**
     * @var array
     */
    private $methodsWithParams;

    /**
     * @var bool
     */
    private $isMethodsWithParamsInit = false;

    /**
     * Common constructor.
     * @param ContainerInterface|null $container
     */
    public function __construct(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @param $entity
     * @return $this
     */
    public function newInstance($entity)
    {
        $this->instanseOf($entity);
        $this->entity = $entity;

        return $this;
    }

    /**
     * @param $name
     * @param $arg
     * @return mixed
     */
    public function __call($name, $arg)
    {
        if (static::EXCLUDED_METHOD !== []) {
            if (in_array($name, static::EXCLUDED_METHOD)) {
                return null;
            }
        }

        if (in_array($name, $this->getMethodsClear())) {
            return $this->getEntityValue($name);
        }

        if (strpos($name, 'get') !== 0) {
            $name = 'get' . ucfirst($name);
        }

        return $this->entity->$name();
    }

    /**
     * @return array
     * @throws \ReflectionException
     */
    public function methods(): array
    {
        $self = new \ReflectionClass(static::class);
        $methods = [];
        foreach ($self->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->isPublic() && strpos($method->name, 'get') === 0) {
                $methods[] = $method->name;
            }
        }

        return array_merge($this->getMethodsClear(), $methods);
    }

    /**
     * Должен возрашать имя класса источника данных
     *
     * @return string
     */
    abstract protected function getSourceClassName();

    /**
     * @param $entity
     */
    protected function instanseOf($entity)
    {
        $sourceName = $this->getSourceClassName();
        if (!($entity instanceof $sourceName)) {
            $msg = 'Class must be an instance of ';
            $msg .= $sourceName . ', ';
            $given = $entity !== null ? 'instance of ' . get_class($entity) : 'null';
            $msg .= $given .' given';

            throw new \TypeError($msg);
        }
    }

    /**
     * @param $methodName
     * @return string
     */
    protected function getEntityValue($methodName): string
    {
        if (array_key_exists($methodName,$this->getMethodsWithParams())) {
            $param = $this->getMethodsWithParams()[$methodName];
            $value = '';
            if (in_array('money', $param)) {
                if ($this->entity->$methodName() !== null) {
                    $value = Helper::numFormat($this->entity->$methodName());
                }
            } elseif (in_array('date', $param)) {
                $data = $this->entity->$methodName();
                if ($data !== null && $data instanceof \DateTime) {
                    $value = $data->format('d.m.Y');
                }
            }

            return $value;
        }

        return $this->entity->$methodName() ?? '';
    }

    /**
     * Все методы. доп параметры удаляются
     *
     * @return array
     */
    private function getMethodsClear(): array
    {
        if (!$this->isMethodsClearInit) {
            $this->methodsClear = array_map(
                function ($rawName) {
                    return explode('|', $rawName)[0];
                },
                static::METHOD);
            $this->isMethodsClearInit = true;
        }

        return $this->methodsClear;
    }

    /**
     * Методы только с параметрами
     * (ключ - имя метода, данные массив параметров)
     *
     * @return array
     */
    private function getMethodsWithParams(): array
    {
        if (!$this->isMethodsWithParamsInit) {
            $this->methodsWithParams = [];
            foreach (static::METHOD as $method) {
                if (strpos($method, '|') !== false) {
                    $p = explode('|', $method);
                    $key = array_shift($p);
                    $this->methodsWithParams[$key] = $p;
                }
            }

            $this->isMethodsWithParamsInit = true;
        }

        return $this->methodsWithParams;
    }
}