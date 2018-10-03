<?php
/**
 * Created by PhpStorm.
 * Date: 26.09.18
 */

namespace Tests\Bundle\OnlineBundle\Controller;


use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;
use MBH\Bundle\UserBundle\DataFixtures\MongoDB\UserData;
use MBH\Bundle\UserBundle\Document\User;


class ApiControllerCheckOrderAction extends WebTestCase
{
    private const PREFIX_URL = '/management/online/api/order/check/';

    public static function setUpBeforeClass()
    {
        $userData = UserData::USERS['user-admin'];

        $container = self::getContainerStat();

        $manager = $container->get('doctrine.odm.mongodb.document_manager');

        $user = new User();
        $user->setUsername($userData['username'])
            ->setEmail($userData['email'])
            ->addRole($userData['role'])
            ->setPlainPassword($userData['password'])
            ->setEnabled(true)
            ->setLocked(false);

        $manager->persist($user);
        $manager->flush();
    }

    public function getMethodForInvalidStatus(): iterable
    {
        yield 'method GET, auth TRUE' => ['GET', true];
        yield 'method GET, auth FALSE' => ['GET', false];
        yield 'method POST, auth TRUE' => ['POST', true];
        yield 'method POST, auth FALSE' => ['POST', false];
    }

    /**
     * @dataProvider getMethodForInvalidStatus
     */
    public function testRequestWithEmptyPaymentSystems(string $method, bool $auth)
    {
        $this->client = self::makeClient($auth);

        $fakeSystem = 'fake-payment-system';
        $url = self::PREFIX_URL . $fakeSystem;

        $this->client->request($method, $url);

        $this->assertStatusCodeWithMsg($url, 404);

        $result = strpos(
            $this->client->getResponse()->getContent(),
            $fakeSystem
        );

        $this->assertTrue(
            $result !== false,
            sprintf(
                'On the page with response was not found error about not valid payment system. Url: %s, method: %s.',
                $url,
                $method
            )
        );
    }
}