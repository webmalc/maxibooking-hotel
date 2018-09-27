<?php
/**
 * Created by PhpStorm.
 * Date: 26.09.18
 */

namespace Tests\Bundle\OnlineBundle\Controller;


use MBH\Bundle\BaseBundle\Lib\Test\Traits\AddPaymentSystemsTrait;
use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;
use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\ClientBundle\Document\ClientConfig;
use MBH\Bundle\ClientBundle\Document\PaymentSystem\Invoice;
use MBH\Bundle\ClientBundle\Document\PaymentSystem\NewRbk;
use MBH\Bundle\ClientBundle\Document\PaymentSystem\Sberbank;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\ExtraData;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\HolderNamePaymentSystem;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\Sberbank\CallbackNotification;
use MBH\Bundle\PackageBundle\Document\Order;
use Symfony\Component\HttpFoundation\Request;

class ApiControllerCheckOrderAction extends WebTestCase
{
    use AddPaymentSystemsTrait;

    private const PREFIX_URL = '/management/online/api/order/check/';

    /**
     * @var ExtraData
     */
    private static $extraData;

    public static function setUpBeforeClass()
    {
        self::baseFixtures();
    }

    public static function tearDownAfterClass()
    {
        self::clearDB();
    }

    public function getMethodForInvalidStatus(): iterable
    {
        yield 'GET' => ['GET'];
        yield 'POST' => ['POST'];
    }

    /**
     * @dataProvider getMethodForInvalidStatus
     */
    public function testRequestWithEmptyPaymentSystems(string $method)
    {
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

    public function getPaymentSystemsNameHolder(): iterable
    {
        foreach ($this->getExtraData()->getPaymentSystemsAsObj() as $holder) {
            if ($holder->getKey() === Invoice::KEY_ARRAY) {
                continue;
            }

//            if ($holder->getKey() !== Sberbank::KEY_ARRAY) {
//                continue;
//            }

            yield $holder->getName() => [$holder];
        }
    }

    /**
     * Добавление платежных систем.
     * оформлено ввиде теста, для простоты.
     * используется общий трейт с основным тестом по добавлению ПС tests/Bundle/ClientBundle/Controller/ClientConfigControllerPaymentSystemTest.php
     * Если он вдруг не пройден надо смотреть основной тест по добавлению ПС
     *
     * @param HolderNamePaymentSystem $holder
     * @dataProvider getPaymentSystemsNameHolder
     */
    public function testSimpleAddPaymentSystems(HolderNamePaymentSystem $holder)
    {
        $this->addToClientConfigPaymentSystem($holder);

        $clientConfig = $this->getContainer()->get('mbh.client_config_manager')->fetchConfig();

        $format = '"%s" is not found in the Client Config. This test is needed simply to add payment systems. ';
        $format .= 'If you see this message, means tests: ';
        $format .= '"tests/Bundle/ClientBundle/Controller/ClientConfigControllerPaymentSystemTest.php" - ';
        $format .= 'most likely FAILED.';

        $this->assertNotEmpty(
            $clientConfig->getPaymentSystemDocByName($holder->getKey()),
            sprintf($format, $holder->getName())
        );
    }

    /**
     * @param HolderNamePaymentSystem $holder
     * @dataProvider getPaymentSystemsNameHolder
     * @depends      testSimpleAddPaymentSystems
     */
    public function testRequestWithEmptyRequstBody(HolderNamePaymentSystem $holder)
    {
        $url = self::PREFIX_URL . $holder->getKey();

        $this->client->request('GET', $url);

        $this->assertStatusCodeWithMsg($url, 404);

        $badSignature = 'Bad signature';
        if ($holder->getKey() === NewRbk::KEY_ARRAY) {
            $badSignature = 'Signature is missing';
        }

        $result = strpos(
            $this->client->getResponse()->getContent(),
            $badSignature
        );

        $this->assertTrue(
            $result !== false,
            sprintf(
                'On the page with response was not found error "%s". Url: %s.',
                $badSignature,
                $url
            )
        );

    }

    /**
     * @depends      testSimpleAddPaymentSystems
     */
    public function testValidRequestSberbank()
    {
        $container = $this->getContainer();
        $dm = $container->get('doctrine.odm.mongodb.document_manager');

        /** @var ClientConfig $clientConfig */
        $clientConfig = $container->get('mbh.client_config_manager')->fetchConfig();

        $holder = $this->getExtraData()->getPaymentSystemAsObj(Sberbank::KEY_ARRAY);

        /** @var Order $order */
        $order = $dm->getRepository('MBHPackageBundle:Order')
            ->findOneBy([
                'isPaid' => false,
            ]);

        $cashDocument = new CashDocument();
        $cashDocument->setIsConfirmed(false)
            ->setIsPaid(false)
            ->setMethod(CashDocument::METHOD_ELECTRONIC)
            ->setOperation(CashDocument::OPERATION_IN)
            ->setOrder($order)
            ->setTotal($order->getPaid());

        $order->addCashDocument($cashDocument);

        $dm->persist($cashDocument);
        $dm->flush();

        $url = self::PREFIX_URL . $holder->getKey();

        $this->client->request('GET', $url, $this->dataSberbank($clientConfig->getSberbank(), $cashDocument));

        $dm->refresh($cashDocument);

        $this->assertStatusCodeWithMsg($url, 200);

        $this->assertContains(
            \MBH\Bundle\ClientBundle\Service\PaymentSystem\Wrapper\Sberbank::SUCCESSFUL_RESPONSE,
            $this->client->getResponse()->getContent()
        );

        $this->assertTrue(
            $cashDocument->getIsPaid(),
            'The status of isPaid in cashDocument has not been changed.'
        );
    }

    /**
     * Генерит валидный параметы для запроса от Сбербанка
     *
     * @param Sberbank $sberbank
     * @param CashDocument $cashDocument
     * @return array
     */
    private function dataSberbank(Sberbank $sberbank, CashDocument $cashDocument): array
    {
        $param = [
            'mdOrder'     => '100500',
            'orderNumber' => $cashDocument->getId(),
            'operation'   => CallbackNotification::OPERATION_DEPOSITED,
            'status'      => CallbackNotification::STATUS_SUCCESS,
        ];

        $fakeRequest = new Request();
        $fakeRequest->query->add($param);

        $fakeData = CallbackNotification::parseRequest($fakeRequest);
        $param['checksum'] = $fakeData->generateHmacSha256($sberbank);

        $fakeRequest = null;

        return $param;
    }

    /**
     * @return ExtraData
     */
    private function getExtraData(): ExtraData
    {
        if (self::$extraData === null) {
            self::$extraData = $this->getContainer()->get('mbh.payment_extra_data');
        }

        return self::$extraData;
    }
}