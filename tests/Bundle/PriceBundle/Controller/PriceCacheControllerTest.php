<?php
/**
 * Created by PhpStorm.
 * User: mb3
 * Date: 10.04.18
 * Time: 12:48
 */

namespace Tests\Bundle\PriceBundle\Controller;


use MBH\Bundle\BaseBundle\Lib\Test\Traits\HotelIdTestTrait;
use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Document\PriceCache;
use MBH\Bundle\PriceBundle\Document\Restriction;
use MBH\Bundle\PriceBundle\Document\Tariff;
use Symfony\Component\DomCrawler\Crawler;

class PriceCacheControllerTest extends WebTestCase
{
    use HotelIdTestTrait;

    private const BASE_URL = '/price/price_cache/';
    private const SPECIAL_TARIFFS = 'Special tariff';

    private const FORM_NAME_NEW_RESTRICTION = 'newPriceCaches';
    private const FORM_NAME_UPDATE_PRICE_CACHE = 'updatePriceCaches';

    private const FORM_NAME_GENERATION = 'mbh_price_bundle_price_cache_generator';

    private const TRIPLE_ROOM = 3;
    private const TWIN_ROOM = 2;

    private const SUNDAY = 0;
    private const TUESDAY = 2;
    private const THURSDAY = 4;


    /**
     * @var RoomType[]
     */
    private $roomTypeCache;

