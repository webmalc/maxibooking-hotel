<?php

namespace MBH\Bundle\BaseBundle\Lib\Normalization;

use MBH\Bundle\BaseBundle\Service\Utils;

class BooleanFieldType implements NormalizableInterface
{
    /**
     * @param $value
     * @param array $options
     * @return bool
     * @throws NormalizationException
     */
    public function normalize($value, array $options)
    {
        if (!Utils::canBeCastedToBool($value)) {
            throw new NormalizationException('Can not normalize ' . $value . ' to boolean value');
        }

        return is_string($value) ? $value === 'true' : (bool)$value;
    }

    /**
     * @param $value
     * @param array $options
     * @return bool
     * @throws NormalizationException
     */
    public function denormalize($value, array $options)
    {
        if (!Utils::canBeCastedToBool($value)) {
            throw new NormalizationException('Can not denormalize ' . $value . ' to boolean value');
        }

        return is_string($value) ? $value === 'true' : (bool)$value;
    }
}