<?php


namespace Tests\Bundle\BaseBundle\Security;


use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\UserBundle\DataFixtures\MongoDB\UserData;
use MBH\Bundle\UserBundle\Document\User;
use Symfony\Component\BrowserKit\Client;

class HotelSelectorSecurityTest extends WebTestCase
{
    public static function setUpBeforeClass()
    {
        self::baseFixtures();
    }

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
        $client->followRedirects();
        $url = $client->request('GET', '/')->getUri();

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


        $url = $this->clientRequest($credentials);
        $this->assertEquals('/management/hotel/notfound', parse_url($url)['path']);

        $user->addHotel($hotel);
        $dm->flush($user);

        $url = $this->clientRequest($credentials);
        $this->assertEquals('/package/', parse_url($url)['path']);

        $user->removeHotel($hotel);
        $dm->flush($user);
    }

    private function clientRequest(array $credentials): string
    {
        $client = self::createClient(
            [],
            [
                'PHP_AUTH_USER' => $credentials['username'],
                'PHP_AUTH_PW'   => $credentials['password'],
            ]
        );
        $client->followRedirects();

        return $client->request('GET', '/')->getUri();
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

    /**
     * @dataProvider getDataForTestWithApi
     */
    public function testHotelAccessWithApiKey(array $data)
    {
        $this->client = $this->makeClient();

        $this->client->followRedirects();

        $crawler = $this->getListCrawler($data['url'] . '?apiKey=' . $data['key']);
        if ($data['resultUrl'] === false) {
            self::assertStatusCode(403, $this->client);
        } else {
            $this->assertEquals($data['resultUrl'], parse_url($crawler->getUri())['path']);
        }

    }

    public function getDataForTestWithApi(): iterable
    {
        yield 'with key user:demo user restriction' => [
            [
                'key'       => UserData::SANDBOX_USER_TOKEN,
                'url'       => '/management/user/',
                'resultUrl' => false,
            ],
        ];

        yield 'with key user:demo group restriction' => [
            [
                'key'       => UserData::SANDBOX_USER_TOKEN,
                'url'       => '/management/group/',
                'resultUrl' => false,
            ],
        ];

        yield 'with key user:demo' => [
            [
                'key'       => UserData::SANDBOX_USER_TOKEN,
                'url'       => '/package/chessboard/',
                'resultUrl' => '/package/chessboard/',
            ],
        ];

        yield 'with key user:demo profile' => [
            [
                'key'       => UserData::SANDBOX_USER_TOKEN,
                'url'       => '/user/profile',
                'resultUrl' => '/user/payment',
            ],
        ];

        yield 'with key user:manager' => [
            [
                'key'       => UserData::USER_MANAGER_API_KEY,
                'url'       => '/package/',
                'resultUrl' => '/management/hotel/notfound',
            ],
        ];
    }


}
