<?php


namespace Tests\Bundle\BaseBundle\Security;


use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;
use MBH\Bundle\BaseBundle\Security\HotelVoter;
use MBH\Bundle\BaseBundle\Security\PackageVoter;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\UserBundle\DataFixtures\MongoDB\UserData;
use MBH\Bundle\UserBundle\Document\User;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManager;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class VotersTest extends WebTestCase
{

    private const RESULTS = [
        UserData::USER_MANAGER => [
            'hotelResults' => [
                'access' => false,
                'edit' => false,
                'fake' => false
            ],
            'packageResults' => [
                'view' => 5,
                'edit' => 5,
                'fake' => 0
            ]
        ],
        UserData::USER_L_MANAGER => [
            'hotelResults' => [
                'access' => true,
                'edit' => false,
                'fake' => false
            ],
            'packageResults' => [
                'view' => 4,
                'edit' => 4,
                'fake' => 0
            ]
        ],
        UserData::USER_ADMIN => [
            'packageResults' => [
                'view' => 8,
                'edit' => 8,
                'fake' => 0
            ]
        ]
    ];

    public function setUp()
    {
        parent::setUp();
    }

    /** @dataProvider dataHotelProvider */
    public function testHotelAccess($user, $attributes, $object, $expected)
    {
        $voter = new HotelVoter();
        $manager = $this->createDecisionManager($voter);
        $token = new UsernamePasswordToken($user, [], 'fakeProviderKey');

        $actual = $manager->decide($token, $attributes, $object);
        $this->assertEquals($expected, $actual);
    }

    /** @dataProvider dataPackageProvider  */
    public function testPackageAccess($user, $attributes, $objects, $expected)
    {
        $voter = new PackageVoter();
        $manager = $this->createDecisionManager($voter);
        $token = new UsernamePasswordToken($user, [], 'fakeProviderKey');

        $result = 0;
        foreach ($objects as $package) {
            $result += (int)$manager->decide($token, $attributes, $package);
        }

        $this->assertEquals($expected, $result, sprintf('User %s', $user->getName()));

    }


    private function createDecisionManager(Voter $voter): AccessDecisionManager
    {
        return new AccessDecisionManager([$voter], AccessDecisionManager::STRATEGY_AFFIRMATIVE);
    }

    public function dataHotelProvider()
    {
        parent::baseFixtures();

        $dm = $this->getContainer()->get('doctrine.odm.mongodb.document_manager');
        foreach ([UserData::USER_MANAGER, UserData::USER_L_MANAGER] as $userName) {
            $user = $dm->getRepository(User::class)->findOneBy(['username' => $userName]);
            $hotel = $dm->getRepository(Hotel::class)->findOneBy([]);
            $data = self::RESULTS[$userName];
            foreach ($data['hotelResults'] ?? [] as $attribute => $result) {
                yield [
                    $user, [$attribute], $hotel, $result
                ];
            }
            $package = $dm->getRepository(Package::class)->findOneBy([]);
            yield [
                $user, ['access'], $package, false
            ];

        }
    }

    public function dataPackageProvider()
    {
        $dm = $this->getContainer()->get('doctrine.odm.mongodb.document_manager');
        $packages = $dm->getRepository(Package::class)->findAll();
        foreach (self::RESULTS as $userName => $data) {
            $user = $dm->getRepository(User::class)->findOneBy(['username' => $userName]);
            foreach ($data['packageResults'] as $attribute => $result) {
                yield [
                    $user, [$attribute], $packages, $result
                ];
            }
            $hotel = $dm->getRepository(Hotel::class)->findOneBy([]);
            yield [
                $user, [$attribute], [$hotel], 0
            ];
        }
    }
}