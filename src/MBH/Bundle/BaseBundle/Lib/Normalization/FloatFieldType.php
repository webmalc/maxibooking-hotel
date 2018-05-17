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
     * @return float
     */
    public function normalize($value)
    {
        return round(floatval($value), $this->numberOfDecimal);
    }

    /**
     * @param $value
     * @return float
     */
    public function denormalize($value)
    {
        return round(floatval($value), $this->numberOfDecimal);
    }
}