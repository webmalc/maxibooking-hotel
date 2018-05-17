<?php

namespace MBH\Bundle\BaseBundle\Lib\Normalization;

class StringFieldType implements NormalizableInterface
{
    /**
     * @param $value
     * @return string
     */
    public function normalize($value)
    {
        return (string)$value;
    }

    /**
     * @param $value
     * @return string
     */
    public function denormalize($value)
    {
        return (string)$value;
    }
}