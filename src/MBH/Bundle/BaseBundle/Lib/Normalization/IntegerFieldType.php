<?php

namespace MBH\Bundle\BaseBundle\Lib\Normalization;

class IntegerFieldType implements NormalizableInterface
{
    /**
     * @param $value
     * @param array $options
     * @return int
     */
    public function normalize($value, array $options)
    {
        return (int)$value;
    }

    /**
     * @param $value
     * @param array $options
     * @return int
     */
    public function denormalize($value, array $options)
    {
        return (int)$value;
    }
}