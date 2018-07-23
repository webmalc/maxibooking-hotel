<?php

namespace Tests\Bundle\SearchBundle\Tests\Controller;

use Tests\Bundle\SearchBundle\SearchWebTestCase;

class SearchControllerTest extends SearchWebTestCase
{

    /** @dataProvider requestDataProvider
     * @param iterable $requestData
     * @param bool $result
     */
    public function testSyncSearchAction(iterable $requestData, bool $result): void
    {
        $this->client->request(
            'POST',
            '/search/sync/json',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($requestData)
        );

        $response = $this->client->getResponse();
        $json = $response->getContent();
        if ($result) {
            $this->assertTrue($response->isSuccessful());
            $this->assertJson($json);
            $answer = json_decode($json, true);
            $this->assertCount(8, $answer['results']);
            $actualResults = $answer['results'];
            foreach ($actualResults as $actualResult) {
                $this->assertArrayHasKey('conditions', $actualResult);
                $this->assertNotEmpty($actualResult['conditions']['id']);
            }

        } else {
            $this->assertFalse($response->isSuccessful());
            $this->assertEquals(400, $response->getStatusCode());
            $answer = json_decode($response->getContent(), true);
            $this->assertArrayHasKey('error', $answer);
            $this->assertNotNull($answer['error']);
        }
    }


    /** @dataProvider requestDataProvider
     * @param iterable $requestData
     * @param bool $result
     */
    public function testAsyncSearchAction(iterable $requestData, bool $result): void
    {

        $this->client->request(
            'POST',
            '/search/async/start',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($requestData)
        );

        $response = $this->client->getResponse();
        $json = $response->getContent();
        if ($result) {
            $this->assertTrue($response->isSuccessful());
            $this->assertJson($json);
            $answer = json_decode($json, true);
            $this->assertArrayHasKey('conditionsId', $answer);
            $this->assertNotEmpty($answer['conditionsId']);
        } else {
            $this->assertFalse($response->isSuccessful());
            $this->assertEquals(400, $response->getStatusCode());
        }
    }


    public function testGetAsyncResultAction()
    {

        $data =
            [
                'begin' => (new \DateTime('midnight +3 days'))->format('d.m.Y'),
                'end' => (new \DateTime('midnight +7 days'))->format('d.m.Y'),
                'adults' => 2,
                'children' => 0,
                'additionalBegin' => 0,
                'roomTypes' => [],
                'tariffs' => []

            ];
        $this->client->request(
            'POST',
            '/search/async/start',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );

        $response = $this->client->getResponse();
        $answer = json_decode($response->getContent(), true);
        $conditionsId = $answer['conditionsId'];

        $count = 0;
        do {
            sleep(1);
            $this->client->request(
                'GET',
                '/search/async/results/'.$conditionsId,
                [],
                [],
                [],
                null
            );
            $response = $this->client->getResponse();
            $count++;
        } while (204 !== $response->getStatusCode() && $count < 5);

        $this->assertEquals(204, $response->getStatusCode());
    }


    public function requestDataProvider()
    {
        yield [
            [
                'begin' => (new \DateTime('midnight +3 days'))->format('d.m.Y'),
                'end' => (new \DateTime('midnight +7 days'))->format('d.m.Y'),
                'adults' => 2,
                'children' => 0,
                'additionalBegin' => 0,
                'roomTypes' => [],
                'tariffs' => []
            ],
            true
        ];

        yield [
            [
                'begin' => '10.66.2018',
                'end' => '24.029.2018',
                'adults' => 2,
                'children' => 0,
                'additionalBegin' => 0,
                'roomTypes' => [],
                'tariffs' => []

            ],
            false
        ];
    }


}
