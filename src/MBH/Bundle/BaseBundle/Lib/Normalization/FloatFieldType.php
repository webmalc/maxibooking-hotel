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
     * @throws NormalizationException
     */
    public function normalize($value, array $options)
    {
        $this->checkIsNumeric($value);

        return round(floatval($value), $this->numberOfDecimal);
    }

    /**
     * @param $value
     * @param array $options
     * @return float
     * @throws NormalizationException
     */
    public function denormalize($value, array $options)
    {
        $this->checkIsNumeric($value);

        return round(floatval($value), $this->numberOfDecimal);
    }

    /**
     * @param $value
     * @throws NormalizationException
     */
    private function checkIsNumeric($value)
    {
        if (!is_numeric($value)) {
            throw new NormalizationException('Passed value can not be casted to float type');
        }
    }
}