<?php

namespace MBH\Bundle\BaseBundle\Service;

use Symfony\Component\PropertyAccess\PropertyAccessor;

class DataComparer
{
    private $propertyAccessor;

    public function __construct(PropertyAccessor $propertyAccessor)
    {
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * @param $firstEntity
     * @param $secondEntity
     * @param $comparedFields
     * @param bool $isArray
     * @return bool
     */
    public function isEqualByFields($firstEntity, $secondEntity, $comparedFields, $isArray = false)
    {
        if (is_null($firstEntity) xor is_null($secondEntity)) {
            return false;
        } elseif (is_null($firstEntity) && is_null($secondEntity)) {
            return true;
        }

        $isEqual = true;
        foreach ($comparedFields as $comparedField) {
            if ($isArray) {
                $comparedField = '[' . $comparedField . ']';
            }

            if ($this->propertyAccessor->getValue($firstEntity, $comparedField)
                != $this->propertyAccessor->getValue($secondEntity, $comparedField)) {
                $isEqual = false;
            }
        }

        return $isEqual;
    }
}