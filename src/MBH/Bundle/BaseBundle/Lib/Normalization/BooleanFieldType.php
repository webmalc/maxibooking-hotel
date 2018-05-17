<?php

namespace MBH\Bundle\BaseBundle\Lib\Normalization;

class BooleanFieldType implements NormalizableInterface
{
    /**
     * @param $value
     * @return bool
     */
    public function normalize($value)
    {
        return (bool)$value;
    }

    /**
     * @param $value
     * @return bool
     */
    public function denormalize($value)
    {
        return (bool)$value;
    }
}