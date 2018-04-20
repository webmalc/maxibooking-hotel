<?php

namespace MBH\Bundle\SearchBundle\Tests\Controller;

use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;

class SearchControllerTest extends WebTestCase
{


    /** @dataProvider queryJsonProvider
     * @param $inputData
     */
    public function testSearchRequest($inputData): void
    {

        $this->client->request(
            'POST',
            $inputData['url'],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($inputData['data'])
        );

        $this->isSuccessful($this->client->getResponse());
        $content = $this->client->getResponse()->getContent();
        $this->assertJson($content, 'No JSON Returned');
        $this->assertJsonStringEqualsJsonString(json_encode($inputData['results']), $content, 'JSON Content is wrong');
    }

    public function queryJsonProvider()
    {

        yield
        [
            [
                'url' => '/search/json',
                'data' => [
                    'begin' => '21.04.2018',
                    'end' => '22.04.2018',
                    'adults' => 3,
                    'children' => 4,
                ],
                'results' => [
                    'search_request' => [
                        'status' => 'ok',
                        'expected_results' => 6,
                    ],
                ],
            ],
        ];

    }

}
