<?php

namespace MBH\Bundle\BaseBundle\Lib\Normalization;

class StringFieldType implements NormalizableInterface
{
    /**
     * @param $value
     * @param array $options
     * @return string
     * @throws NormalizationException
     */
    public function normalize($value, array $options)
    {
        return $this->castToString($value);
    }

    /**
     * @param $value
     * @param array $options
     * @return string
     * @throws NormalizationException
     */
    public function denormalize($value, array $options)
    {
        return $this->castToString($value);
    }

    /**
     * @param $value
     * @return string
     * @throws NormalizationException
     */
    private function castToString($value)
    {
        try {
            $result = (string)$value;
        } catch (\Throwable $exception) {
            throw new NormalizationException($exception->getMessage());
        }

        return $result;
    }
}