<?php

namespace MBH\Bundle\BaseBundle\Lib\Normalization;

use Symfony\Component\Serializer\Exception\InvalidArgumentException;

class CollectionFieldType implements NormalizableInterface
{
    /** @var NormalizableInterface */
    private $elementFieldType;

    public function __construct($elementFieldType = null) {
        $this->elementFieldType = $elementFieldType;
    }

    /**
     * @param $value
     * @param array $options
     * @return array
     */
    public function normalize($value, array $options)
    {
        $this->checkIsIterable($value);

        return is_null($this->elementFieldType) ? (array)$value : array_map(function ($element) use ($options) {
            return $this->elementFieldType->normalize($element, $options);
        }, (array)$value);
    }

    /**
     * @param $value
     * @param array $options
     * @return array
     */
    public function denormalize($value, array $options)
    {
        $this->checkIsIterable($value);

        return is_null($this->elementFieldType) ? (array)$value : array_map(function ($element) use ($options) {
            return $this->elementFieldType->denormalize($element, $options);
        }, (array)$value);
    }

    /**
     * @param $value
     * @throws InvalidArgumentException
     */
    private function checkIsIterable($value)
    {
        if (!is_iterable($value)) {
            throw new InvalidArgumentException('Passed value is not iterable');
        }
    }
}