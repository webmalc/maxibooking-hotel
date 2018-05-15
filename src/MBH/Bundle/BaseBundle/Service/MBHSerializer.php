<?php

namespace MBH\Bundle\BaseBundle\Service;

use Doctrine\Common\Annotations\CachedReader;
use Doctrine\ODM\MongoDB\Mapping\Annotations\Field;
use Doctrine\ODM\MongoDB\Mapping\Annotations\ReferenceMany;
use Doctrine\ODM\MongoDB\Mapping\Annotations\ReferenceOne;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class MBHSerializer
{
    const DATE_FORMAT = 'd.m.Y';

    private $annotationReader;
    private $propertyAccessor;

    public function __construct(CachedReader $annotationReader, PropertyAccessor $propertyAccessor) {
        $this->annotationReader = $annotationReader;
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * @param $document
     * @param array $excludedFields
     * @throws \ReflectionException
     */
    public function normalize($document, $excludedFields = [])
    {
        $normalizedDocument = [];
        $reflClass = new \ReflectionClass(get_class($document));
        foreach ($reflClass->getProperties() as $property) {
            $propertyName = $property->getName();
            if (in_array($propertyName, $excludedFields)) {
                continue;
            }

            $normalizedDocument[$propertyName] = $this->normalizeValue($document, $propertyName, $property);
        }
    }

    /**
     * @param $document
     * @param $propertyName
     * @param $property
     * @return bool|string
     */
    private function normalizeValue($document, $propertyName, $property)
    {
        $fieldValue = $this->propertyAccessor->getValue($document, $propertyName);
        if (is_null($fieldValue)) {
            return null;
        }
        $annotation = $this->annotationReader->getPropertyAnnotation($property, Field::class);

        if (!is_null($annotation)) {
            switch ($annotation->type) {
                case 'boolean':
                    return (bool)$fieldValue;
                case 'string':
                    return (string)$fieldValue;
                case 'float':
                    return round($fieldValue, 2);
                case 'collection':
                    return $fieldValue;
                case 'numeric':
                case 'int':
                    return (int)$fieldValue;
                case 'date':
                    return $fieldValue->format(self::DATE_FORMAT);
            }
        }

        $annotation = $this->annotationReader->getPropertyAnnotation($property, ReferenceOne::class);
        $annotation = $this->annotationReader->getPropertyAnnotation($property, ReferenceMany::class);

        return $normalizedValue = (string)$fieldValue;
    }
}