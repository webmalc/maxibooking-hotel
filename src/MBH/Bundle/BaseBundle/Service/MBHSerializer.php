<?php

namespace MBH\Bundle\BaseBundle\Service;

use Doctrine\Common\Annotations\CachedReader;
use Doctrine\ODM\MongoDB\Mapping\Annotations\Date;
use Doctrine\ODM\MongoDB\Mapping\Annotations\EmbedMany;
use Doctrine\ODM\MongoDB\Mapping\Annotations\EmbedOne;
use Doctrine\ODM\MongoDB\Mapping\Annotations\Field;
use Doctrine\ODM\MongoDB\Mapping\Annotations\ReferenceMany;
use Doctrine\ODM\MongoDB\Mapping\Annotations\ReferenceOne;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class MBHSerializer
{
    const DATE_FORMAT = 'd.m.Y';

    private $annotationReader;
    private $propertyAccessor;
    private $helper;

    public function __construct(CachedReader $annotationReader, PropertyAccessor $propertyAccessor, Helper $helper) {
        $this->annotationReader = $annotationReader;
        $this->propertyAccessor = $propertyAccessor;
        $this->helper = $helper;
    }

    /**
     * @param $document
     * @param array $excludedFields
     * @return array
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

        return $normalizedDocument;
    }

    /**
     * @param $document
     * @param $propertyName
     * @param $property
     * @return bool|string|array
     * @throws \ReflectionException
     */
    private function normalizeValue($document, $propertyName, $property)
    {
        $fieldValue = $this->propertyAccessor->getValue($document, $propertyName);
        if (is_null($fieldValue)) {
            return null;
        }
        //TODO: Проверить специально указанные случаи
        $annotation = $this->annotationReader->getPropertyAnnotation($property, Field::class);

        if (!is_null($annotation)) {
            switch ($annotation->type) {
                case 'boolean':
                case 'bool':
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

        if (!is_null($this->annotationReader->getPropertyAnnotation($property, ReferenceOne::class))) {
            return $fieldValue->getId();
        }

        if (!is_null($this->annotationReader->getPropertyAnnotation($property, ReferenceMany::class))) {
            return $this->helper->toIds($fieldValue);
        }

        if (!is_null($this->annotationReader->getPropertyAnnotation($property, EmbedOne::class))) {
            return $this->normalize($fieldValue);
        }

        if (!is_null($this->annotationReader->getPropertyAnnotation($property, EmbedMany::class))) {
            array_walk($fieldValue, function ($embeddedDoc) {
                return $this->normalize($embeddedDoc);
            });

            return $fieldValue;
        }

        if (!is_null($this->annotationReader->getPropertyAnnotation($property, Date::class))) {
            return $fieldValue->format(self::DATE_FORMAT);
        }

        return $normalizedValue = (string)$fieldValue;
    }

    public function denormalize($document)
    {
        //TODO: Реализовать
    }
}