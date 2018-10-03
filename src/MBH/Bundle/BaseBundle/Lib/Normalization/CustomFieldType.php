<?php

namespace MBH\Bundle\BaseBundle\Lib\Normalization;

class CustomFieldType implements NormalizableInterface
{
    private $normalizeCallback;
    private $denormalizeCallback;

    public function __construct(callable $normalizeCallback = null, callable $denormalizeCallback = null) {
        $this->normalizeCallback = $normalizeCallback;
        $this->denormalizeCallback = $denormalizeCallback;
    }

    public function normalize($value, array $options)
    {
        if (is_null($this->normalizeCallback)) {
            throw new \InvalidArgumentException();
        }

        return ($this->normalizeCallback)($value);
    }

    public function denormalize($value, array $options)
    {
        if (is_null($this->denormalizeCallback)) {
            throw new \InvalidArgumentException();
        }

        return ($this->denormalizeCallback)($value);
    }
}