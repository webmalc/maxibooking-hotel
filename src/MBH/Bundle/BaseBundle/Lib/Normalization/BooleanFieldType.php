<?php

namespace MBH\Bundle\BaseBundle\Lib\Normalization;

class BooleanFieldType implements NormalizableInterface
{
    /**
     * @param $value
     * @param array $options
     * @return bool
     */
    public function normalize($value, array $options)
    {
        return (bool)$value;
    }

    /**
     * @param $value
     * @param array $options
     * @return bool
     */
    public function denormalize($value, array $options)
    {
        return (bool)$value;
    }
}