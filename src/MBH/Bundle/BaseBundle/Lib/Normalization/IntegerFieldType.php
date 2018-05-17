<?php

namespace MBH\Bundle\BaseBundle\Lib\Normalization;

class IntegerFieldType implements NormalizableInterface
{
    /**
     * @param $value
     * @return int
     */
    public function normalize($value)
    {
        return (int)$value;
    }

    /**
     * @param $value
     * @return int
     */
    public function denormalize($value)
    {
        return (int)$value;
    }
}