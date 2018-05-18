<?php

namespace MBH\Bundle\BaseBundle\Lib\Normalization;

interface NormalizableInterface
{
    public function normalize($value, array $options);
    public function denormalize($value, array $options);
}