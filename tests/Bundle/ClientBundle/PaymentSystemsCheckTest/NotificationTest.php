<?php
/**
 * Created by PhpStorm.
 * Date: 28.09.18
 */

namespace Tests\Bundle\ClientBundle\PaymentSystemsCheckTest;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\ClientBundle\Document\PaymentSystem\Moneymail;
use MBH\Bundle\ClientBundle\Lib\Test\TraitExtraData;
use MBH\Bundle\ClientBundle\Lib\Test\TraitForAddPaymentSystems;
use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;
use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\ClientBundle\Document\ClientConfig;
use MBH\Bundle\ClientBundle\Document\PaymentSystem\Invoice;
use MBH\Bundle\ClientBundle\Document\PaymentSystem\NewRbk;
use MBH\Bundle\ClientBundle\Document\PaymentSystem\Sberbank;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\HolderNamePaymentSystem;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\NewRbk\Webhook;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\Sberbank\CallbackNotification;
use MBH\Bundle\PackageBundle\Document\Order;
use Symfony\Component\HttpFoundation\Request;

class NotificationTest extends WebTestCase
{
    use TraitForAddPaymentSystems;
    use TraitExtraData;

    private const PREFIX_URL = '/management/online/api/order/check/';

    public static function setUpBeforeClass()
    {
        self::baseFixtures();
    }

    public static function tearDownAfterClass()
    {
        self::clearDB();
    }

    /**
     * Base setup
     */
    public function setUp()
    {
        $this->client = self::makeClient(false);
    }

