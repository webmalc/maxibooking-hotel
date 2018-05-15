<?php
/**
 * Created by PhpStorm.
 * Date: 28.04.18
 */

namespace MBH\Bundle\ClientBundle\Service\DocumentSerialize;


use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class Common
{
    protected $entity;

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
        if (strpos($name, 'get') !== 0) {
            $name = 'get' . ucfirst($name);
        }

        return $this->entity->$name();
    }

    /**
     * @return array
     * @throws \ReflectionException
     */
    public static function methods(): array
    {
        $self = new \ReflectionClass(static::class);
        $methods = [];

        foreach ($self->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->isPublic() && strpos($method->name, 'get') === 0) {
                $methods[] = $method->name;
            }

        }

        return $methods;
    }
}