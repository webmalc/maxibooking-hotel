<?php

/**
 * Created by PhpStorm.
 * Date: 20.06.18
 */


class CashControllerTest extends \MBH\Bundle\BaseBundle\Lib\Test\WebTestCase
{
    private const URL_BASE = '/cash/';
    private const URL_ADD_NEW = '/cash/new';
    private const URL_JSON = '/cash/json';
    private const URL_EXPORT_ONE_C = '/cash/export/1c';

    private const URL_POSTFIX_DELETE = 'delete';
    private const URL_POSTFIX_EDIT = 'edit';
    private const URL_POSTFIX_CONFIRM = 'confirm';
    private const URL_POSTFIX_PAY = 'pay';
    private const URL_POSTFIX_CARD_MONEY = 'card/money';

    private const DB_DEFAULT_AMOUNT_RECORDS = 8;

    private const QUERY_FILTER_DELETED_AT = 'filter=deletedAt';
    private const QUERY_FILTER_DELETED_COMMON = 'deleted=1';
    private const QUERY_FILTER_DOCUMENT_DATE = 'filter=documentDate';
    private const QUERY_FILTER_PAID_DATE = 'filter=paidDate';
    private const QUERY_FILTER_SHOW_NO_PAID_1 = 'show_no_paid=1';
    private const QUERY_FILTER_SHOW_NO_CONFIRMED_1 = 'show_no_confirmed=1';
    private const QUERY_FILTER_BY_DAY = 'by_day=1';

    private const ORG_TEST_CHECKING_ACCOUNT = 'TestCheckingAccount';

    private const VALUES_DEFAULT = [
        'draw'                => '',
        'noConfirmedTotalIn'  => '1,360.00',
        'noConfirmedTotalOut' => '0.00',
        'recordsFiltered'     => '8',
        'recordsTotal'        => '8',
        'total'               => '12,647.00',
        'totalIn'             => '12,647.00',
        'totalOut'            => '0.00',
        'data'                => [],
    ];

    private const VALUES_COMMON_WITH_200_NOT_CONFIRMED = [
        'draw'                => '',
        'noConfirmedTotalIn'  => '1,560.00',
        'noConfirmedTotalOut' => '0.00',
        'recordsFiltered'     => '9',
        'recordsTotal'        => '9',
        'total'               => '12,847.00',
        'totalIn'             => '12,847.00',
        'totalOut'            => '0.00',
        'data'                => [],
    ];

    private const VALUES_COMMON_WITH_200_CONFIRMED = [
        'draw'                => '',
        'noConfirmedTotalIn'  => '1,360.00',
        'noConfirmedTotalOut' => '0.00',
        'recordsFiltered'     => '9',
        'recordsTotal'        => '9',
        'total'               => '12,847.00',
        'totalIn'             => '12,847.00',
        'totalOut'            => '0.00',
        'data'                => [],
    ];

    private const VALUES_AFTER_EDIT_UP_TO_200 = [
        'draw'                => '',
        'noConfirmedTotalIn'  => '200.00',
        'noConfirmedTotalOut' => '0.00',
        'recordsFiltered'     => '1',
        'recordsTotal'        => '1',
        'total'               => '200.00',
        'totalIn'             => '200.00',
        'totalOut'            => '0.00',
        'data'                => [],
    ];

    private const VALUES_DEFAULT_DATA_FOR_FILTER_DELETE_AND_DOCUMENT_DATE = [
        'draw'                => '',
        'noConfirmedTotalIn'  => '0.00',
        'noConfirmedTotalOut' => '0.00',
        'recordsFiltered'     => '0',
        'recordsTotal'        => '0',
        'total'               => '0.00',
        'totalIn'             => '0.00',
        'totalOut'            => '0.00',
        'data'                => [],
    ];

    /**
     * @var array
     */
    private $valueJson = [];

    /**
     * @var string
     */
    private static $idCashDocument;

