<?php

namespace Tests\Bundle\UserBundle\Security;

use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;
use MBH\Bundle\UserBundle\Lib\Exception\InvisibleCaptchaException;
use MBH\Bundle\UserBundle\Service\ReCaptcha\InvisibleCaptcha;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;

/**
 * Class AuthTest
 * @package Tests\Bundle\UserBundle\Security
 */
class AuthCaptchaTest extends WebTestCase
{
    protected const LOCALHOST_URL = 'http://localhost/';

    protected const LOGIN_FORM_URL = 'http://localhost/user/login';

    protected const AFTER_LOGIN_REDIRECT = 'http://localhost/package/chessboard/';

    /**
     * @var string
     */
    private $loginFormUrl;

    /**
     * @var string
     */
    private $loginCheckUrl;

    /**
     * @var array
     */
    private $validFormData;

    /**
     * @var array
     */
    private $invalidFormData;

    /**
     * @return string
     */
    public function getLoginFormUrl()
    {
        return $this->loginFormUrl;
    }

    /**
     * @param mixed $loginFormUrl
     * @return AuthCaptchaTest
     */
    public function setLoginFormUrl(string $loginFormUrl)
    {
        $this->loginFormUrl = $loginFormUrl;
        return $this;
    }

    /**
     * @return string
     */
    public function getLoginCheckUrl()
    {
        return $this->loginCheckUrl;
    }

    /**
     * @param mixed $loginCheckUrl
     * @return AuthCaptchaTest
     */
    public function setLoginCheckUrl(string $loginCheckUrl)
    {
        $this->loginCheckUrl = $loginCheckUrl;
        return $this;
    }

    /**
     * @return array
     */
    public function getValidFormData()
    {
        return $this->validFormData;
    }

    /**
     * @param mixed $validFormData
     * @return AuthCaptchaTest
     */
    public function setValidFormData(array $validFormData)
    {
        $this->validFormData = $validFormData;
        return $this;
    }

    /**
     * @return array
     */
    public function getInvalidFormData()
    {
        return $this->invalidFormData;
    }

    /**
     * @param mixed $invalidFormData
     * @return AuthCaptchaTest
     */
    public function setInvalidFormData(array $invalidFormData)
    {
        $this->invalidFormData = $invalidFormData;
        return $this;
    }

    public static function setUpBeforeClass()
    {
        self::baseFixtures();
    }

    public static function tearDownAfterClass()
    {
        self::clearDB();
    }

    public function setUp()
    {
        $this->client = self::makeClient();

        $this->createSessionAndCookie();

        $this
            ->setLoginFormUrl('/user/login')
            ->setLoginCheckUrl('/user/login_check')
            ->setInvalidFormData([
                '_username' => 'invalid_username',
                '_password' => 'invalid_password'
            ])
            ->setValidFormData([
                '_username' => 'admin',
                '_password' => 'admin',
            ]);
    }

    public function testGetFormPage()
    {
        $crawler = $this->client->request('GET', $this->getLoginFormUrl());
        $formCount = $crawler->filter('form[name="loginForm"]')->count();

        $this->assertStatusCode(200, $this->client);
        $this->assertSame(1, $formCount);
    }

    public function testSendValidForm()
    {
        $this->mockCaptcha(null);

        $form = $this->getForm();
        $form->setValues($this->getValidFormData());
        $this->client->submit($form);

        $this->assertSame(self::LOCALHOST_URL, $this->client->followRedirect()->getUri());
    }

    public function testCheckMainFirewall()
    {
        $this->mockCaptcha(null);

        $form = $this->getForm();
        $form->setValues($this->getValidFormData());
        $this->client->submit($form);

        $this->client->followRedirect();
        $uri = $this->client->followRedirect()->getUri();

        $this->assertStatusCode(200, $this->client);
        $this->assertSame(self::AFTER_LOGIN_REDIRECT, $uri);
    }

    public function testSendInvalidForm()
    {
        $this->mockCaptcha(new InvisibleCaptchaException());

        $form = $this->getForm();
        $form->setValues($this->getInvalidFormData());
        $this->client->submit($form);

        $this->assertSame(self::LOGIN_FORM_URL, $this->client->followRedirect()->getUri());
    }

    /**
     * @return \Symfony\Component\DomCrawler\Form
     */
    private function getForm()
    {
        $crawler = $this->client->request('GET', $this->getLoginFormUrl());
        $form = $crawler->filter('form[name="loginForm"]')->form();

        return $form;
    }

    /**
     * @param $returnValue
     */
    protected function mockCaptcha($returnValue)
    {
        $mock = $this->createMock(InvisibleCaptcha::class);
        $mock->method('validate')->willReturn($returnValue);
        $this->getContainer()->set('mbh.invisible_captcha', $mock);
    }
    
    protected function createSessionAndCookie()
    {
        $session = $this->client->getContainer()->get('session');
        $firewallContext = 'test_content';
        $userManager = $this->client->getContainer()->get('fos_user.user_manager');
        $user = $userManager->findUserByUsername('admin');
        $token = new AnonymousToken(null, $user, ["ROLE_BASE_USER"]);
        $session->set('_security_' . $firewallContext, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }
}