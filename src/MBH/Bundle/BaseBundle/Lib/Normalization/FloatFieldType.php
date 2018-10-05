<?php

namespace MBH\Bundle\BaseBundle\Lib\Normalization;

use MBH\Bundle\BaseBundle\Service\Utils;

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
    public function normalize($value, array $options = [])
    {
        $this->checkIsNumeric($value);

        return $this->castToFloat($value);
    }

    /**
     * @param $value
     * @param array $options
     * @return float
     * @throws NormalizationException
     */
    public function denormalize($value, array $options = [])
    {
        $this->checkIsNumeric($value);

        return $this->castToFloat($value);
    }

    /**
     * @param $value
     * @return float
     */
    private function castToFloat($value)
    {
        return round(floatval($value), $this->numberOfDecimal);
    }

    /**
     * @param $value
     * @throws NormalizationException
     */
    private function checkIsNumeric($value)
    {
        if (!is_numeric($value)) {
            throw new NormalizationException(Utils::getStringValueOrType($value) . ' can not be casted to float type');
        }
    }
}