    /**
     * @var \Symfony\Component\Translation\TranslatorInterface
     */
    private $translator;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::baseFixtures();
    }

    public static function tearDownAfterClass()
    {
        self::clearDB();
    }

    public function setUp()
    {
        parent::setUp();
        $this->setDefaultValue();
    }

    public function testStatusCodeUrlBase()
    {
        $this->getListCrawler(self::URL_BASE);

        self::assertStatusCode(
            200,
            $this->client
        );
    }

    public function testStatusCodeUrlJson()
    {
        $this->getListCrawler(self::URL_JSON);
        json_decode($this->client->getResponse()->getContent(), true);

        self::assertStatusCode(
            200,
            $this->client
        );

        $this->assertEquals(JSON_ERROR_NONE, json_last_error());
    }

    public function getUrlWithInvalidCashDocumentId(): array
    {
        return [
            [
                [
                    'url'    => self::URL_POSTFIX_PAY,
                    'status' => 200,
                ],
            ],
            [
                [
                    'url'    => self::URL_POSTFIX_CONFIRM,
                    'status' => 200,
                ],
            ],
            [
                [
                    'url'    => self::URL_POSTFIX_EDIT,
                    'status' => 404,
                ],
            ],
            [
                [
                    'url'    => self::URL_POSTFIX_CARD_MONEY,
                    'status' => 404,
                ],
            ],
            [
                [
                    'url'    => self::URL_POSTFIX_DELETE,
                    'status' => 404,
                ],
            ],
        ];
    }

    /**
     * @dataProvider getUrlWithInvalidCashDocumentId
     */
    public function testInvalidPayCashDocument(array $data)
    {
        $this->getListCrawler('/cash/invalid_id_info_100pr/' . $data['url']);

        self::assertStatusCode(
            $data['status'],
            $this->client
        );

        $errMsg = 'Err in the url: ' . $data['url'];
        switch ($data['status']) {
            case 200:
                $response = json_decode($this->client->getResponse()->getContent(), true);
                $this->assertEquals(
                    ['error' => true, 'message' => "CashDocument not found"],
                    $response,
                    $errMsg
                );
                break;
            case 404:
                $e = $this->client->getContainer()->get('liip_functional_test.exception_listener')->getLastException();
                $this->assertEquals(
                    'MBHCashBundle:CashDocument object not found.',
                    $e->getMessage(),
                    $errMsg
                );
                break;
        }
    }

    public function testDefaultAmountItemInDB()
    {
        $this->checkAmountItemInCollections(self::DB_DEFAULT_AMOUNT_RECORDS);
    }

    public function getDefaultData(): array
    {
        return [
            [
                [
                    'filter' => [],
                    'data'   => self::VALUES_DEFAULT,
                ],
            ],
            [
                [
                    'filter' => [self::QUERY_FILTER_PAID_DATE],
                    'data'   => self::VALUES_DEFAULT,
                ],
            ],
            [
                [
                    'filter' => [self::QUERY_FILTER_PAID_DATE, self::QUERY_FILTER_BY_DAY],
                    'data'   => self::VALUES_DEFAULT,
                    'msg'    => 'using filter by_day'
                ],
            ],
            [
                [
                    'filter' => [self::QUERY_FILTER_DELETED_AT],
                    'data'   => self::VALUES_DEFAULT_DATA_FOR_FILTER_DELETE_AND_DOCUMENT_DATE,
                ],
            ],
            [
                [
                    'filter' => [self::QUERY_FILTER_DOCUMENT_DATE],
                    'data'   => self::VALUES_DEFAULT_DATA_FOR_FILTER_DELETE_AND_DOCUMENT_DATE,
                ],
            ],
        ];
    }

    /**
     * @dataProvider getDefaultData
     * @param array $data
     */
    public function testDefaultValueFromJsonWithFilter(array $data): void
    {
        $this->filterTest($data);
    }

    /**
     * убрал т.к. в проверка на заполняемось установлена только в форме на фронтэнде.
     * ставить проверку в классе коллекции. пока не решился.
     */
