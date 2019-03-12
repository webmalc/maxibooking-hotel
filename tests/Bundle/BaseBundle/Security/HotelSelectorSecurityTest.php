<?php


namespace Tests\Bundle\BaseBundle\Security;


use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;

class HotelSelectorSecurityTest extends WebTestCase
{
    /** @dataProvider dataProvider */
    public function testHotelNoAccess(array $data)
    {

        $credentials = [
            'username' => $data['credentials'][0],
            'password' => $data['credentials'][1
            ]
        ];
        $client = $this->makeClient($credentials);

        $client->request('GET', '/');
        $url = '';
        while ($client->getResponse()->getStatusCode() === 302) {
            $url = $client->followRedirect()->getUri();
        }

        $this->assertEquals($data['url'], parse_url($url)['path']);


    }

    public function dataProvider()
    {
        $data = [
            'admin' => [
                'credentials' =>
                    ['admin', 'admin']
                ,
                'url' => '/package/chessboard/'
            ],

            'manager' => [
                'credentials' =>
                    ['manager', 'manager'],
                'url' => '/management/hotel/notfound'
            ],
        ];

        foreach ($data as $dataInstance) {
            yield [
                'data' => $dataInstance,


            ];
        }
    }
}