<?php

namespace MBH\Bundle\BaseBundle\Lib\Normalization;

class FloatFieldType implements NormalizableInterface
{
    private $numberOfDecimal;

    public function __construct(int $numberOfDecimal = 2) {
        $this->numberOfDecimal = $numberOfDecimal;
    }

    /**
     * @param $value
     * @param array $options
     * @return float
     */
    public function normalize($value, array $options)
    {
        return round(floatval($value), $this->numberOfDecimal);
    }

    /**
     * @param $value
     * @param array $options
     * @return float
     */
    public function denormalize($value, array $options)
    {
        return round(floatval($value), $this->numberOfDecimal);
    }
}