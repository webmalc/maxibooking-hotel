<?php

namespace MBH\Bundle\BaseBundle\Lib\Normalization;

use MBH\Bundle\BaseBundle\Service\MBHSerializer;

class EmbedOneFieldType implements EmbedNormalizableInterface
{
    private $documentClass;

    public function __construct(string $documentClass) {
        $this->documentClass = $documentClass;
    }

    /**
     * @param $value
     * @param MBHSerializer $serializer
     * @return array
     * @throws \ReflectionException
     */
    public function normalize($value, MBHSerializer $serializer)
    {
        return $serializer->normalize($value);
    }

    /**
     * @param $value
     * @param MBHSerializer $serializer
     * @return array
     */
    public function denormalize($value, MBHSerializer $serializer)
    {
        return $serializer->denormalize($value, new $this->documentClass());
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->documentClass;
    }
}