<?php

namespace MBH\Bundle\BaseBundle\Service;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Lib\Normalization\NormalizableInterface;
use MBH\Bundle\BaseBundle\Lib\Normalization\NormalizationException;
use MBH\Bundle\BaseBundle\Lib\Normalization\SerializerSettings;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class MBHSerializer
{
    private $propertyAccessor;
    private $fieldsManager;
    private $dm;

    private $fieldTypes = [];

    public function __construct(PropertyAccessor $propertyAccessor, DocumentFieldsManager $fieldsManager, DocumentManager $dm)
    {
        $this->propertyAccessor = $propertyAccessor;
        $this->fieldsManager = $fieldsManager;
        $this->dm = $dm;
    }

    /**
     * @param string $class
     * @param $field
     * @param NormalizableInterface $fieldType
     * @return MBHSerializer
     */
    public function setSpecialFieldType(string $class, $field, NormalizableInterface $fieldType)
    {
        $this->fieldTypes[$class][$field] = $fieldType;

        return $this;
    }

    /**
     * @param string $class
     * @param array $fieldTypesByFieldNames
     * @return MBHSerializer
     */
    public function setSpecialFieldTypes(string $class, array $fieldTypesByFieldNames)
    {
        foreach ($fieldTypesByFieldNames as $fieldName => $fieldType) {
            $this->fieldTypes[$class][$fieldName] = $fieldType;
        }

        return $this;
    }

    /**
     * @param $document
     * @return array
     * @throws \ReflectionException
     * @throws NormalizationException
     */
    public function normalize($document)
    {
        $reflClass = new \ReflectionClass(get_class($document));

        return $this->normalizeByReflFields($document, $reflClass->getProperties());
    }

    /**
     * @param $document
     * @param array $fields
     * @return array
     * @throws \ReflectionException
     * @throws NormalizationException
     */
    public function normalizeByFields($document, array $fields)
    {
        $class = get_class($document);
        $reflFields = array_map(function (string $field) use ($class) {
            return new \ReflectionProperty($class, $field);
        }, $fields);

        return $this->normalizeByReflFields($document, $reflFields);
    }

    /**
     * @param $document
     * @param array $excludedFields
     * @return array
     * @throws \ReflectionException
     * @throws NormalizationException
     */
    public function normalizeExcludingFields($document, array $excludedFields)
    {
        $reflClass = new \ReflectionClass(get_class($document));
        $filteredFields = array_filter($reflClass->getProperties(), function (\ReflectionProperty $property) use ($excludedFields) {
            return !in_array($property->name, $excludedFields);
        });

        return $this->normalizeByReflFields($document, $filteredFields);
    }

    /**
     * @param $document
     * @param string $group
     * @return array
     * @throws \ReflectionException
     * @throws NormalizationException
     */
    public function normalizeByGroup($document, string $group = SerializerSettings::API_GROUP)
    {
        $class = get_class($document);
        if (!isset(SerializerSettings::NORMALIZED_FIELDS_BY_GROUPS[$class][$group])) {
            throw new \InvalidArgumentException('There is no settings for class ' . $class . ' and group ' . $group);
        }

        $fields = SerializerSettings::NORMALIZED_FIELDS_BY_GROUPS[$class][$group];

        return $this->normalizeByFields($document, $fields);
    }

    /**
     * @param $fieldValue
     * @param \ReflectionProperty $property
     * @return array|bool|float|int|null|string
     * @throws \ReflectionException
     * @throws NormalizationException
     */
    public function normalizeSingleField($fieldValue, \ReflectionProperty $property)
    {
        if (is_null($fieldValue)) {
            return null;
        }

        $options = ['dm' => $this->dm, 'serializer' => $this];
        $fieldType = $this->getCashedFieldType($property->class, $property->getName());
        if (!$fieldType instanceof NormalizableInterface) {
            throw new \InvalidArgumentException('Unexpected field type "' . get_class($fieldType) . '"');
        }

        return $fieldType->normalize($fieldValue, $options);
    }

    /**
     * @param array $dataToDenormalize
     * @param $document
     * @return object
     * @throws \ReflectionException
     * @throws NormalizationException
     */
    public function denormalize(array $dataToDenormalize, $document)
    {
        $documentClass = get_class($document);
        $fieldsToDenormalize = $this->getFieldsToDenormalize(new \ReflectionClass($documentClass));

        foreach ($dataToDenormalize as $fieldName => $value) {
            if (in_array($fieldName, $fieldsToDenormalize)) {
                $denormalizedValue = $this->denormalizeSingleField($value, $documentClass, $fieldName);
                $this->propertyAccessor->setValue($document, $fieldName, $denormalizedValue);
            }
        }

        return $document;
    }

    /**
     * @param $value
     * @param string $documentClass
     * @param string $fieldName
     * @return array|bool|\DateTime|float|int|null|string
     * @throws \ReflectionException
     * @throws NormalizationException
     */
    public function denormalizeSingleField($value, string $documentClass, string $fieldName)
    {
        if (is_null($value)) {
            return null;
        }

        $fieldType = $this->getCashedFieldType($documentClass, $fieldName);
        if (!$fieldType instanceof NormalizableInterface) {
            throw new \InvalidArgumentException('Unexpected field type "' . get_class($fieldType) . '"');
        }

        $options = ['dm' => $this->dm, 'serializer' => $this];

        return $fieldType->denormalize($value, $options);
    }

    /**
     * @param string $className
     * @param array $normalizedData
     * @return object
     * @throws \ReflectionException
     * @throws NormalizationException
     */
    public function instantiateClass(string $className, array $normalizedData)
    {
        $reflClass = new \ReflectionClass($className);
        $reflClass->getConstructor();
        $constructorParams = [];
        foreach ($reflClass->getConstructor()->getParameters() as $parameter) {
            if (!$parameter->isDefaultValueAvailable()) {
                $paramName = $parameter->getName();
                $constructorParams[] = $this->denormalizeSingleField($normalizedData[$paramName], $className, $paramName);
            }
        }

        return $reflClass->newInstanceArgs($constructorParams);
    }

    /**
     * @param \ReflectionClass $reflClass
     * @return array
     */
    private function getFieldsToDenormalize(\ReflectionClass $reflClass)
    {
        $result = [];
        $excludedFields = SerializerSettings::EXCLUDED_FIELDS_BY_NORMALIZATION[$reflClass->name] ?? [];
        foreach ($reflClass->getProperties() as $property) {
            if (!in_array($property->name, $excludedFields)) {
                $result[] = $property->name;
            }
        }

        return $result;
    }

    /**
     * @param string $documentClass
     * @param string $fieldName
     * @return NormalizableInterface
     * @throws \ReflectionException
     */
    private function getCashedFieldType(string $documentClass, string $fieldName)
    {
        if (isset($this->fieldTypes[$documentClass][$fieldName])) {
            return $this->fieldTypes[$documentClass][$fieldName];
        }

        $fieldType = $this->fieldsManager->getFieldType(new \ReflectionProperty($documentClass, $fieldName));
        $this->fieldTypes[$documentClass][$fieldName] = $fieldType;

        return $fieldType;
    }

    /**
     * @param $document
     * @param array $reflFields
     * @return \ReflectionProperty[]
     * @throws \ReflectionException
     * @throws NormalizationException
     */
    private function normalizeByReflFields($document, array $reflFields)
    {
        $normalizedDocument = [];
        $documentClass = get_class($document);

        /** @var \ReflectionProperty $field */
        foreach ($reflFields as $field) {
            $externalField = SerializerSettings::EXTERNAL_FIELD_NAMES_BY_INTERNAL[$documentClass][$field->name] ?? $field->name;
            $fieldValue = $this->propertyAccessor->getValue($document, $field->name);
            $normalizedValue = $this->normalizeSingleField($fieldValue, $field);
            $normalizedDocument[$externalField] = $normalizedValue;
        }

        return $normalizedDocument;
    }
}