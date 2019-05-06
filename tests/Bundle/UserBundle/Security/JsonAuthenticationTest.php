<?php


namespace Tests\Bundle\UserBundle\Security;


use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class JsonAuthenticationTest extends WebTestCase
{
    protected function createAuthenticatedClient($username = 'admin', $password = 'admin'): Client
    {
        $client = static::createClient();
        $client->request(
            'POST',
            '/api/login_check',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            json_encode(array(
                'username' => $username,
                'password' => $password,
            ))
        );

        $actual = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('token', $actual);
        $this->assertNotEmpty($actual['token']);

        $client = static::createClient();
        $client->setServerParameter('HTTP_Authorization', sprintf('Bearer %s', $actual['token']));

        return $client;

    }

    public function testJsonAuth()
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/doc/');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testJsonNoAuth()
    {
        $client = static::createClient();
        $client->request('GET', '/api/doc/');
        $this->assertEquals(401, $client->getResponse()->getStatusCode());
    }
}