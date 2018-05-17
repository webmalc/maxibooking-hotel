<?php

namespace MBH\Bundle\BaseBundle\Lib\Normalization;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Document\Base;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;

class DocumentFieldType implements DBNormalizableInterface
{
    private $documentName;

    public function __construct(string $documentName) {
        $this->documentName = $documentName;
    }

    /**
     * @param $value
     * @return string
     * @throws InvalidArgumentException
     */
    public function normalize($value)
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
     * @param DocumentManager $dm
     * @return object
     * @throws InvalidArgumentException
     */
    public function denormalize($value, DocumentManager $dm)
    {
        if (!\MongoId::isValid($value)) {
            throw new InvalidArgumentException($value . ' is not a valid mongo ID');
        }

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