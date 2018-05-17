<?php

namespace MBH\Bundle\BaseBundle\Lib\Normalization;

use Symfony\Component\Serializer\Exception\InvalidArgumentException;

class CollectionFieldType implements NormalizableInterface
{
    /**
     * @param $value
     * @return array
     * @throws InvalidArgumentException
     */
    public function normalize($value)
    {
        $this->checkIsIterable($value);

        return (array)$value;
    }

    /**
     * @param $value
     * @return array
     * @throws InvalidArgumentException
     */
    public function denormalize($value)
    {
        $this->checkIsIterable($value);

        return (array)$value;
    }

    /**
     * @param $value
     * @throws InvalidArgumentException
     */
    private function checkIsIterable($value)
    {
        if (!is_iterable($value)) {
            throw new InvalidArgumentException('Passed value is not iterable');
        }
    }
}