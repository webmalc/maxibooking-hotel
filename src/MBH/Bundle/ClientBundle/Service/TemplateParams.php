<?php

namespace MBH\Bundle\ClientBundle\Service;


use MBH\Bundle\BaseBundle\Document\Base;

class TemplateParams
{
    protected function availableProperties()
    {
        return [
            'status' => 'getStatus'
        ];
    }

    public function getValueByName($name, Base $entity)
    {
        $properties = $this->availableProperties();

        if(array_key_exists($name, $properties)) {
            $method = $properties[$name];
            if (method_exists($entity, $method)) {
                return $entity->$method();
            }
        }
    }
}