<?php
/**
 * Created by PhpStorm.
 * Date: 27.08.18
 */

namespace Tests\Bundle\ClientBundle\Controller;


use MBH\Bundle\BaseBundle\Lib\Test\Traits\AddPaymentSystemsTrait;
use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;
use MBH\Bundle\ClientBundle\Document\ClientConfig;
use MBH\Bundle\ClientBundle\Form\ClientPaymentSystemType;
use MBH\Bundle\ClientBundle\Form\PaymentSystem\PaymentSystemType;
use MBH\Bundle\ClientBundle\Form\PaymentSystemsUrlsType;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\ExtraData;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\HolderNamePaymentSystem;
use MBH\Bundle\ClientBundle\Lib\PaymentSystemDocument;
use Symfony\Component\DomCrawler\Crawler;

class ClientConfigControllerPaymentSystemTest extends WebTestCase
{
    use AddPaymentSystemsTrait;

    private const URL_INDEX = '/management/client/config/payment_systems';
    private const URL_FORM = '/management/client/config/payment_system/form';
    private const URL_REMOVE = '/management/client/config/payment_system/remove/';
    private const URL_ADD_URL = '/management/client/config/payment_urls';

    /** @var ExtraData */
    private static $extraData;

    public static function setUpBeforeClass()
    {
        self::baseFixtures();
    }

    public static function tearDownAfterClass()
    {
        self::clearDB();
    }

    public function getPaymentSystemsNameHolder()
    {
        foreach ($this->getExtraData()->getPaymentSystemsAsObj() as $holder) {
            yield $holder->getName() => [$holder];
        }
    }

    /**
     * проверяем статус ответа
     */
    public function testStatusCode()
    {
        $this->getListCrawler(self::URL_INDEX);

        $this->assertStatusCode(
            200,
            $this->client
        );
    }

    /**
     * @param HolderNamePaymentSystem $holder
     * @dataProvider getPaymentSystemsNameHolder
     */
    public function testNamingClassFormType($holder)
    {
        $className = $this->getClassNamePaymentSystemType($holder);

        $this->assertTrue(
            class_exists($className),
            sprintf('A class with a form for "%s" payment system was not found.', $holder->getName())
        );

        $this->assertInstanceOf(PaymentSystemType::class, $this->getContainer()->get($className));
    }

    public function testDefaultData_HtmlTable()
    {
        $this->checkForMissingPaymentSystemInHtml();
    }

    public function testDefaultData_ClientConfig()
    {
        $this->checkForMissingPaymentSystemInClientConfig();
    }

    public function testInvalid_EmptyPaymentSystem()
    {
        $crawler = $this->getListCrawler($this->getUrlForAddPaymentSystem());

        $form = $crawler->filter('button[name="save"]')
            ->form(
                [
                    ClientPaymentSystemType::FORM_NAME . '[paymentSystem]' => '',
                ]
            );

        $this->client->submit($form);

        $this->assertStatusCode(200, $this->client);

        $this->assertValidationErrors(['children[paymentSystem].data'], $this->client->getContainer());
    }

    /**
     * @depends      testInvalid_EmptyPaymentSystem
     * @param HolderNamePaymentSystem $holder
     * @dataProvider getPaymentSystemsNameHolder
     */
    public function testInvalid_SubmitEmptyData($holder)
    {
        $crawler = $this->getListCrawler($this->getUrlForAddPaymentSystem());

        $form = $crawler->filter('button[name="save"]')
            ->form(
                [
                    ClientPaymentSystemType::FORM_NAME . '[paymentSystem]' => $holder->getKey(),
                ]
            );

        $result = $this->client->submit($form);

        $this->assertStatusCode(200, $this->client);

        $errors = $this->client->getContainer()->get('liip_functional_test.validator')->getLastErrors();
        $this->assertNotEmpty($errors, sprintf('No errors were found for empty data for "%s".', $holder->getName()));

        $another = [];

        foreach ($errors as $error) {
            if (!strpos($error->getPropertyPath(), $holder->getKey())) {
                $another[] = $error->getPropertyPath();
            }
        }

        $this->assertEquals([], $another, 'Found error another payment system.');
    }

    /**
     * @depends      testInvalid_SubmitEmptyData
     * @param HolderNamePaymentSystem $holder
     * @dataProvider getPaymentSystemsNameHolder
     */
    public function testAddAndEditAndRemovePaymentSystem($holder)
    {
        $newData = $this->addPaymentSystem($holder);

        $this->compareData('add new data', $newData, $holder);

        $editData = $this->editPaymentSystem($holder);

        $this->compareData('edit data', $editData, $holder);

        $this->removePaymentSystem($holder);
    }

    public function testPaymentUrl_Status()
    {
        $this->getListCrawler(self::URL_ADD_URL);

        $this->assertStatusCode(
            200,
            $this->client
        );
    }

