<?php

namespace MBH\Bundle\BaseBundle\Lib\Normalization;

use MBH\Bundle\BaseBundle\Service\MBHSerializer;

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
    public function normalize($value, array $options = [])
    {
        $serializer = $options['serializer'];

        return $serializer->normalize($value);
    }

    /**
     * @param $value
     * @param array $options
     * @return array|object
     * @throws NormalizationException
     * @throws \ReflectionException
     */
    public function denormalize($value, array $options)
    {
        /** @var MBHSerializer $serializer */
        $serializer = $options['serializer'];

        return $serializer->denormalize($value, $serializer->instantiateClass($this->documentClass, $value));
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->documentClass;
    }
}