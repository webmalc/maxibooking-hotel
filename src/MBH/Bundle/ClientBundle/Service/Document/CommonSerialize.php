<?php
/**
 * Created by PhpStorm.
 * Date: 28.04.18
 */

namespace MBH\Bundle\ClientBundle\Service\Document;


abstract class CommonSerialize
{
    protected $entity;

    public function __call($name, $arg)
    {
        return $this->entity->$name();
    }

    public static function methods(): array
    {
        $self = new \ReflectionClass(static::class);
        $methods = [];

        foreach ($self->getMethods(\ReflectionMethod::IS_PUBLIC) as $method){
//            if($method->isConstructor() || $method->isStatic()){
//                continue;
//            }
            if ($method->isPublic() && strpos($method->name, 'get') === 0){
                $methods[] = $method->name;
            }

        }

        return $methods;
    }
}