    /**
     * @return iterable
     */
    public function getPaymentSystemsNameHolder(): iterable
    {
        foreach ($this->getExtraData()->getPaymentSystemsAsObj() as $holder) {
            if ($holder->getKey() === Invoice::KEY) {
                continue;
            }

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
        $this->client = self::makeClient(true);

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
        if ($holder->getKey() === NewRbk::KEY) {
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
     * @depends testSimpleAddPaymentSystems
     */
    public function testValidDataMoneyMail()
    {
        $container = $this->getContainer();
        $dm = $container->get('doctrine.odm.mongodb.document_manager');

        /** @var ClientConfig $clientConfig */
        $clientConfig = $container->get('mbh.client_config_manager')->fetchConfig();

        $moneymail = new Moneymail();
        $moneymail->setMoneymailKey('key');
        $moneymail->setMoneymailShopIDP('1984');

        $clientConfig->setMoneymail($moneymail);

        /** @var Order $order */
        $order = $this->getOrder($dm);

        /** @var CashDocument $cashDocument */
        $cashDocument = $this->createCashDocument($dm, $order);

        $holder = $this->getExtraData()->getPaymentSystemAsObj(Moneymail::KEY);

        $url = self::PREFIX_URL . $holder->getKey();

        $data = [
            'Order_IDP' => $cashDocument->getId(),
            'Status' => 'AS000',
            'Shop_IDP' => $moneymail->getMoneymailShopIDP(),
            'CyberSourceTransactionNumber' => 'WTF',
            'Comission' => '100500',
        ];

        $signature = strtoupper(
            str_replace(
                '-',
                '',
                md5(
                    implode('', $data).$moneymail->getMoneymailKey()
                )
            )
        );

        $data['Signature'] = $signature;

        $this->client->request('GET', $url, $data);

        $this->commonAssert($dm, $url, $order, $cashDocument);
    }

    /**
     * @depends testSimpleAddPaymentSystems
     */
    public function testValidRequestSberbank()
    {
        $container = $this->getContainer();
        $dm = $container->get('doctrine.odm.mongodb.document_manager');

        /** @var ClientConfig $clientConfig */
        $clientConfig = $container->get('mbh.client_config_manager')->fetchConfig();

        $holder = $this->getExtraData()->getPaymentSystemAsObj(Sberbank::KEY);

        /** @var Order $order */
        $order = $this->getOrder($dm);

        /** @var CashDocument $cashDocument */
        $cashDocument = $this->createCashDocument($dm, $order);

        $url = self::PREFIX_URL . $holder->getKey();

        $this->client->request('GET', $url, $this->dataSberbank($clientConfig->getSberbank(), $cashDocument));

        $this->commonAssert($dm, $url, $order, $cashDocument);

        $this->assertContains(
            \MBH\Bundle\ClientBundle\Service\PaymentSystem\Wrapper\Sberbank::SUCCESSFUL_RESPONSE,
            $this->client->getResponse()->getContent()
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
     * runInSeparateProcess
     * @depends testSimpleAddPaymentSystems
     */
    public function testValidRequestNewRbk()
    {
        $container = $this->getContainer();
        $dm = $container->get('doctrine.odm.mongodb.document_manager');

        /** @var ClientConfig $clientConfig */
        $clientConfig = $container->get('mbh.client_config_manager')->fetchConfig();

        $newRbk = $clientConfig->getNewRbk();
        $newRbk->setWebhookKey($this->getPublicKey());

        $clientConfig->setNewRbk($newRbk);

        /** @var Order $order */
        $order = $this->getOrder($dm);

        /** @var CashDocument $cashDocument */
        $cashDocument = $this->createCashDocument($dm, $order);

        $holder = $this->getExtraData()->getPaymentSystemAsObj(NewRbk::KEY);

        $url = self::PREFIX_URL . $holder->getKey();

        $param = json_encode(
            [
                "eventID"   => 0,
                "occuredAt" => (new \DateTime())->format(\DateTime::ATOM),
                "topic"     => Webhook::INVOICES_TOPIC,
                "eventType" => Webhook::PAYMENT_CAPTURED,
                'invoice'   => [
                    "shopID"      => "e7f690a7-2aec-4166-8bf2-d0ff884fde24",
                    "dueDate"     => (new \DateTime('+ 1 day'))->format(\DateTime::ATOM),
                    "amount"      => $cashDocument->getTotal(),
                    "currency"    => "RUB",
                    "product"     => "Maxi-Test",
                    "description" => "Keep.Summer.Safe",
                    "metadata"    => [
                        "cashId" => $cashDocument->getId(),
                    ],
                ],
            ],
            JSON_UNESCAPED_UNICODE
        );

        $this->generateHeaders($param);

        $this->client->request('POST', $url, [], [], [], $param);

        $this->commonAssert($dm, $url, $order, $cashDocument);
    }

    /**
     * @param DocumentManager $dm
     * @return Order
     */
    private function getOrder(DocumentManager $dm): Order
    {
        /** @var Order $order */
        $order = $dm->getRepository('MBHPackageBundle:Order')
            ->findOneBy([
                'deletedAt' => null,
                'isEnabled' => true,
            ]);

        $order->setStatus(Order::ONLINE_STATUS);
        $order->setIsPaid(false);

        $dm->persist($order);
        $dm->flush();

        return $order;
    }

    /**
     * @param DocumentManager $dm
     * @param Order $order
     * @return CashDocument
     */
    private function createCashDocument(DocumentManager $dm, Order $order): CashDocument
    {
        $cashDocument = new CashDocument();
        $cashDocument->setIsConfirmed(false)
            ->setIsPaid(false)
            ->setMethod(CashDocument::METHOD_ELECTRONIC)
            ->setOperation(CashDocument::OPERATION_IN)
            ->setOrder($order)
            ->setTotal($order->getTotalOverwrite());

        $order->addCashDocument($cashDocument);

        $dm->persist($cashDocument);
        $dm->flush();

        return $cashDocument;
    }

    private function commonAssert(DocumentManager $dm, string $url, Order $order, CashDocument $cashDocument): void
    {
        $dm->refresh($cashDocument);
        $dm->refresh($order);

        $this->assertStatusCodeWithMsg($url, 200);

        $this->assertTrue(
            $order->getIsPaid(),
            'The status of isPaid in Order has not been changed.'
        );

        $this->assertTrue(
            $cashDocument->getIsPaid(),
            'The status of isPaid in cashDocument has not been changed.'
        );
    }

    /**
     * Пхпюнит перезаписывает заголовки, поэтому подпись просто записывается в глобальный массив
     *
     * @param string $data
     */
    private function generateHeaders(string $data): void
    {
        $signature = '';
        openssl_sign($data, $signature, $this->getPrivateKey(), OPENSSL_ALGO_SHA256);

        $headers = [
            'HTTP_CONTENT_SIGNATURE' => 'alg=RS256; digest=' . base64_encode($signature),
        ];

        $_SERVER['HTTP_CONTENT_SIGNATURE'] = $headers['HTTP_CONTENT_SIGNATURE'];
    }

    /**
     * Публичный ключ для проверки подписи
     * для теста newRbk
     *
     * @return string
     */
    private function getPublicKey(): string
    {
//        $rec = openssl_pkey_get_public($this->getCertificate());
//
//        $keyData = openssl_pkey_get_details($rec);
//
//        openssl_free_key($rec);
//
//        $publicKey = $keyData['key'];

        /*
         * Ключ сгенерирован на стоках выше
         */
        $publicKey = <<<WEBHOOKEY
-----BEGIN PUBLIC KEY-----
MIICIjANBgkqhkiG9w0BAQEFAAOCAg8AMIICCgKCAgEAx/kF48U25gOk/Xg7m5wr
+ttqH7+00085XpymeyPugM5RT0eo1NwVcCML614G6BFagQ0eEWOUd9g1amDYVz2q
SJqbYp50hqiIpGHXF1unbbf94XPo5b5cDtlqDOkx8Q1zrBi+8PKLOBLJu4aNqVaX
8JBY6Fby9k1nrE9FBFEZDE7sL1ALuD0zUSbExHaoMHYtduESf9RKKzFHiYQK3lmr
FdxnAQnhWZasUorqIdKMAB3+p2dDkv9mDVMHyLu/DWQkbXKp6Cxi5NTErHC211C1
Yuj90CiWFWzzyWS1pi/5V/8tE7kvIG9fLKpyNy76MVyP7qAXoUqi0LqBBJ66fVLC
U5K/IIP1pdtHZzhk5jBwMlUTBqAdt0YF5BAlTt4G562gyNlbbbYcAdMB6+5ZpZDx
DZCnBUb/qEjDO0wIJLPwhFohbTV2mIQE/eZGj7bedGBScZipHNCKdh1Jg8x23nMJ
OVmmhAfrG1kBUUv3f3Sde6G+dYnCX7KQ7xv3ucbR0K4pHlK/DO8d8trxwhWX7WvZ
5J0YyNalrkM906jgh3u1B419j2LiJxsfyJrhIzjNMdePcvf8hfV+zfJw4aVfG2hZ
vaTIS3J/fEbbiQA5zbDpiwWXJ9HYP+j+38mr6qBOVh18JJcsqD6g87mAyAcrG/Ai
vggvs44Bh8jZ7ZJE8zxInhECAwEAAQ==
-----END PUBLIC KEY-----
WEBHOOKEY;

        return $publicKey;
    }

    /**
     * Ключ генерации подписи
     * для теста newRbk
     *
     * @return string
     */
    private function getPrivateKey(): string
    {
        $key = <<<PRIVATE_KEY
-----BEGIN PRIVATE KEY-----
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
WXH3+qEIrVVZTv/G4380e1OPOyyy5Q==
-----END PRIVATE KEY-----
PRIVATE_KEY;

        return $key;
    }

    /**
     * Сертификат для генерации публичного ключа
     * для теста newRbk
     *
     * @return string
     */
    private function getCertificate(): string
    {
        $cert = <<<CERT
-----BEGIN CERTIFICATE-----
MIIFkjCCA3oCCQCKRGwbsM4GNjANBgkqhkiG9w0BAQsFADCBijELMAkGA1UEBhMC
UlUxDTALBgNVBAgMBEMxMzcxETAPBgNVBAcMCFNpbiBDaXR5MQ0wCwYDVQQKDARN
YXhpMQwwCgYDVQQLDANkZXYxDjAMBgNVBAMMBWdvc2hhMSwwKgYJKoZIhvcNAQkB
Fh1nb3NoYS5zZW51a292QG1heGktYm9va2luZy5ydTAeFw0xODA5MjgxMjAxNTBa
Fw0yMTAzMTYxMjAxNTBaMIGKMQswCQYDVQQGEwJSVTENMAsGA1UECAwEQzEzNzER
MA8GA1UEBwwIU2luIENpdHkxDTALBgNVBAoMBE1heGkxDDAKBgNVBAsMA2RldjEO
MAwGA1UEAwwFZ29zaGExLDAqBgkqhkiG9w0BCQEWHWdvc2hhLnNlbnVrb3ZAbWF4
aS1ib29raW5nLnJ1MIICIjANBgkqhkiG9w0BAQEFAAOCAg8AMIICCgKCAgEAx/kF
48U25gOk/Xg7m5wr+ttqH7+00085XpymeyPugM5RT0eo1NwVcCML614G6BFagQ0e
EWOUd9g1amDYVz2qSJqbYp50hqiIpGHXF1unbbf94XPo5b5cDtlqDOkx8Q1zrBi+
8PKLOBLJu4aNqVaX8JBY6Fby9k1nrE9FBFEZDE7sL1ALuD0zUSbExHaoMHYtduES
f9RKKzFHiYQK3lmrFdxnAQnhWZasUorqIdKMAB3+p2dDkv9mDVMHyLu/DWQkbXKp
6Cxi5NTErHC211C1Yuj90CiWFWzzyWS1pi/5V/8tE7kvIG9fLKpyNy76MVyP7qAX
oUqi0LqBBJ66fVLCU5K/IIP1pdtHZzhk5jBwMlUTBqAdt0YF5BAlTt4G562gyNlb
bbYcAdMB6+5ZpZDxDZCnBUb/qEjDO0wIJLPwhFohbTV2mIQE/eZGj7bedGBScZip
HNCKdh1Jg8x23nMJOVmmhAfrG1kBUUv3f3Sde6G+dYnCX7KQ7xv3ucbR0K4pHlK/
DO8d8trxwhWX7WvZ5J0YyNalrkM906jgh3u1B419j2LiJxsfyJrhIzjNMdePcvf8
hfV+zfJw4aVfG2hZvaTIS3J/fEbbiQA5zbDpiwWXJ9HYP+j+38mr6qBOVh18JJcs
qD6g87mAyAcrG/Aivggvs44Bh8jZ7ZJE8zxInhECAwEAATANBgkqhkiG9w0BAQsF
AAOCAgEAoONl+Sk+a7h/WRvE3JSoAhJRIzQsbDQ76Uyxi/oSeLjhsdce1emXVzFQ
W9oLq3X0J6qs4A+DgHnjqRshcJh1IyKec1+6FzXt1FjB88kqI8LRMmNiIczt5QYy
hMcaLel0wwNfVKvqV/PJWWq/x/A2dt5ZECccr10qLY2IcfxvjDmGHNl29CoWrFWC
o4iX8GPoNrKNFIJyFN0hqCOBNJx+5kN5ouKN6TC0brEFdluY1RD4L2ToJtup4N4X
j+jb6izsPIxPxus3ab1LHledRxJ2iwG3WaFPye1p6k7D4PAvCVV3RpcG9rEbny9c
tQZn/cacegDs+ihwIBtkvv/orEFaGHD7Hv3+9pHE+zXlYh8Tsj5KUT5CKxZeCuQY
x46JaHVJ9xUrZ0r0zO8jd2TEnNyrVxHYYX8eVyER/rFA2rw7WQB7rIQ0f2vMpPL6
UveMyZXMoV22GT0XJk+go14AzV12GgyzqoFjcNzexZAmhLjLiMbxM/40raL3xc30
ylypRIdM5OcB1u2m+/3hH849/gLaEnqzf1DP8vJN+5MAMv6KMKTYrVz7KBg6fovp
4AObUPtWpaGuQbwPBkA+kYikXgs9TeGVXc+fjcMcuhm+UYyPea87MjFgsmlU+Bys
t83Vgz8G2/GdOcV73ztZTwQrZ1xkpGSAkJ5n4cgO+rchLifRiaQ=
-----END CERTIFICATE-----
CERT;

        return $cert;
    }
}