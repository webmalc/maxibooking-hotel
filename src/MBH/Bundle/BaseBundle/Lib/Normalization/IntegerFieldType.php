<?php

namespace MBH\Bundle\BaseBundle\Lib\Normalization;

use MBH\Bundle\BaseBundle\Service\Utils;

class IntegerFieldType implements NormalizableInterface
{
    /**
     * @param $value
     * @param array $options
     * @return int
     * @throws NormalizationException
     */
    public function normalize($value, array $options = [])
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
    public function denormalize($value, array $options = [])
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
            throw new NormalizationException(Utils::getStringValueOrType($value) . ' can not be casted to int type');
        }
    }
}