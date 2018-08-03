<?php


namespace Tests\Bundle\SearchBundle\Services\Data\Serializers;


use MBH\Bundle\SearchBundle\Lib\Result\Result;
use MBH\Bundle\SearchBundle\Services\Data\Serializers\ResultSerializer;
use Tests\Bundle\SearchBundle\SearchWebTestCase;

class ResultSerializerTest extends SearchWebTestCase
{

    /** @dataProvider resultProvider */
    public function testSerialize($data): void
    {
        $serializer = $this->getContainer()->get('mbh_search.result_serializer');
        $actual = $serializer->serialize($data);
        $a = 'b';
    }

    /** @dataProvider arrayProvider */
    public function testDeserialize($data): void
    {
        $a = 'b';
    }

    public function resultProvider(): array
    {
        return [
            [
                'result' => $this->getResult(),

            ]
        ];
    }

    public function arrayProvider(): array
    {
        return [
            [
                'array' => $this->getArray()
            ]
        ];
    }


    public function getArray(): array
    {
        return [
            'data' => 'test',
            'arrayInArray' => [
                'key' => 'value'
            ]
        ];
    }
    public function getResult(): Result
    {
        $result = new Result();
        $result->setBegin(new \DateTime());

        return $result;
    }
}