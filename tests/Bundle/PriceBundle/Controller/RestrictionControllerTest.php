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
use MBH\Bundle\PriceBundle\Document\Restriction;
use MBH\Bundle\PriceBundle\Document\Tariff;
use Symfony\Component\DomCrawler\Crawler;

class RestrictionControllerTest extends WebTestCase
{
    use HotelIdTestTrait;

    private const BASE_URL = '/price/restriction/';
    private const SPECIAL_TARIFFS = 'Special tariff';

    private const FORM_NAME_NEW_RESTRICTION = 'newRestrictions';
    private const FORM_NAME_UPDATE_RESTRICTION = 'updateRestrictions';

    private const FORM_NAME_GENERATION = 'mbh_bundle_pricebundle_restriction_generator_type';

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
        $this->assertEquals(['2', '3', '6', '5', '3'], $this->getResultFromTable());
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

        $this->assertEquals(['3', '5', '3'], $this->getResultFromTable());

        $this->getContainer()->get('mbh.client_config_manager')->changeDisableableMode(false);
        $roomType->setIsEnabled(true);

        $this->assertEquals(['2', '3', '6', '5', '3'], $this->getResultFromTable());
    }

    public function testRestrictionAdd()
    {
        $amountRestriction = $this->getAmountRestriction();

        $date = new \DateTime('noon yesterday');
//        $restriction = $this->getRestrictions(new \DateTime('yesterday'),self::TRIPLE_ROOM, [$this->getIdSpecialTariff()]);
//        $restrictionTomorrow = $this->getRestrictions(,self::TRIPLE_ROOM, [$this->getIdSpecialTariff()]);

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

        $this->assertCount($amountRestriction + 1, $this->getFromRepoRestrictions());
    }

    /**
     * @depends testRestrictionAdd
     */
    public function testRestrictionChange()
    {
        $amountRestriction = $this->getAmountRestriction();

        $date = new \DateTime('noon yesterday');

        /** @var Restriction $restriction */
        $restriction = $this->getRestriction(self::TWIN_ROOM, $this->getIdSpecialTariff(), $date);

        $data = $this->getRandomDataForForm();

        $this->client->request(
            'POST',
            self::BASE_URL . 'save',
            [
                self::FORM_NAME_UPDATE_RESTRICTION => [
                    $restriction->getId() => $data,
                ],
            ]
        );

        $this->assertEquals([], $this->getResultFromTable($date, self::TRIPLE_ROOM));

        $this->assertEquals(array_values($data), $this->getResultFromTable($date, self::TWIN_ROOM));

        $this->assertCount($amountRestriction, $this->getFromRepoRestrictions());
    }

    /**
     * @depends testRestrictionAdd
     */
    public function testRestrictionClearSingle()
    {
        $amountRestriction = $this->getAmountRestriction();

        $date = new \DateTime('noon yesterday');

        /** @var Restriction $restriction */
        $restriction = $this->getRestriction(self::TWIN_ROOM, $this->getIdSpecialTariff(), $date);

        $data = $this->getEmptyDataForForm();

        $this->client->request(
            'POST',
            self::BASE_URL . 'save',
            [
                self::FORM_NAME_UPDATE_RESTRICTION => [
                    $restriction->getId() => $data,
                ],
            ]
        );

        $this->assertEquals([], $this->getResultFromTable($date, self::TRIPLE_ROOM));

        $this->assertEquals([], $this->getResultFromTable($date, self::TWIN_ROOM));

        $this->assertCount($amountRestriction - 1, $this->getFromRepoRestrictions());
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

    public function testInvalidAmountGuestGeneration()
    {
        $form = $this->getGenerationFormWithValues(
            $this->getRandomDataForForm(true,false)
        );

        $this->client->submit($form);

        $this->assertValidationErrors(['data'], $this->client->getContainer());
    }

    public function testGenerate()
    {
        $amountRestriction = $this->getAmountRestriction();

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

        $this->assertCount($amountRestriction + 3, $this->getFromRepoRestrictions());
    }

    public function testCleareWithGenerator()
    {
        $amountRestriction = $this->getAmountRestriction();

        $data = $this->getEmptyDataForForm();

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
            [],
            $this->getResultFromTable(null, self::TRIPLE_ROOM, [$this->getIdSpecialTariff()])
        );

        $this->assertEquals(
            [],
            $this->getResultFromTable(new \DateTime('noon -3 days'), self::TWIN_ROOM, [$this->getIdSpecialTariff()])
        );

        $this->assertCount($amountRestriction - 6, $this->getFromRepoRestrictions());
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

        foreach ($data as $name => $val){
            $values[self::FORM_NAME_GENERATION . '[' . $name . ']'] = $val;
        }

        $form->setValues(array_merge($setting,$values));
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
     * @return Restriction
     */
    private function getRestriction(int $places, string $tariff, \DateTime $date = null): Restriction
    {
        $date = $date === null ? new \DateTime('noon') : $date;

        $dm = $this->getContainer()->get('doctrine.odm.mongodb.document_manager');

        return $dm->getRepository('MBHPriceBundle:Restriction')->findOneBy(
            [
                'hotel.id' => $this->getHotelId(),
                'tariff.id' => $tariff,
                'roomType.id' => $this->getRoomType($places),
                'date' => $date
            ]
        );
    }

    /**
     * @param bool $without
     * @param bool $validGuest
     * @return array
     */
    private function getRandomDataForForm(bool $without = true, bool $validGuest = true ): array
    {
        return $this->getDataForForm(
            function () {
                return mt_rand(1, 200);
            },
            $without,
            $validGuest
        );
    }

    /**
     * @param bool $without
     * @return array
     */
    private function getEmptyDataForForm($without = true): array
    {
        return $this->getDataForForm('', $without);
    }

    /**
     * @param $value
     * @param bool $without
     * @param bool $validGuest
     * @return array
     */
    private function getDataForForm($value, bool $without, bool $validGuest = true): array
    {
        $data = [
            'minStayArrival' => 0,
            'maxStayArrival' => 0,
            'minStay' => 0,
            'maxStay' => 0,
            'minBeforeArrival' => 0,
            'maxBeforeArrival' => 0,
            'maxGuest' => 0,
            'minGuest' => 0,
        ];

        foreach ($data as &$d) {
            $d = $value instanceof \Closure ? $value() : $value;
        }
        unset($d);

        if ($value != ''){
            if ($validGuest) {
                $data['maxGuest'] = mt_rand(1, 200);
                $data['minGuest'] = mt_rand(1, $data['maxGuest']);
            } else {
                $data['minGuest'] = mt_rand(2, 200);
                $data['maxGuest'] = mt_rand(1, $data['minGuest']-1);
            }
        }

        if (!$without) {
            $data2 = [
                'closedOnDeparture' => 1,
                'closedOnArrival'   => 1,
                'closed'            => 1,
            ];

            $data = array_merge($data, $data2);
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
            $selector .= ':not(.closedOnDeparture):not(.closedOnArrival):not(.closed)';
        }

        $table = $this->getTable(null, null, $tariffs);

//        file_put_contents(time().'.html', $table->html());
//        sleep(1);

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
    private function getFromRepoRestrictions(): array
    {
        $dm = $this->getContainer()->get('doctrine.odm.mongodb.document_manager');

        return $dm->getRepository('MBHPriceBundle:Restriction')->findBy(
            [
                'hotel.id' => $this->getHotelId(),
            ]
        );
    }

    /**
     * @return int
     */
    private function getAmountRestriction(): int
    {
        return count($this->getFromRepoRestrictions());
    }
}