<?php

namespace MBH\Bundle\BaseBundle\Lib\Normalization;

class EmbedManyFieldType implements NormalizableInterface
{
    private $documentClass;

    public function __construct(string $documentClass) {
        $this->documentClass = $documentClass;
    }

    /**
     * @param $value
     * @param array $options
     * @return array
     */
    public function normalize($value, array $options)
    {
        $serializer = $options['serializer'];

        return array_map(function($singleEmbedded) use ($serializer) {
            return $serializer->normalize($singleEmbedded);
        }, $this->castToArray($value));
    }

    /**
     * @param $value
     * @param array $options
     * @return array
     */
    public function denormalize($value, array $options)
    {
        $serializer = $options['serializer'];

        return array_map(function($singleEmbedded) use ($serializer) {
            return $serializer->denormalize($singleEmbedded, $this->documentClass);
        }, $this->castToArray($value));
    }

    /**
     * @param $value
     * @return array
     */
    private function castToArray($value)
    {
        return is_array($value) ? $value : iterator_to_array($value);
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->documentClass;
    }
}