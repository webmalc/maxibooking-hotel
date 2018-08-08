<?php


namespace MBH\Bundle\SearchBundle\Services\Data\Serializers;


use MBH\Bundle\SearchBundle\Lib\Result\Result;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class ResultSerializer
{

    /** @var SerializerInterface */
    private $serializer;

    /**
     * ResultSerializer constructor.
     * @param SerializerInterface $serializer
     */
    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function serialize(Result $result, string $type = 'json'): string
    {
        return $this->serializer->serialize($result, $type, [
            'json_encode_options' => JSON_UNESCAPED_UNICODE
        ]);
    }

    public function deserialize($serializedResult, $format = 'json')
    {
        return $this->serializer->deserialize($serializedResult, Result::class, $format);
    }
}