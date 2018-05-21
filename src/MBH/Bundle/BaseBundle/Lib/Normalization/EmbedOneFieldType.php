<?php

namespace MBH\Bundle\BaseBundle\Lib\Normalization;

class EmbedOneFieldType implements NormalizableInterface
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

        return $serializer->normalize($value);
    }

    /**
     * @param $value
     * @param array $options
     * @return array|object
     */
    public function denormalize($value, array $options)
    {
        $serializer = $options['serializer'];

        return $serializer->denormalize($value, $this->documentClass);
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->documentClass;
    }
}