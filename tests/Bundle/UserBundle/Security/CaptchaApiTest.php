<?php

namespace Tests\Bundle\UserBundle\Security;


use MBH\Bundle\BaseBundle\Lib\Test\UnitTestCase;
use GuzzleHttp\Client as GuzzleClient;
use MBH\Bundle\UserBundle\Lib\Exception\InvisibleCaptchaException;
use Symfony\Component\DependencyInjection\Container;

class CaptchaApiTest extends UnitTestCase
{
    private const GOOGLE_CAPTCHA_API_URL = 'https://www.google.com/recaptcha/api/siteverify';

    private $fakeToken = '03AOLTBLTJKWI9bNfjZu0-8jF0ztX91021I2-WuOUNYMabUipXavtrtLKHfHo6Hoe2LUAQsNqmFN3hcrjy-MeP5ZvlF06sn0QbRzR0TCr4hjGGFfBQx4upbJPjD3hZLmNhTQKFgURlnGypE7t3azAhR_GvfzX5zIHWfJ-PA6ws2WykqP0yx5rv1uuFhT0yMAKmMyqHKkTgVfoEiLFCddq7zHpqg9wrC4OwFHi1XfTlv5XWLCV-IfWU8D5M2gaczfVT9jM9KSje35KnXCO-Dz-XkXSWSOg0neAbT_F4-K_KM41eEhf9jN0rnSk';

    /**
     * @var Container
     */
    protected $container;

    public static function setUpBeforeClass()
    {
        self::baseFixtures();
    }

    public function setUp()
    {
        parent::setUp();

        self::bootKernel();

        $this->container = self::getContainerStat();
    }

    public static function tearDownAfterClass()
    {
            self::clearDB();
    }

    public function testCaptchaException()
    {
        $captcha = $this->container->get('mbh.invisible_captcha');

        $this->expectException(InvisibleCaptchaException::class);

        $captcha->validate($this->fakeToken);
    }

    public function testCaptchaApi()
    {
        $client = new GuzzleClient();

        $guzzleResponse = $client->request(
            'POST',
            self::GOOGLE_CAPTCHA_API_URL,
            [
                'form_params' => [
                    'secret' => $this->container->getParameter('captcha_secret'),
                    'response' => $this->fakeToken
                ]
            ]
        );

        $response = json_decode($guzzleResponse->getBody(), true);

        $this->assertArrayHasKey('success', $response);
        $this->assertEquals($response['success'], false);
        $this->assertArrayHasKey('error-codes', $response);
        $this->assertContains($response['error-codes'][0], ['invalid-input-secret', 'timeout-or-duplicate']);
    }
}