//    public function testInvalidDataForAddNew()
//    {
//        $form = $this->getListCrawler(self::URL_ADD_NEW)->filter('button[name="save_close"]')->form();
//
//        $this->client->submit($form);
//
//        $this->assertValidationErrors(['data.operation', 'data.total'], $this->client->getContainer());
//    }

    /**
     * добавляем новый платеж, без оплаты, без подтверждения
     * (а так же записываем id нового документа)
     */
    public function testAddNew()
    {
        $form = $this->getListCrawler(self::URL_ADD_NEW)->filter('button[name="save_close"]')->form();

        $form->setValues([
            'mbh_bundle_cash_cash_document[operation]' => \MBH\Bundle\CashBundle\Document\CashDocument::OPERATION_IN,
            'mbh_bundle_cash_cash_document[total]'     => 100,
            'mbh_bundle_cash_cash_document[isPaid]'    => false,
        ]);

        $this->client->submit($form);

        $this->setIdFromNewCashDocument();

        $this->checkAmountItemInCollections(self::DB_DEFAULT_AMOUNT_RECORDS + 1);
    }

    public function getDataAfterAddNew(): array
    {
        $data = [
            'draw'                => '',
            'noConfirmedTotalIn'  => '100.00',
            'noConfirmedTotalOut' => '0.00',
            'recordsFiltered'     => '1',
            'recordsTotal'        => '1',
            'total'               => '100.00',
            'totalIn'             => '100.00',
            'totalOut'            => '0.00',
            'data'                => [],
        ];

        return [
            [
                [
                    'filter' => [],
                    'data'   => self::VALUES_DEFAULT,
                ],
            ],
            [
                [
                    'filter' => [self::QUERY_FILTER_PAID_DATE],
                    'data'   => self::VALUES_DEFAULT,
                ],
            ],
            [
                [
                    'filter' => [self::QUERY_FILTER_DELETED_AT],
                    'data'   => self::VALUES_DEFAULT_DATA_FOR_FILTER_DELETE_AND_DOCUMENT_DATE,
                ],
            ],
            [
                [
                    'filter' => [self::QUERY_FILTER_DOCUMENT_DATE],
                    'data'   => self::VALUES_DEFAULT_DATA_FOR_FILTER_DELETE_AND_DOCUMENT_DATE,
                ],
            ],
            [
                [
                    'filter' => [self::QUERY_FILTER_DOCUMENT_DATE, self::QUERY_FILTER_SHOW_NO_PAID_1],
                    'data'   => $data,
                ],
            ],
        ];
    }

    /**
     * @param $data
     * @depends      testAddNew
     * @dataProvider getDataAfterAddNew
     */
    public function testFilterAfterAddNew($data)
    {
        $this->filterTest($data);
    }

    /**
     * @depends testAddNew
     */
    public function testEditCashDocument()
    {
        $form = $this->getListCrawler($this->getLinkForAction(self::URL_POSTFIX_EDIT))
            ->filter('button[name="save_close"]')->form();

        $form->setValues([
            'mbh_bundle_cash_cash_document[total]' => 200,
        ]);

        $this->client->submit($form);

        $data = [
            [
                'data'   => self::VALUES_AFTER_EDIT_UP_TO_200,
                'filter' => [self::QUERY_FILTER_DOCUMENT_DATE, self::QUERY_FILTER_SHOW_NO_PAID_1],
            ],
            [
                'data'   => self::VALUES_DEFAULT_DATA_FOR_FILTER_DELETE_AND_DOCUMENT_DATE,
                'filter' => [self::QUERY_FILTER_DOCUMENT_DATE],
            ],
            [
                'data'   => self::VALUES_DEFAULT,
                'filter' => [],
                'msg'    => 'using without filters',
            ],
        ];

        $this->filterGroupTest($data);
    }

    /**
     * @depends testEditCashDocument
     */
    public function testPayCashDocument()
    {
        $this->getListCrawler($this->getLinkForAction(self::URL_POSTFIX_PAY));

        $dataFilter = [
            'draw'                => '',
            'noConfirmedTotalIn'  => '1,560.00',
            'noConfirmedTotalOut' => '0.00',
            'recordsFiltered'     => '4',
            'recordsTotal'        => '4',
            'total'               => '12,847.00',
            'totalIn'             => '12,847.00',
            'totalOut'            => '0.00',
            'data'                => [],
        ];

        $data = [
            [
                'data'   => self::VALUES_AFTER_EDIT_UP_TO_200,
                'filter' => [self::QUERY_FILTER_DOCUMENT_DATE, self::QUERY_FILTER_SHOW_NO_PAID_1],
            ],
            [
                'data'   => self::VALUES_AFTER_EDIT_UP_TO_200,
                'filter' => [self::QUERY_FILTER_DOCUMENT_DATE],
            ],
            [
                'data'   => self::VALUES_COMMON_WITH_200_NOT_CONFIRMED,
                'filter' => [],
                'msg'    => 'using without filters',
            ],
            [
                'data'   => $dataFilter,
                'filter' => [self::QUERY_FILTER_SHOW_NO_CONFIRMED_1],
                'msg'    => 'using filter no confirmed',
            ],
        ];

        $this->filterGroupTest($data);
    }

    /**
     * @depends testPayCashDocument
     */
    public function testConfirmCashDocument()
    {
        $this->getListCrawler($this->getLinkForAction(self::URL_POSTFIX_CONFIRM));

        $data = [
            [
                'data'   => self::VALUES_COMMON_WITH_200_CONFIRMED,
                'filter' => [],
                'msg'    => 'using without filters',
            ],
        ];

        $this->filterGroupTest($data);
    }

    /**
     * @depends testConfirmCashDocument
     */
    public function testDeleteCashDocument()
    {
        $this->getListCrawler($this->getLinkForAction(self::URL_POSTFIX_DELETE));

        $this->checkAmountItemInCollections(self::DB_DEFAULT_AMOUNT_RECORDS + 1);
    }

    public function getDataAfterDelete(): array
    {
        $dataOnlyDelete = [
            'draw'                => '',
            'noConfirmedTotalIn'  => '0.00',
            'noConfirmedTotalOut' => '0.00',
            'recordsFiltered'     => '1',
            'recordsTotal'        => '1',
            'total'               => '200.00',
            'totalIn'             => '200.00',
            'totalOut'            => '0.00',
            'data'                => [],
        ];

        return [
            [
                [
                    'filter' => [],
                    'data'   => self::VALUES_DEFAULT,
                    'msg'    => 'using without filters',
                ],
            ],
            [
                [
                    'filter' => [self::QUERY_FILTER_DELETED_AT, self::QUERY_FILTER_DELETED_COMMON],
                    'data'   => $dataOnlyDelete,
                    'msg'    => 'using filter show only delete',
                ],
            ],
            [
                [
                    'filter' => [self::QUERY_FILTER_DELETED_COMMON],
                    'data'   => self::VALUES_COMMON_WITH_200_CONFIRMED,
                    'msg'    => 'using filter show delete',
                ],
            ],
        ];
    }

    /**
     * @depends      testDeleteCashDocument
     * @dataProvider getDataAfterDelete
     */
    public function testFiltersAfterDelete($data)
    {
        $this->filterTest($data);
    }

    public function testButtonExportOneCNotAvailable()
    {
        $crawler = $this->getListCrawler(self::URL_BASE);

        $this->assertCount(0,$crawler->filter('a[href="' . self::URL_EXPORT_ONE_C . '"]'));
    }

    public function testButtonExportOneCAvailable()
    {
        $this->setOrganizationForHotel();

        $crawler = $this->getListCrawler(self::URL_BASE);

        $this->assertCount(1,$crawler->filter('a[href="' . self::URL_EXPORT_ONE_C . '"]'));

    }

    /**
     * @depends testButtonExportOneCAvailable
     */
    public function testStatusCodeReportOneC()
    {
        $this->getListCrawler(self::URL_EXPORT_ONE_C);
        self::assertStatusCode(
            200,
            $this->client
        );
    }

    /**
     * @depends testStatusCodeReportOneC
     */
    public function testExportOneC()
    {
        $this->getListCrawler(self::URL_EXPORT_ONE_C);

        $content = explode("\r\n", mb_convert_encoding($this->client->getResponse()->getContent(), 'utf-8', 'windows-1251'));

        $resultWithoutTime = array_filter($content, function ($value) {
            return strpos($value,'ВремяСоздания') !== 0;
        });

        self::assertStatusCode(
            200,
            $this->client
        );

        $this->assertEquals([],array_diff($this->getContetntReportOneC(),$resultWithoutTime));
    }

    private function getContetntReportOneC()
    {
        $this->translator = $this->getContainer()->get('translator');
        $body = '';

        $dateBegin = (new DateTime('-7 days'))->format('d.m.Y');
        $dateCreate = $dateEnd = (new DateTime())->format('d.m.Y');

        $format[] = '1CClientBankExchange';
        $format[] = $this->trans('versiyaformata') . '=1.02';
        $format[] = $this->trans('kodirovka') .'=Windows';
        $format[] = $this->trans('otpravitel') . '=';
        $format[] = $this->trans('poluchatel') . '=';
        $format[] = $this->trans('datasozdaniya') . '=' . $dateCreate;
        $format[] = $this->trans('datanachala') . '=' . $dateBegin;
        $format[] = $this->trans('datakontsa') . '=' . $dateEnd;
        $format[] = $this->trans('raschschet') . '=' . self::ORG_TEST_CHECKING_ACCOUNT;
        $format[] = $this->trans('sektsiyaraschschet');
        $format[] = $this->trans('datanachala') . '=' . $dateBegin;
        $format[] = $this->trans('datakontsa') . '=' . $dateEnd;
        $format[] = $this->trans('nachalnyyostatok') . '=0';
        $format[] = $this->trans('raschschet') . '=' . self::ORG_TEST_CHECKING_ACCOUNT;
        $format[] = $this->trans('vsegospisano') . '=0';
        $format[] = $this->trans('vsegopostupilo') . '=' . '0.00';
        $format[] = $this->trans('konechnyyostatok') . '=0';
        $format[] = $this->trans('konetsraschschet');
        $format[] = $body . $this->trans('konetsfayla');

        return $format;
    }

    private function trans(string $name, bool $prefix = true): string
    {
        $id = $prefix ? 'mbhcashbundle.service.onecexporter.' . $name : $name;


        return $this->translator->trans($id);
    }

    private function setOrganizationForHotel(): void
    {
        $dm = $this->getContainer()->get('doctrine.odm.mongodb.document_manager');

        $org = new \MBH\Bundle\PackageBundle\Document\Organization();
        $org->setName('TestOrg');
        $org->setInn('1231313131');
        $org->setCheckingAccount(self::ORG_TEST_CHECKING_ACCOUNT);

        $hotels = $dm
            ->getRepository('MBHHotelBundle:Hotel')
            ->findAll();

        foreach ($hotels as $hotel) {
            $org->addHotel($hotel);
        }

        $dm->persist($org);
        $dm->flush();
    }

    private function getLinkForAction(string $action): string
    {
        return '/cash/' . $this->getCashDocumentId() . '/' . $action;
    }

    private function setIdFromNewCashDocument(): void
    {
        /** @var \Doctrine\ODM\MongoDB\Query\Builder $b */
        $b = $this->getContainer()->get('doctrine.odm.mongodb.document_manager')
            ->getRepository('MBHCashBundle:CashDocument')
            ->createQueryBuilder();
        $cash = $b->find()
            ->select('_id')
            ->sort('_id', '-1')
            ->limit(1)
            ->getQuery()
            ->getSingleResult();


        self::$idCashDocument = $cash->getId();
    }

    private function checkAmountItemInCollections(int $amount)
    {
        $dm = $this->getContainer()->get('doctrine.odm.mongodb.document_manager');

        $dm->getFilterCollection()->disable('softdeleteable');
        $amountInDB = count($dm
            ->getRepository('MBHCashBundle:CashDocument')
            ->findAll()
        );

        $this->assertEquals($amount, $amountInDB);
    }

    private function setDefaultValue(): void
    {
        $this->valueJson = self::VALUES_DEFAULT;
    }

    private function changeValueJson(array $newData): array
    {
        return $this->valueJson = $newData;
    }

    private function getDataFromUrlJson(array $filters = []): array
    {
        $queryStr = $filters !== [] ? '?' . implode('&', $filters) : '';

        $this->getListCrawler(self::URL_JSON . $queryStr);

        return json_decode($this->client->getResponse()->getContent(), true);
    }

    private function getValuesJson(): array
    {
        return $this->valueJson;
    }

    private function compareWithValueJson(array $compareData, string $msg = ''): void
    {
        if (!is_array($compareData['data'])) {
            $this->assertThat($compareData['data'], self::isType('array'));
        }
        $compareData['data'] = [];
        $this->assertEquals($this->getValuesJson(), $compareData, $msg);
    }

    private function getCashDocumentId(): string
    {
        return self::$idCashDocument;
    }

    private function filterTest(array $data): void
    {
        $this->changeValueJson($data['data']);
        $this->compareWithValueJson($this->getDataFromUrlJson($data['filter']), $data['msg'] ?? '');
    }

    private function filterGroupTest(array $data): void
    {
        foreach ($data as $d) {
            $this->filterTest($d);
        }
    }
}