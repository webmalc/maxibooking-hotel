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

    private function createRequest(Client $client, string $url = '/'): string
    {
        $client->request('GET', $url);
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

    public function getDataForTestWithApi(): iterable
    {
        yield 'with key user:demo' => [
            [
                'key'       => UserData::SANDBOX_USER_TOKEN,
                'url'       => '/package/chessboard/',
                'resultUrl' => '/package/chessboard/',
            ],
        ];
        yield 'with key user:manger, not access' => [
            [
                'key'       => UserData::USER_MANAGER_API_KEY,
                'url'       => '/package/chessboard/',
                'resultUrl' => '/management/hotel/notfound',
            ],
        ];
        yield 'with key user:manager' => [
            [
                'key'       => UserData::USER_MANAGER_API_KEY,
                'url'       => '/management/hotel/notfound',
                'resultUrl' => '/management/hotel/notfound',
            ],
        ];
    }

    /**
     * @dataProvider getDataForTestWithApi
     */
    public function testHotelAccessWithApiKey(array $data)
    {
        $this->client = self::makeClient(false);

        $this->client->followRedirects();

        $crawler = $this->getListCrawler($data['url'] . '?apiKey=' . $data['key']);

        $this->assertEquals($data['resultUrl'], parse_url($crawler->getUri())['path']);
    }
}
