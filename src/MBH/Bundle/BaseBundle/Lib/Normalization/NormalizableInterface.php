<?php

namespace MBH\Bundle\BaseBundle\Lib\Normalization;

interface NormalizableInterface
{
    /**
     * @param $value
     * @param array $options
     * @return mixed
     * @throws NormalizationException
     */
    public function normalize($value, array $options);

    /**
     * @param $value
     * @param array $options
     * @return mixed
     * @throws NormalizationException
     */
    public function denormalize($value, array $options);
}