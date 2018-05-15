<?php
/**
 * Created by PhpStorm.
 * Date: 28.04.18
 */

namespace MBH\Bundle\ClientBundle\Service\DocumentSerialize;


use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class Common
{
    public function __construct(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    protected $entity;

    public function newInstance($entity)
    {
        $this->entity = $entity;
        return $this;
    }

    public function __call($name, $arg)
    {
        if(strpos($name, 'get') !== 0) {
            $name = 'get' . ucfirst($name);
        }

        return $this->entity->$name();
    }

    public static function methods(): array
    {
        $self = new \ReflectionClass(static::class);
        $methods = [];

        foreach ($self->getMethods(\ReflectionMethod::IS_PUBLIC) as $method){
            if ($method->isPublic() && strpos($method->name, 'get') === 0){
                $methods[] = $method->name;
            }

        }

        return $methods;
    }
}