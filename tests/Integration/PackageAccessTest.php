<?php


namespace Tests\Integration;


use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;
use MBH\Bundle\UserBundle\DataFixtures\MongoDB\UserData;
use Symfony\Component\BrowserKit\Client;
use Symfony\Component\DomCrawler\Crawler;

class PackageAccessTest extends WebTestCase
{
    private const DATA = [
        UserData::USER_ADMIN => [
            'expected' => 15
        ],
        UserData::USER_L_MANAGER => [
            'expected' => 4
        ]
    ];


    /** @dataProvider dataProvider() */
    public function testPackageAccessLmanager($credentials, $expected)
    {

        $client = $this->makeClient($credentials);

        $this->createRequest($client, '/package/json');
        $json = $client->getResponse()->getContent();
        $data = json_decode($json, true)['data'];
        $this->assertCount($expected, $data);
        foreach ($data as $rawData) {
            $html = implode('', $rawData);
            $crawler = new Crawler($html);
            $this->assertEquals(1, $crawler->filter('.fa-check')->count());
        }

    }

    private function createRequest(Client $client, string $uri): string
    {
        $client->request('GET', $uri);
        $url = '';
        while ($client->getResponse()->getStatusCode() === 302 || $client->getResponse()->getStatusCode() === 301) {
            $url = $client->followRedirect()->getUri();
        }

        return $url;
    }

    public function dataProvider()
    {
        foreach (self::DATA as $name => $data) {
            $credentials = [
                'username' => $name,
                'password' => $name
            ];
            $expected = $data['expected'];
            yield [
                $credentials, $expected
            ];
        }
    }
}