    /**
     * @depends testPaymentUrl_Status
     */
    public function testPaymentUrl_DefaultData()
    {
        /** @var ClientConfig $clientConfig */
        $clientConfig = $this->client->getContainer()->get('mbh.client_config_manager')->fetchConfig();

        $format = 'Not empty "%s" in clientConfig.';
        $this->assertEmpty($clientConfig->getSuccessUrl(), sprintf($format, 'Success Url'));
        $this->assertEmpty($clientConfig->getFailUrl(), sprintf($format, 'Fail Url'));
    }

    /**
     * @depends testPaymentUrl_DefaultData
     */
    public function testPaymnetUrl_CheckStatusCode_SuccessUrl()
    {
        $this->client->request('GET', '/management/online/api/success/url');

        $this->assertStatusCode(404, $this->client);
    }

    /**
     * @depends testPaymentUrl_DefaultData
     */
    public function testPaymnetUrl_CheckStatusCode_FailUrl()
    {
        $this->client->request('GET', '/management/online/api/fail/url');

        $this->assertStatusCode(404, $this->client);
    }

    /**
     * @depends testPaymentUrl_DefaultData
     */
    public function testPaymentUrl_InvalidData()
    {
        $crawler = $this->getListCrawler(self::URL_ADD_URL);

        $form = $crawler
            ->filter(sprintf('form[name="%s"]', PaymentSystemsUrlsType::FORM_NAME))
            ->form([
                PaymentSystemsUrlsType::FORM_NAME . '[successUrl]' => 'no valid url',
                PaymentSystemsUrlsType::FORM_NAME . '[failUrl]'    => 'no valid url',
            ]);

        $this->client->submit($form);

        $this->assertValidationErrors(
            [
                'data.successUrl',
                'data.failUrl',
            ],
            $this->client->getContainer()
        );
    }

    /**
     * @depends testPaymentUrl_InvalidData
     */
    public function testPaymnetUrl_AddUrl()
    {
        $urlSuccess = 'http://www.success.ru';
        $urlFail = 'http://www.fail.ru';

        $crawler = $this->getListCrawler(self::URL_ADD_URL);

        $form = $crawler
            ->filter(sprintf('form[name="%s"]', PaymentSystemsUrlsType::FORM_NAME))
            ->form([
                PaymentSystemsUrlsType::FORM_NAME . '[successUrl]' => $urlSuccess,
                PaymentSystemsUrlsType::FORM_NAME . '[failUrl]'    => $urlFail,
            ]);

        $this->client->submit($form);

        /** @var ClientConfig $clientConfig */
        $clientConfig = $this->client->getContainer()->get('mbh.client_config_manager')->fetchConfig();

        $this->assertEquals($urlSuccess, $clientConfig->getSuccessUrl(), 'Data in clientConfig->getSuccessUrl');
        $this->assertEquals($urlFail, $clientConfig->getFailUrl(), 'Data in clientConfig->getFailUrl');
    }

    /**
     * @depends testPaymnetUrl_AddUrl
     */
    public function testPaymnetUrl_CheckStatusCodeAfterAdd_SuccessUrl()
    {
        $this->client->request('GET', '/management/online/api/success/url');

        $this->assertStatusCode(302, $this->client);
    }

    /**
     * @depends testPaymnetUrl_AddUrl
     */
    public function testPaymnetUrl_CheckStatusCodeAfterAdd_FailUrl()
    {
        $this->client->request('GET', '/management/online/api/fail/url');

        $this->assertStatusCode(302, $this->client);
    }

    /**
     * @param string $url
     * @param string $msg
     */
    private function findLinkInPage(string $url, string $msg): void
    {
        $crawler = $this->getListCrawler(self::URL_INDEX);

        $this->assertContains(
            $url,
            $crawler->html(),
            sprintf('Not found link for %s', $msg)
        );
    }

    /**
     * @param HolderNamePaymentSystem $holder
     */
    private function removePaymentSystem(HolderNamePaymentSystem $holder): void
    {
        $url = self::URL_REMOVE . $holder->getKey();

        $this->findLinkInPage($url, 'remove');

        $this->getListCrawler($url);

        $this->assertStatusCode(
            302,
            $this->client
        );

        $this->checkForMissingPaymentSystemInHtml();
        $this->checkForMissingPaymentSystemInClientConfig();
    }

    /**
     * @param string $msg
     * @param array $data
     * @param HolderNamePaymentSystem $holder
     */
    private function compareData(string $msg, array $data, HolderNamePaymentSystem $holder): void
    {
        $crawler = $this->getListCrawler($this->getUrlForAddPaymentSystem() . '/' . $holder->getKey());

        $this->assertEquals(
            $data,
            $this->getDataFromForm($crawler, $holder),
            'Compare after ' . $msg
        );
    }

