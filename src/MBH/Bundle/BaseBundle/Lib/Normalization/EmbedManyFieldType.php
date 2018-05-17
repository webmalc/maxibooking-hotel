<?php

namespace MBH\Bundle\BaseBundle\Lib\Normalization;

use MBH\Bundle\BaseBundle\Service\MBHSerializer;

class EmbedManyFieldType implements EmbedNormalizableInterface
{
    private $documentClass;

    public function __construct(string $documentClass) {
        $this->documentClass = $documentClass;
    }

    /**
     * @param $value
     * @param MBHSerializer $serializer
     * @return array
     */
    public function normalize($value, MBHSerializer $serializer)
    {
        return array_map(function($singleEmbedded) use ($serializer) {
            return $serializer->normalize($singleEmbedded);
        }, (array)$value);
    }

    /**
     * @param $value
     * @param MBHSerializer $serializer
     * @return array
     */
    public function denormalize($value, MBHSerializer $serializer)
    {
        return array_map(function($singleEmbedded) use ($serializer) {
            return $serializer->denormalize($singleEmbedded, new $this->documentClass());
        }, (array)$value);
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->documentClass;
    }
}