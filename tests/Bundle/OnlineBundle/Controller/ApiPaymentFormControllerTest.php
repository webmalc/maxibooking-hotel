<?php
/**
 * Created by PhpStorm.
 * Date: 03.12.18
 */

namespace Tests\Bundle\OnlineBundle\Controller;


use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;
use MBH\Bundle\OnlineBundle\Document\PaymentFormConfig;
use MBH\Bundle\OnlineBundle\Form\OrderSearchType;
use MBH\Bundle\PackageBundle\DataFixtures\MongoDB\OrderData;
use MBH\Bundle\PackageBundle\DataFixtures\MongoDB\TouristData;

class ApiPaymentFormControllerTest extends WebTestCase
{
    private const URL_NAME_LOAD = 'online_payment_form_load_js';
    private const URL_NAME_SEARCH = 'online_payment_search_form';
    private const URL_NAME_PAYMENT = 'online_api_payment_form_payment';

    /**
     * @var PaymentFormConfig
     */
    private static $paymentForm;

    private const URL_PREFIX = '/management/online/api_payment_form/';

    public static function setUpBeforeClass()
    {
        self::baseFixtures();

        self::$paymentForm = self::getContainerStat()->get('doctrine.odm.mongodb.document_manager')
            ->getRepository(PaymentFormConfig::class)
            ->findOneBy([]);
    }

    public function setUp()
    {
        $this->client = $this->makeClient();
    }

    public function getDataForUrlLoad(): iterable
    {
        $data = [
            'load, no valid config id, auth true'         => [self::URL_NAME_LOAD, 404, 'fakeConfigId', true],
            'load, valid config id, auth true'            => [self::URL_NAME_LOAD, 200, null, true],
            'load, no valid config id, auth false'        => [self::URL_NAME_LOAD, 404, 'fakeConfigId', false,],
            'load, valid config id, auth false'           => [self::URL_NAME_LOAD, 200, null, false],
            'search_form, no valid config id, auth true'  => [self::URL_NAME_SEARCH, 404, 'fakeConfigId', true],
            'search_form, valid config id, auth true'     => [self::URL_NAME_SEARCH, 200, null, true],
            'search_form, no valid config id, auth false' => [self::URL_NAME_SEARCH, 404, 'fakeConfigId', false],
            'search_form, valid config id, auth false'    => [self::URL_NAME_SEARCH, 200, null, false],
            'payment, auth false'                         => [self::URL_NAME_PAYMENT, 200, null, false],
            'payment, auth true'                          => [self::URL_NAME_PAYMENT, 200, null, true],
        ];

        yield from $data;
    }

    /**
     * @dataProvider getDataForUrlLoad
     */
    public function testUrl(string $routeName, int $status, ?string $configId, bool $auth)
    {
        if ($auth) {
            $this->client = $this->makeAuthenticatedClient();
        }

        $url = $this->getUrlWithConfigId($routeName, $configId);

        $this->getListCrawler($url);

        $this->assertStatusCodeWithMsg($url, $status);
    }

    public function getDataForTestSearchOrder(): iterable
    {
        $data = [
            'invalid email'          => [false, 'doofus_rick@dimension.j19z7', null],
            'valid email'            => [true, TouristData::TOURIST_RICK_DATA['email'], null],
            'use user name, invalid' => [false, TouristData::TOURIST_RICK_DATA['email'], 'Doofus Rick'],
            'use user name, valid'   => [
                true,
                TouristData::TOURIST_RICK_DATA['email'],
                TouristData::TOURIST_RICK_DATA['lastName'],
            ],
        ];

        yield from $data;
    }

    /**
     * @dataProvider getDataForTestSearchOrder
     *
     * @param bool $valid
     * @param string $email
     * @param string|null $userName
     */
    public function testSearchOrder(bool $valid, string $email, ?string $userName)
    {
        $prefixForm = OrderSearchType::PREFIX;

        if ($userName !== null) {
            $config = $this->getPaymentForm();
            if (!$config->isFieldUserNameIsVisible()) {
                $config->setFieldUserNameIsVisible(true);

                $this->getContainer()->get('doctrine.odm.mongodb.document_manager')->persist($config);
                $this->getContainer()->get('doctrine.odm.mongodb.document_manager')->flush();
            }
        }

        $crawler = $this->getListCrawler($this->getUrlWithConfigId(self::URL_NAME_SEARCH));

        $form = $crawler->filter('form#' . $prefixForm)->form();

        $form->setValues([
            $prefixForm . '[numberPackage]' => OrderData::ORDER_DATA_4_NUMBER . '/1',
            $prefixForm . '[phoneOrEmail]'  => $email,
        ]);

        if ($userName !== null) {
            $form->setValues([$prefixForm . '[userName]' => $userName]);
        }

        $this->client->submit($form);

        $result = json_decode($this->client->getResponse()->getContent(), true,512,JSON_UNESCAPED_UNICODE);

        if ($valid) {
            $this->assertTrue(isset($result['needIsPaid']), 'Expected key "needIsPaid" not found in the response.');
            $this->assertEquals(
                $result['data']['orderId'],
                OrderData::ORDER_DATA_4_NUMBER,
                sprintf('Expected number order %s.', OrderData::ORDER_DATA_4_NUMBER)
            );
            $total = OrderData::ORDER_DATA_4_PRICE - OrderData::ORDER_DATA_4_PAID;
            $this->assertEquals(
                $result['data']['total'],
                $total,
                sprintf('Expected number order %s.', OrderData::ORDER_DATA_4_NUMBER)
            );
        } else {
            $this->assertTrue(isset($result['error']), 'Expected key "error" not found in the response.');
            $this->assertContains('Заказ не найден', $result['error']);
        }
    }

    /**
     * @return PaymentFormConfig
     */
    private function getPaymentForm(): PaymentFormConfig
    {
        return self::$paymentForm;
    }

    /**
     * @param string $routeName
     * @param string|null $putConfigId
     * @return string
     */
    private function getUrlWithConfigId(string $routeName, string $putConfigId = null): string
    {
        $configId = $putConfigId === null ? $this->getPaymentForm()->getId() : $putConfigId;

        switch ($routeName) {
            case self::URL_NAME_LOAD:
                $url = 'file/%s/load';
                break;
            case self::URL_NAME_SEARCH:
                $url = 'form/search/%s';
                break;
            case self::URL_NAME_PAYMENT:
                $url = 'payment';
                break;
        }

        return self::URL_PREFIX . sprintf($url, $configId);
    }
}