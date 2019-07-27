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
     * @var ObjectNormalizer
     */
    private $normalizer;

    /**
     * ResultSerializer constructor.
     * @param SerializerInterface $serializer
     * @param ObjectNormalizer $normalizer
     */
    public function __construct(SerializerInterface $serializer, ObjectNormalizer $normalizer)
    {
        $this->serializer = $serializer;
        $this->normalizer = $normalizer;
    }

    /**
     * @param Result $result
     * @param string $type
     * @return iterable|string
     */
    public function serialize(Result $result, string $type = 'json')
    {
        if ('array' === $type) {
            return $this->normalize($result);
        }

        return $this->serializer->serialize($result, $type, [
            'json_encode_options' => JSON_UNESCAPED_UNICODE
        ]);
    }

    /**
     * @param Result $result
     * @param array $context
     * @return iterable
     */
    public function normalize(Result $result, array $context = []): array
    {
        return $this->normalizer->normalize($result, null, $context);
    }

    public function deserialize($serializedResult, $format = 'json')
    {
        return $this->serializer->deserialize($serializedResult, Result::class, $format);
    }

    public function encodeArrayToJson(array $result): string
    {
        $encoder = new JsonEncoder();

        return $encoder->encode($result, 'json', [
            'json_encode_options' => JSON_UNESCAPED_UNICODE
        ]);
    }

    public function decodeJsonToArray(string $json): iterable
    {
        $encoder = new JsonEncoder();

        return $encoder->decode($json, 'json');
    }

    /**
     * @param array $result
     * @return Result|object
     */
    public function denormalize(array $result): Result
    {
        return $this->normalizer->denormalize($result, Result::class);
    }
}