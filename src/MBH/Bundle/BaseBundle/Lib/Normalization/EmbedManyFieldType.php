<?php

namespace MBH\Bundle\BaseBundle\Lib\Normalization;

use MBH\Bundle\BaseBundle\Service\MBHSerializer;
use MBH\Bundle\BaseBundle\Service\Utils;

class EmbedManyFieldType implements NormalizableInterface
{
    private $documentClass;

    public function __construct(string $documentClass)
    {
        $this->documentClass = $documentClass;
    }

    /**
     * @param $value
     * @param array $options
     * @return array
     * @throws NormalizationException
     */
    public function normalize($value, array $options = [])
    {
        $this->checkIsIterable($value);
        $serializer = $options['serializer'];

        return array_map(function ($singleEmbedded) use ($serializer) {
            return $serializer->normalize($singleEmbedded);
        }, Utils::castIterableToArray($value));
    }

    /**
     * @param $value
     * @param array $options
     * @return array
     * @throws NormalizationException
     */
    public function denormalize($value, array $options)
    {
        $this->checkIsIterable($value);
        /** @var MBHSerializer $serializer */
        $serializer = $options['serializer'];

        return array_map(function ($singleEmbedded) use ($serializer) {
            return $serializer->denormalize(
                $singleEmbedded,
                $serializer->instantiateClass($this->documentClass, $singleEmbedded)
            );
        }, Utils::castIterableToArray($value));
    }

    /**
     * @param $value
     * @throws NormalizationException
     */
    private function checkIsIterable($value)
    {
        if (!is_iterable($value)) {
            throw new NormalizationException('Passed value is not iterable');
        }
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->documentClass;
    }
}