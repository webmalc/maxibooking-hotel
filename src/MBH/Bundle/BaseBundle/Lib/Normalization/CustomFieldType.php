<?php

namespace MBH\Bundle\BaseBundle\Lib\Normalization;

use MBH\Bundle\BaseBundle\Lib\Exception;

class CustomFieldType implements NormalizableInterface
{
    private $normalizeCallback;
    private $denormalizeCallback;

    public function __construct(callable $normalizeCallback = null, callable $denormalizeCallback = null) {
        $this->normalizeCallback = $normalizeCallback;
        $this->denormalizeCallback = $denormalizeCallback;
    }

    /**
     * @param $value
     * @param array $options
     * @return mixed
     * @throws Exception
     */
    public function normalize($value, array $options)
    {
        if (is_null($this->normalizeCallback)) {
            throw new Exception('There is no normalize callback for custom field type');
        }

        return ($this->normalizeCallback)($value);
    }

    /**
     * @param $value
     * @param array $options
     * @return mixed
     * @throws Exception
     */
    public function denormalize($value, array $options)
    {
        if (is_null($this->denormalizeCallback)) {
            throw new Exception('There is no denormalize callback for custom field type');
        }

        return ($this->denormalizeCallback)($value);
    }
}