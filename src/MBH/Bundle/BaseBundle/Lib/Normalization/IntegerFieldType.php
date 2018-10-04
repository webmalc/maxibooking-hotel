<?php

namespace MBH\Bundle\BaseBundle\Lib\Normalization;

class IntegerFieldType implements NormalizableInterface
{
    /**
     * @param $value
     * @param array $options
     * @return int
     * @throws NormalizationException
     */
    public function normalize($value, array $options)
    {
        $this->checkIsNumeric($value);

        return (int)$value;
    }

    /**
     * @param $value
     * @param array $options
     * @return int
     * @throws NormalizationException
     */
    public function denormalize($value, array $options)
    {
        $this->checkIsNumeric($value);

        return (int)$value;
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