<?php


namespace MBH\Bundle\SearchBundle\Services\Data\Serializers;


use MBH\Bundle\SearchBundle\Lib\Result\Result;
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

    public function serialize(Result $result)
    {
        $array = $this->serializer->serialize($result, 'json',
            [
                'attributes' => ['begin']
            ]);
        $a = 'b';
    }

    public function deserialize(array $result)
    {

    }
}