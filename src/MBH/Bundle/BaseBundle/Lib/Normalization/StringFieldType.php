<?php

namespace MBH\Bundle\BaseBundle\Lib\Normalization;

class StringFieldType implements NormalizableInterface
{
    /**
     * @param $value
     * @param array $options
     * @return string
     */
    public function normalize($value, array $options)
    {
        return (string)$value;
    }

    /**
     * @param $value
     * @param array $options
     * @return string
     */
    public function denormalize($value, array $options)
    {
        return (string)$value;
    }
}