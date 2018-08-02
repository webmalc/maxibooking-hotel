<?php

namespace MBH\Bundle\BaseBundle\Service;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Lib\Normalization\NormalizableInterface;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class MBHSerializer
{
    const DATE_FORMAT = 'd.m.Y';
    const DATETIME_FORMAT = 'd.m.Y H:i';
    const TIME_FORMAT = 'H:i';

    private $propertyAccessor;
    private $fieldsManager;
    private $dm;

    public function __construct(PropertyAccessor $propertyAccessor, DocumentFieldsManager $fieldsManager, DocumentManager $dm)
    {
        $this->propertyAccessor = $propertyAccessor;
        $this->fieldsManager = $fieldsManager;
        $this->dm = $dm;
    }

    /**
     * @param $document
     * @param array|null $includedFields
     * @param array $excludedFields
     * @return array
     * @throws \ReflectionException
     */
    public function normalize($document, array $includedFields = null, $excludedFields = [])
    {
        $normalizedDocument = [];
        $reflClass = new \ReflectionClass(get_class($document));

        foreach ($reflClass->getProperties() as $property) {
            $propertyName = $property->getName();
            if (in_array($propertyName, $excludedFields)
                || (!is_null($includedFields) && !in_array($propertyName, $includedFields))) {
                continue;
            }

            $fieldValue = $this->propertyAccessor->getValue($document, $propertyName);
            $normalizedValue = $this->normalizeSingleField($fieldValue, $property);

            $normalizedDocument[$propertyName] = $normalizedValue;
        }

        return $normalizedDocument;
    }

    /**
     * @param array $documents
     * @param array|null $includedFields
     * @param array $excludedFields
     * @return array
     */
    public function normalizeArrayOfDocuments(array $documents, array $includedFields = null, $excludedFields = [])
    {
        return array_map(function($document) use ($includedFields, $excludedFields) {
            return $this->normalize($document, $includedFields, $excludedFields);
        }, $documents);
    }

    /**
     * @param array $dataToDenormalize
     * @param $documentClass
     * @param array $excludedFields
     * @return object
     * @throws \ReflectionException
     */
    public function denormalize(array $dataToDenormalize, string $documentClass, $excludedFields = [])
    {
        $document = new $documentClass();

        foreach ($dataToDenormalize as $fieldName => $value) {
            if (in_array($fieldName, $excludedFields)) {
                continue;
            }

            $denormalizedValue = $this->denormalizeSingleField($value, $documentClass, $fieldName);
            $this->propertyAccessor->setValue($document, $fieldName, $denormalizedValue);
        }

        return $document;
    }

    /**
     * @param array $normalizedObjects
     * @param string $documentClass
     * @param array $excludedFields
     * @return array
     */
    public function denormalizeArrayOfObjects(array $normalizedObjects, string $documentClass, $excludedFields = [])
    {
        return array_map(function($object) use ($documentClass, $excludedFields) {
            return $this->denormalize($object, $documentClass, $excludedFields);
        }, $normalizedObjects);
    }

    /**
     * @param $value
     * @param $fieldType
     * @return mixed
     */
    public function denormalizeByFieldType($value, NormalizableInterface $fieldType)
    {
        if (is_null($value)) {
            return null;
        }

        $options = ['dm' => $this->dm, 'serializer' => $this];

        return $fieldType->denormalize($value, $options);
    }

    /**
     * @param $fieldValue
     * @param $fieldType
     * @return null
     */
    public function normalizeByFieldType($fieldValue, NormalizableInterface $fieldType)
    {
        if (is_null($fieldValue)) {
            return null;
        }

        $options = ['dm' => $this->dm, 'serializer' => $this];

        return $fieldType->normalize($fieldValue, $options);
    }

    /**
     * @param $fieldValue
     * @param \ReflectionProperty $property
     * @return array|bool|float|int|null|string
     */
    private function normalizeSingleField($fieldValue, \ReflectionProperty $property)
    {
        $fieldType = $this->fieldsManager->getFieldType($property);
        if (!$fieldType instanceof NormalizableInterface) {
            throw new \InvalidArgumentException('Unexpected field type "' . get_class($fieldType) . '"');
        }

        return $this->normalizeByFieldType($fieldValue, $fieldType);
    }

    /**
     * @param $value
     * @param string $documentClass
     * @param string $fieldName
     * @return array|bool|\DateTime|float|int|null|string
     * @throws \ReflectionException
     */
    private function denormalizeSingleField($value, string $documentClass, string $fieldName)
    {
        $fieldType = $this->fieldsManager->getFieldType(new \ReflectionProperty($documentClass, $fieldName));

        return $this->denormalizeByFieldType($value, $fieldType);
    }
}