<?php

namespace MBH\Bundle\BaseBundle\Lib\Normalization;

use MBH\Bundle\BaseBundle\Service\Utils;

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
     * @throws NormalizationException
     */
    public function normalize($value, array $options = [])
    {
        $this->checkIsIterable($value);
        $arrayValue = Utils::castIterableToArray($value);

        return is_null($this->elementFieldType) ? $arrayValue : array_map(function ($element) use ($options) {
            return $this->elementFieldType->normalize($element, $options);
        }, $arrayValue);
    }

    /**
     * @param $value
     * @param array $options
     * @return array
     * @throws NormalizationException
     */
    public function denormalize($value, array $options = [])
    {
        $this->checkIsIterable($value);
        $arrayValue = Utils::castIterableToArray($value);

        return is_null($this->elementFieldType) ? $arrayValue : array_map(function ($element) use ($options) {
            return $this->elementFieldType->denormalize($element, $options);
        }, $arrayValue);
    }

    /**
     * @param $value
     * @throws NormalizationException
     */
    private function checkIsIterable($value)
    {
        if (!is_iterable($value)) {
            throw new NormalizationException('Passed value is not iterable');
        }
    }
}