    /**
     * @var Tariff[]
     */
    private $tariffs;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::baseFixtures();
    }

    public static function tearDownAfterClass()
    {
        self::clearDB();
    }

    public function testStatusCode()
    {
        $this->getListCrawler(self::BASE_URL);

        $this->assertStatusCode(
            200,
            $this->client
        );
    }

    public function testStatusCodeTable()
    {
        $this->getTable();

        $this->assertStatusCode(
            200,
            $this->client
        );
    }

    public function testDefaultTable()
    {
        $this->assertEquals(
            [
                '2200', '1500', '1000',
                '1500', '1000', '800',
                '1200', '900', '700',
                '1200', '1000', '700',
                '1000', '800', '500',
                '1200', '900', '700',
            ],
            $this->getResultFromTable()
        );
    }

    public function testIsDisabled()
    {
        $dm = $this->getDocumentManager();

        $this->getContainer()->get('mbh.client_config_manager')->changeDisableableMode(true);

        /** @var RoomType $roomType */
        $roomType = $dm->getRepository('MBHHotelBundle:RoomType')
            ->find($this->getRoomType(self::TRIPLE_ROOM))
            ->setIsEnabled(false);

        $dm->flush();

        $this->assertEquals(
            [
                '1500', '1000', '800',
                '1200', '900', '700',
                '1000', '800', '500',
                '1200', '900', '700',
            ],
            $this->getResultFromTable()
        );

        $this->getContainer()->get('mbh.client_config_manager')->changeDisableableMode(false);
        $roomType->setIsEnabled(true);

        $this->assertEquals(
            [
                '2200', '1500', '1000',
                '1500', '1000', '800',
                '1200', '900', '700',
                '1200', '1000', '700',
                '1000', '800', '500',
                '1200', '900', '700',
            ],
            $this->getResultFromTable()
        );
    }

    public function testPriceAdd()
    {
        $amountRestriction = $this->getAmountPrice();

        $date = new \DateTime('noon yesterday');

        $data = $this->getRandomDataForForm();

        $this->client->request(
            'POST',
            self::BASE_URL . 'save',
            [
                self::FORM_NAME_NEW_RESTRICTION => [
                    $this->getRoomType(self::TWIN_ROOM) => [
                        $this->getIdSpecialTariff() => [
                            $date->format('d.m.Y') => $data,
                        ],
                    ],
                ],
            ]
        );

        $this->assertEquals([], $this->getResultFromTable($date, self::TRIPLE_ROOM));

        $this->assertEquals(array_values($data), $this->getResultFromTable($date, self::TWIN_ROOM));

        $this->assertCount($amountRestriction + 1, $this->getPriceFromRepo());
    }

    /**
     * @depends testPriceAdd
     */
    public function testPriceChange()
    {
        $amountPrice = $this->getAmountPrice();

        $date = new \DateTime('noon yesterday');

        /** @var PriceCache $price */
        $price = $this->getPrice(self::TWIN_ROOM, $this->getIdSpecialTariff(), $date);

        $data = $this->getRandomDataForForm();

        $this->client->request(
            'POST',
            self::BASE_URL . 'save',
            [
                self::FORM_NAME_UPDATE_PRICE_CACHE => [
                    $price->getId() => $data,
                ],
            ]
        );

        $this->assertEquals([], $this->getResultFromTable($date, self::TRIPLE_ROOM));

        $this->assertEquals(array_values($data), $this->getResultFromTable($date, self::TWIN_ROOM));

        $this->assertCount($amountPrice + 1, $this->getPriceFromRepo());
    }

    /**
     * @depends testPriceChange
     */
    public function testPriceClearSingle()
    {
        $amountPrice = $this->getAmountPrice();

        $date = new \DateTime('noon yesterday');

        /** @var PriceCache $price */
        $price = $this->getPrice(self::TWIN_ROOM, $this->getIdSpecialTariff(), $date);

        $this->client->request(
            'POST',
            self::BASE_URL . 'save',
            [
                self::FORM_NAME_UPDATE_PRICE_CACHE => [
                    $price->getId() => [
                        'price' => ''
                    ],
                ],
            ]
        );

        $this->assertEquals([], $this->getResultFromTable($date, self::TRIPLE_ROOM));

        $this->assertEquals([], $this->getResultFromTable($date, self::TWIN_ROOM));

        $this->assertCount($amountPrice, $this->getPriceFromRepo());
    }

    public function testInvalidDateInGeneration()
    {
        $form = $this->getGenerationFormWithValues(
            $this->getRandomDataForForm(),
            null,
            [],
            new \DateTime(),
            new \DateTime('-10 day')
        );

        $this->client->submit($form);

        $this->assertValidationErrors(['data'], $this->client->getContainer());
    }

    public function testInvalidPriceGeneration()
    {
        $data = $this->getRandomDataForForm();
        unset($data['price']);

        $form = $this->getGenerationFormWithValues($data);

        $this->client->submit($form);

        $this->assertValidationErrors(['children[price].data'], $this->client->getContainer());
    }

    public function testGenerate()
    {
        $amountPrice = $this->getAmountPrice();

        $data = $this->getRandomDataForForm(false);

        $form = $this->getGenerationFormWithValues(
            $data,
            self::TRIPLE_ROOM,
            [],
            new \DateTime('noon -3 days'),
            new \DateTime('noon +2 days'),
            [$this->getIdSpecialTariff()]
        );

        $this->client->submit($form);

        $this->assertEquals(
            array_values($data),
            $this->getResultFromTable(null, self::TRIPLE_ROOM, [$this->getIdSpecialTariff()], false)
        );

        $this->assertEquals(
            [],
            $this->getResultFromTable(new \DateTime('noon yesterday'), self::TWIN_ROOM, [$this->getIdSpecialTariff()])
        );

        $this->assertCount($amountPrice + 6, $this->getPriceFromRepo());
    }

    public function testCleareWithGenerator()
    {
        $amountRestriction = $this->getAmountPrice();

        $form = $this->getGenerationFormWithValues(
            ['price' => -1],
            self::TRIPLE_ROOM,
            [],
            new \DateTime('noon -3 days'),
            new \DateTime('noon +2 days'),
            [$this->getIdSpecialTariff()]
        );

        $this->client->submit($form);

        $this->assertEquals(
            [],
            $this->getResultFromTable(null, self::TRIPLE_ROOM, [$this->getIdSpecialTariff()])
        );

        $this->assertEquals(
            [],
            $this->getResultFromTable(new \DateTime('noon -3 days'), self::TWIN_ROOM, [$this->getIdSpecialTariff()])
        );

        $this->assertCount($amountRestriction, $this->getPriceFromRepo());
    }

    /**
     * @param array $data
     * @param null|int $places
     * @param array $weekdays
     * @param \DateTime|null $dateBegin
     * @param \DateTime|null $dateEnd
     * @param array $tariffs
     * @return \Symfony\Component\DomCrawler\Form
     */
    private function getGenerationFormWithValues(
        array $data,
        int $places = null,
        array $weekdays = [],
        \DateTime $dateBegin = null,
        \DateTime $dateEnd = null,
        array $tariffs = []
    ): \Symfony\Component\DomCrawler\Form
    {
        if ($weekdays === []) {
            $weekdays = range(self::SUNDAY, 6);
        }

        if ($dateBegin === null) {
            $dateBegin = new \DateTime('noon -2 day');
        }

        if ($dateEnd === null) {
            $dateEnd = new \DateTime('noon +21 day');
        }


        $form = $this->getGenerationForm();

        $setting = [
            self::FORM_NAME_GENERATION . '[begin]'     => $dateBegin->format('d.m.Y'),
            self::FORM_NAME_GENERATION . '[end]'       => $dateEnd->format('d.m.Y'),
            self::FORM_NAME_GENERATION . '[weekdays]'  => $weekdays,
            self::FORM_NAME_GENERATION . '[roomTypes]' => $this->getRoomType($places),
            self::FORM_NAME_GENERATION . '[tariffs]'   => $tariffs,
        ];

        $values = [];

        foreach ($data as $name => $val) {
            $values[self::FORM_NAME_GENERATION . '[' . $name . ']'] = $val;
        }

        $form->setValues(array_merge($setting, $values));
        return $form;
    }

    /**
     * @return \Symfony\Component\DomCrawler\Form
     */
    private function getGenerationForm(): \Symfony\Component\DomCrawler\Form
    {
        $crawler = $this->getListCrawler(self::BASE_URL . 'generator');

        return $crawler->filter('button[name="save_close"]')->form();
    }

    /**
     * @param int $places
     * @param string $tariff
     * @param \DateTime|null $date
     * @return PriceCache
     */
    private function getPrice(int $places, string $tariff, \DateTime $date = null): PriceCache
    {
        $date = $date === null ? new \DateTime('noon') : $date;

        $dm = $this->getContainer()->get('doctrine.odm.mongodb.document_manager');

        return $dm->getRepository('MBHPriceBundle:PriceCache')->findOneBy(
            [
                'hotel.id'    => $this->getHotelId(),
                'tariff.id'   => $tariff,
                'roomType.id' => $this->getRoomType($places),
                'date'        => $date,
                'isEnabled'   => true,
            ]
        );
    }

    /**
     * @param bool $without
     * @return array
     */
    private function getRandomDataForForm(bool $without = true): array
    {
        return $this->getDataForForm(
            function () {
                return mt_rand(1, 200);
            },
            $without
        );
    }

    /**
     * @param $value
     * @param bool $validGuest
     * @return array
     */
    private function getDataForForm($value, bool $without): array
    {
        $data = [
            'price'                   => 0,
            'isPersonPrice'           => 1,
            'singlePrice'             => 0,
            'additionalPrice'         => 0,
            'additionalChildrenPrice' => 0,
        ];

        foreach ($data as &$d) {
            $d = $value instanceof \Closure ? $value() : $value;
        }
        unset($d);

        if (!$without) {
            $data['isPersonPrice'] = 1;
        } else {
            unset($data['isPersonPrice']);
        }
        return $data;
    }

    /**
     * @return string
     */
    private function getIdSpecialTariff(): string
    {
        return $this->getTariffs()[self::SPECIAL_TARIFFS];
    }

    /**
     * @return array
     */
    private function getTariffs(): array
    {
        if (empty($this->tariffs)) {
            $dm = $this->getDocumentManager();
            $tariffs = $dm->getRepository('MBHPriceBundle:Tariff')
                ->findBy(['hotel.id' => $this->getHotelId()]);
            foreach ($tariffs as $tariff) {
                $this->tariffs[$tariff->getFullTitle()] = $tariff->getId();
            }
        }
        return $this->tariffs;
    }

    /**
     * @param \DateTime|null $date
     * @param null|int $place
     * @param array $tariffs
     * @return array
     */
    private function getResultFromTable(\DateTime $date = null, int $places = null, array $tariffs = [], bool $without = true): array
    {
        $date = $date !== null ? $date : new \DateTime();

        $selector = '';

        if ($places !== null) {
            $selector .= 'tr[data-copy-row-id="' . $this->getRoomType($places) . '"] ';
        }

        $selector .= 'td[data-id$="_' . $date->format('d.m.Y') . '"] input';

        if ($without) {
            $selector .= ':not(.isPersonPrice)';
        }

        $table = $this->getTable(null, null, $tariffs);

        $td = $table->filter($selector);

        return $this->getResultFromIterator($td);
    }

    /**
     * @param Crawler $td
     * @return array
     */
    private function getResultFromIterator(Crawler $td): array
    {
        $result = [];
        foreach ($td->getIterator() as $element) {
            $value = trim($element->attributes->getNamedItem('value')->textContent);
            if ($value != '') {
                $result[] = $value;
            }
        }

        return $result;
    }

    /**
     * @param \DateTime|null $begin
     * @param \DateTime|null $end
     * @param array $tariffs
     * @return Crawler
     */
    private function getTable(\DateTime $begin = null, \DateTime $end = null, array $tariffs = []): Crawler
    {
        if ($begin === null) {
            $begin = new \DateTime('noon -4 day');
        }
        if ($end === null) {
            $end = new \DateTime('noon +25 day');
        }

        $url = self::BASE_URL . 'table?';
        $url .= 'begin=' . $begin->format('d.m.Y');
        $url .= '&end=' . $end->format('d.m.Y');
        $url .= '&roomTypes=';

        if ($tariffs !== []) {
            foreach ($tariffs as $tariff) {
                $url .= '&tariffs[]=' . $tariff;
            }
        }

        return $this->getListCrawler($url);
    }

    /**
     * @return \Doctrine\ODM\MongoDB\DocumentManager
     */
    private function getDocumentManager(): \Doctrine\ODM\MongoDB\DocumentManager
    {
        return $this->getContainer()->get('doctrine.odm.mongodb.document_manager');
    }

    /**
     * @param int|null $places
     * @param bool $returnObject
     * @return array|RoomType|string
     */
    private function getRoomType(int $places = null, $returnObject = false)
    {
        if ($places !== null && !in_array($places, [self::TWIN_ROOM, self::TRIPLE_ROOM], true)) {
            throw new \LogicException('Needed true roomType');
        }

        $typeRooms = [];

        /** @var RoomType $typeRoom */
        foreach ($this->getRoomTypeCache() as $typeRoom) {
            if ($places === null) {
                if ($returnObject) {
                    $typeRooms[] = $typeRoom;
                } else {
                    $typeRooms[] = $typeRoom->getId();
                }
            } else {
                if ($typeRoom->getPlaces() == $places) {
                    if ($returnObject) {
                        $typeRooms = $typeRoom;
                    } else {
                        $typeRooms = $typeRoom->getId();
                    }
                }
            }
        }

        return $typeRooms;
    }

    /**
     * @return array
     */
    private function getRoomTypeCache(): array
    {
        if (empty($this->roomTypeCache)) {
            $dm = $this->getDocumentManager();
            $this->roomTypeCache = $dm
                ->getRepository('MBHHotelBundle:RoomType')
                ->findBy(['hotel.id' => $this->getHotelId()]);
        }
        return $this->roomTypeCache;
    }

    /**
     * @return Restriction[]
     */
    private function getPriceFromRepo(): array
    {
        $dm = $this->getContainer()->get('doctrine.odm.mongodb.document_manager');

        return $dm->getRepository('MBHPriceBundle:PriceCache')->findBy(
            [
                'hotel.id' => $this->getHotelId(),
            ]
        );
    }

    /**
     * @return int
     */
    private function getAmountPrice(): int
    {
        return count($this->getPriceFromRepo());
    }
}