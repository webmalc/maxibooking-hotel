<?php


namespace MBH\Bundle\SearchBundle\Services\Data\Serializers;


use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Serializer as SymfonySerializer;
use Symfony\Component\Serializer\SerializerInterface;

class SearchSerializerFactory
{

    public static function createSerializer(): SerializerInterface
    {
        $jsonEncoder = new JsonEncoder();
        $dateTimeNormalizer = new DateTimeNormalizer();
        $arrayDenormalizer = new ArrayDenormalizer();
        $objectNormalizer = new ObjectNormalizer(null, null, null, new ReflectionExtractor());

        return  new SymfonySerializer([$dateTimeNormalizer, $arrayDenormalizer, $objectNormalizer], [$jsonEncoder]);

    }

    public static function createNormalizer(): ObjectNormalizer
    {
        $normalizer = new ObjectNormalizer(null, null, null, new ReflectionExtractor());
        $normalizer->setSerializer(new Serializer([new DateTimeNormalizer(), new ArrayDenormalizer(), new ObjectNormalizer(null, null, null, new ReflectionExtractor())]));

        return $normalizer;
    }
}