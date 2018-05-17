<?php

namespace MBH\Bundle\BaseBundle\Lib\Normalization;

interface NormalizableInterface
{
    public function normalize($value);
    public function denormalize($value);
}