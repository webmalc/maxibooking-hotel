<?php

namespace MBH\Bundle\BaseBundle\Lib\Normalization;

use MBH\Bundle\BaseBundle\Document\Base;
use MBH\Bundle\BaseBundle\Service\Utils;

class DocumentFieldType implements NormalizableInterface
{
    private $documentName;

    public function __construct(string $documentName) {
        $this->documentName = $documentName;
    }

    /**
     * @param $value
     * @param array $options
     * @return string
     * @throws NormalizationException
     */
    public function normalize($value, array $options = [])
    {
        if (!is_object($value)) {
            throw new NormalizationException('Passed to normalization value is not an object');
        }

        if (!$value instanceof Base) {
            throw new NormalizationException('Object of class ' . get_class($value) . 'can not be normalized as document');
        }

        return $value->getId();
    }

    /**
     * @param $value
     * @param array $options
     * @return object
     * @throws NormalizationException
     */
    public function denormalize($value, array $options)
    {
        if (!\MongoId::isValid($value)) {
            throw new NormalizationException(Utils::getStringValueOrType($value) . ' is not a valid ID');
        }
        $dm = $options['dm'];

        return $dm->find($this->documentName, $value);
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->documentName;
    }
}