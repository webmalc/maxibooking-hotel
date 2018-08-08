<?php


namespace MBH\Bundle\SearchBundle\Services\Data\Serializers;


use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class SearchSerializerFactory
{

    public static function createSerializer(): SerializerInterface
    {
        $jsonEncoder = new JsonEncoder();
        $dateTimeNormalizer = new DateTimeNormalizer();
        $objectNormalizer = new ObjectNormalizer(null, null, null, new ReflectionExtractor());

        return  new \Symfony\Component\Serializer\Serializer([$dateTimeNormalizer, $objectNormalizer], [$jsonEncoder]);

    }

}