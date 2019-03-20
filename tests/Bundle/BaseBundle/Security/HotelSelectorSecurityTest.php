<?php


namespace Tests\Bundle\BaseBundle\Security;


use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\UserBundle\Document\User;
use Symfony\Component\BrowserKit\Client;

class HotelSelectorSecurityTest extends WebTestCase
{


    /** @dataProvider dataProvider */
    public function testHotelNoAccess(array $data)
    {

        $credentials = [
            'username' => $data['credentials'][0],
            'password' => $data['credentials'][1]
        ];
        $client = self::createClient(
            [],
            [
                'PHP_AUTH_USER' => $credentials['username'],
                'PHP_AUTH_PW'   => $credentials['password'],
            ]
        );
        $url = $this->createRequest($client);

        $this->assertEquals($data['url'], parse_url($url)['path']);


    }

    public function testHotelAccess()
    {
        $userName = 'manager';
        $dm = $this->getContainer()
            ->get('doctrine.odm.mongodb.document_manager');
        /** @var User $user */
        $user = $dm->getRepository(User::class)->findOneBy(['username' => $userName]);
        /** @var Hotel $hotel */
        $hotel = $dm->getRepository(Hotel::class)->findOneBy([]);

        $credentials = [
            'username' => $userName,
            'password' => 'manager'
        ];

        $client = self::createClient(
            [],
            [
                'PHP_AUTH_USER' => $credentials['username'],
                'PHP_AUTH_PW'   => $credentials['password'],
            ]
        );
        $url = $this->createRequest($client);
        $this->assertEquals('/management/hotel/notfound', parse_url($url)['path']);

        $user->addHotel($hotel);
        $dm->flush($user);

        $client = $this->makeClient($credentials);
        $url = $this->createRequest($client);
        $this->assertEquals('/package/', parse_url($url)['path']);

        $user->removeHotel($hotel);
        $dm->flush($user);
    }

    private function createRequest(Client $client): string
    {
        $client->request('GET', '/');
        $url = '';
        while ($client->getResponse()->getStatusCode() === 302) {
            $url = $client->followRedirect()->getUri();
        }

        return $url;
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