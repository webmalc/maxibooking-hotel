<?php

namespace MBH\Bundle\BaseBundle\Lib\Normalization;

use MBH\Bundle\BaseBundle\Service\Utils;

class StringFieldType implements NormalizableInterface
{
    /**
     * @param $value
     * @param array $options
     * @return string
     * @throws NormalizationException
     */
    public function normalize($value, array $options = [])
    {
        return $this->castToString($value);
    }

    /**
     * @param $value
     * @param array $options
     * @return string
     * @throws NormalizationException
     */
    public function denormalize($value, array $options = [])
    {
        return $this->castToString($value);
    }

    /**
     * @param $value
     * @return string
     * @throws NormalizationException
     */
    private function castToString($value)
    {
        if (Utils::canConvertToString($value)) {
            return (string)$value;
        }

        throw new NormalizationException(Utils::getStringValueOrType($value) . ' can not be casted to string');
    }
}