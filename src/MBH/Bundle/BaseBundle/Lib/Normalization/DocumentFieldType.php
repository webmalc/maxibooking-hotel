<?php

namespace MBH\Bundle\BaseBundle\Lib\Normalization;

use MBH\Bundle\BaseBundle\Document\Base;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;

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
     */
    public function normalize($value, array $options)
    {
        if (!is_object($value)) {
            throw new InvalidArgumentException('Passed to normalization value is not an object');
        }

        if (!$value instanceof Base) {
            throw new InvalidArgumentException('Object of class ' . get_class($value) . 'can not be normalized as document');
        }

        return $value->getId();
    }

    /**
     * @param $value
     * @param array $options
     * @return object
     */
    public function denormalize($value, array $options)
    {
        if (!\MongoId::isValid($value)) {
            throw new InvalidArgumentException($value . ' is not a valid mongo ID');
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