    /**
     * @param HolderNamePaymentSystem $holder
     * @return array
     */
    private function editPaymentSystem(HolderNamePaymentSystem $holder): array
    {
        $url = $this->getUrlForAddPaymentSystem() . '/' . $holder->getKey();

        $this->findLinkInPage($url, 'edit');

        $crawler = $this->getListCrawler($url);

        $editData = $this->getDataFromForm($crawler, $holder, '123456');

        $form = $crawler->filter('button[name="save"]')
            ->form(
                [
                    ClientPaymentSystemType::FORM_NAME . '[paymentSystem]' => $holder->getKey(),
                ]
            );

        $form->setValues($editData);

        $this->client->submit($form);

        return $editData;
    }

    /**
     * @param HolderNamePaymentSystem $holder
     * @return array
     */
    private function addPaymentSystem(HolderNamePaymentSystem $holder): array
    {
        $dataForForm = $this->addToClientConfigPaymentSystem($holder);

        // проверяем статус
        $this->assertStatusCode(302, $this->client);

        // проверяем количество записей платежных систем на странице
        $table = $this->getListCrawler(self::URL_INDEX);

        $this->assertEquals(
            1,
            $table->filter('section.content table tbody>tr')->count(),
            'Entries in the table with payment systems greater than 1'
        );


        /** @var ClientConfig $clientConfig */
        $clientConfig = $this->getContainer()->get('mbh.client_config_manager')->fetchConfig();

        // проверяем что система одна
        $this->assertEquals(
            [$holder->getKey()],
            $clientConfig->getPaymentSystems(),
            'ClientConfig stores more records.'
        );

        /** @var PaymentSystemDocument $srcDoc */
        $srcDoc = $this->getSourceDocumentForPaymentSystemType($holder);

        $getter = 'get' . $srcDoc::fileClassName();

        // проверяем что данные есть
        $this->assertNotEmpty($clientConfig->$getter());

        // и что они одни
        $this->assertEquals(
            [],
            $this->getNamePaymentSystemThatHaveDataInClientConfig($clientConfig, $holder),
            'ClientConfig contains not only ' . $holder->getName()
        );

        return $dataForForm;
    }

    /**
     * @param HolderNamePaymentSystem $holder
     * @return PaymentSystemDocument
     */
    private function getSourceDocumentForPaymentSystemType(HolderNamePaymentSystem $holder): PaymentSystemDocument
    {
        /** @var PaymentSystemType $typeClass */
        $typeClass = $this->getClassNamePaymentSystemType($holder);

        return $typeClass::getSourceDocument();
    }

    /**
     * @param HolderNamePaymentSystem $holder
     * @return string
     */
    private function getClassNamePaymentSystemType(HolderNamePaymentSystem $holder): string
    {
        return "MBH\Bundle\ClientBundle\Form\PaymentSystem\\" . $holder->getName() . "Type";
    }

    /**
     * @return ExtraData
     */
    private function getExtraData(): ExtraData
    {
        if (static::$extraData === null) {
            static::$extraData = $this->getContainer()->get('mbh.payment_extra_data');
        }

        return static::$extraData;
    }

    /**
     *
     *
     * @param ClientConfig $clientConfig
     * @param HolderNamePaymentSystem|null $exceptionSystem
     * @return array
     */
    private function getNamePaymentSystemThatHaveDataInClientConfig(
        ClientConfig $clientConfig,
        HolderNamePaymentSystem $exceptionSystem = null
    ): array {
        $paymentSystem = [];

        /** @var HolderNamePaymentSystem $holder */
        foreach ($this->getExtraData()->getPaymentSystemsAsObj() as $holder) {
            if ($exceptionSystem !== null && $exceptionSystem->getKey() === $holder->getKey()) {
                continue;
            }
            /** @var PaymentSystemDocument $srcDoc */
            $srcDoc = $this->getSourceDocumentForPaymentSystemType($holder);
            $getter = 'get' . $srcDoc::fileClassName();
            if ($clientConfig->$getter() !== null) {
                $paymentSystem[] = $holder->getName();
            }
        }

        return $paymentSystem;
    }

    /**
     * проверка на отсутствие платежных систем
     * в таблице на странице платежных систем
     */
    private function checkForMissingPaymentSystemInHtml(): void
    {
        $table = $this->getListCrawler(self::URL_INDEX);

        $this->assertEquals(
            0,
            $table->filter('section.content table tbody>tr')->count(),
            'Entries in the table with payment systems are found'
        );
    }

    /**
     * проверка на отсутствие платежных систем
     * в клинтКонфиг, как в массиве доступных так и данных о системе
     */
    private function checkForMissingPaymentSystemInClientConfig(): void
    {
        /** @var ClientConfig $clientConfig */
        $clientConfig = $this->client->getContainer()->get('mbh.client_config_manager')->fetchConfig();

        $paymentSystem = $this->getNamePaymentSystemThatHaveDataInClientConfig($clientConfig);

        $this->assertEmpty($clientConfig->getPaymentSystems(), 'Found PaymentSystem in clientConfig');
        $this->assertEquals([], $paymentSystem, 'Found data in clientConfig for payment system');
    }

}