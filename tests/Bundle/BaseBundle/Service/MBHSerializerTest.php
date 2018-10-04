<?php

namespace Tests\Bundle\BaseBundle\Service;

use MBH\Bundle\BaseBundle\Lib\Normalization\CustomFieldType;
use MBH\Bundle\BaseBundle\Lib\Normalization\EmbedManyFieldType;
use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;
use MBH\Bundle\BaseBundle\Service\MBHSerializer;
use MBH\Bundle\HotelBundle\Document\Facility;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\Room;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\HotelBundle\Document\RoomViewType;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\PackageAccommodation;
use MBH\Bundle\PackageBundle\Document\PackagePrice;
use MBH\Bundle\PackageBundle\Lib\SearchResult;
use MBH\Bundle\PriceBundle\Document\Tariff;

class MBHSerializerTest extends WebTestCase
{
    /** @var MBHSerializer */
    private $serializer;

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
        parent::setUp();
        $this->serializer = $this->getContainer()->get('mbh.serializer');
    }

    /**
     * @throws \ReflectionException
     * @throws \MBH\Bundle\BaseBundle\Lib\Normalization\NormalizationException
     */
    public function testNormalizeByFields()
    {
        $dm = $this->getDm();

        $package = $dm->getRepository(Package::class)->findOneBy([]);
        $normalizedFields = ['id', 'order', 'roomType', 'numberWithPrefix', 'adults', 'begin', 'price', 'prices', 'isSmoking'];

        $normalizedPackages = $this->serializer->normalizeByFields($package, $normalizedFields);
        $normalizedPackagePrices = array_map(function (PackagePrice $packagePrice) {
            return [
                'date' => $packagePrice->getDate()->format('d.m.Y'),
                'price' => round($packagePrice->getPrice(), 2),
                'tariff' => $packagePrice->getTariff()->getId(),
                'promotion' => $packagePrice->getPromotion() ? $packagePrice->getPromotion()->getId() : null,
                'special' => $packagePrice->getSpecial() ? $packagePrice->getSpecial()->getId() : null
            ];
        }, $package->getPrices()->toArray());

        $this->assertEquals([
            'id' => $package->getId(),
            'order' => $package->getOrder()->getId(),
            'roomType' => $package->getRoomType()->getId(),
            'numberWithPrefix' => $package->getNumberWithPrefix(),
            'adults' => $package->getAdults(),
            'begin' => $package->getBegin()->format('d.m.Y'),
            'price' => $package->getPrice(),
            'prices' => $normalizedPackagePrices,
            'isSmoking' => $package->getIsSmoking()
        ], $normalizedPackages);
    }

    /**
     * @throws \ReflectionException
     * @throws \MBH\Bundle\BaseBundle\Lib\Normalization\NormalizationException
     */
    public function testNormalizeByGroup()
    {
        $package = $this->getDm()->getRepository('MBHPackageBundle:Package')->findOneBy([]);
        $result = $this->serializer->normalizeByGroup($package, MBHSerializer::API_GROUP);

        $expectedFields = ['id', 'numberWithPrefix', 'begin', 'end', 'roomType', 'adults', 'children', 'accommodations'];
        $this->assertEquals($expectedFields, array_keys($result));
    }

    /**
     * @throws \ReflectionException
     * @throws \MBH\Bundle\BaseBundle\Lib\Normalization\NormalizationException
     */
    public function testNormalizeExcludingFields()
    {
        $facility = (new Facility())
            ->setHotel($this->getDm()->getRepository('MBHHotelBundle:Hotel')->findOneBy([]))
            ->setDescription('sdfasf')
            ->setFacilityId('12313');

        $this->getDm()->persist($facility);
        $this->getDm()->flush();
        $this->getDm()->refresh($facility);

        $normalizedFacility = $this->serializer
            ->normalizeExcludingFields($facility, ['description', 'facilityId']);
        $this->assertEquals(['hotel', 'id', 'isEnabled', 'locale'], array_keys($normalizedFacility));
    }

    /**
     * @throws \Exception
     */
    public function testNormalizeObjectBySettings()
    {
        $dm = $this->getDm();

        $begin = new \DateTime('midnight + 10 days');
        $end = new \DateTime('midnight + 12 days');
        $roomType = $dm->getRepository(RoomType::class)->findOneBy([]);
        $tariff = $dm->getRepository(Tariff::class)->findOneBy([]);

        $packagePrices = [];
        $prices = [];
        $denormalizedPackagePrices = [];
        /** @var \DateTime $day */
        foreach (new \DatePeriod($begin, new \DateInterval('P1D'), $end) as $dayNumber => $day) {
            $price = 111 + $dayNumber;
            $prices[$day->format('d_m_Y')] = $price;
            $packagePrices[] = (new PackagePrice($day, $price, $tariff));
            $denormalizedPackagePrices[] = [
                'date' => $day->format('d.m.Y'),
                'price' => $price,
                'tariff' => $tariff->getId(),
                'promotion' => null,
                'special' => null
            ];
        }

        $searchResult = (new SearchResult())
            ->setBegin($begin)
            ->setEnd($end)
            ->setAdults(2)
            ->setRoomType($roomType)
            ->setTariff($tariff)
            ->setPricesByDate($prices, 2, 0)
            ->setUseCategories(false)
            ->setPackagePrices($packagePrices, 2, 0);

        $normalizedResult = $this->serializer
            ->normalizeByFields($searchResult,
                ['begin', 'end', 'adults', 'roomType', 'tariff', 'pricesByDate', 'packagePrices']
            );

        $this->assertEquals([
            'begin' => $begin->format('d.m.Y'),
            'end' => $end->format('d.m.Y'),
            'adults' => 2,
            'roomType' => $roomType->getId(),
            'tariff' => $tariff->getId(),
            'pricesByDate' => ['2_0' => $prices],
            'packagePrices' => ['2_0' => $denormalizedPackagePrices]
        ], $normalizedResult);
    }

    /**
     * @throws \ReflectionException
     * @throws \MBH\Bundle\BaseBundle\Lib\Normalization\NormalizationException
     */
    public function testWithSpecialFieldTypes()
    {
        $package = $this->getDm()
            ->getRepository('MBHPackageBundle:Package')
            ->findOneBy(['price' => 2000]);

        $firstAccEnd = (clone $package->getBegin())->modify('+1 day');
        $firstAccommodation = (new PackageAccommodation())
            ->setBegin($package->getBegin())
            ->setEnd($firstAccEnd)
            ->setAccommodation($this->getDm()->getRepository(Room::class)->findOneBy([]))
            ->setCreatedBy('admin');
        $package->addAccommodation($firstAccommodation);
        $this->getDm()->persist($firstAccommodation);

        $secondAccommodation = (new PackageAccommodation())
            ->setBegin($firstAccEnd)
            ->setEnd($package->getEnd())
            ->setAccommodation($this->getDm()->getRepository(Room::class)->findOneBy([]))
            ->setCreatedBy('admin');
        $this->getDm()->persist($secondAccommodation);
        $package->addAccommodation($secondAccommodation);

        $this->getDm()->flush();
        $this->getDm()->refresh($package);

        $roomTypeNormalizationCallback = function(RoomType $roomType) {
            return [
                'id' => $roomType->getId(),
                'name' => $roomType->getName()
            ];
        };

        $this->serializer->setSpecialFieldTypes(Package::class, [
            'roomType' => new CustomFieldType($roomTypeNormalizationCallback),
            'accommodations' => new EmbedManyFieldType(PackageAccommodation::class)
        ]);

        $normalizedPackage = $this->serializer->normalizeByFields($package, ['roomType', 'accommodations']);
        $this->assertEquals([
            'roomType' => [
                'id' => $package->getRoomType()->getId(),
                'name' => $package->getRoomType()->getName()
            ],
            'accommodations' => [
                [
                    'begin' => $firstAccommodation->getBegin()->format('d.m.Y'),
                    'end' => $firstAccommodation->getEnd()->format('d.m.Y'),
                    'accommodation' => $firstAccommodation->getAccommodation()->getId(),
                    'packageForValidator' => null,
                    'id' => $firstAccommodation->getId(),
                    'isEnabled' => true,
                    'createdAt' => $firstAccommodation->getCreatedAt()->format('d.m.Y'),
                    'updatedAt' => $firstAccommodation->getUpdatedAt()->format('d.m.Y'),
                    'deletedAt' => null,
                    'createdBy' => 'admin',
                    'updatedBy' => null,
                    'note' => '',
                    'isAutomaticallyChangeable' => true,
                ],
                [
                    'begin' => $secondAccommodation->getBegin()->format('d.m.Y'),
                    'end' => $secondAccommodation->getEnd()->format('d.m.Y'),
                    'accommodation' => $secondAccommodation->getAccommodation()->getId(),
                    'packageForValidator' => null,
                    'id' => $secondAccommodation->getId(),
                    'isEnabled' => true,
                    'createdAt' => $secondAccommodation->getCreatedAt()->format('d.m.Y'),
                    'updatedAt' => $secondAccommodation->getUpdatedAt()->format('d.m.Y'),
                    'deletedAt' => null,
                    'createdBy' => 'admin',
                    'updatedBy' => null,
                    'note' => '',
                    'isAutomaticallyChangeable' => true,
                ]
            ]
        ], $normalizedPackage);
    }

    /**
     * @throws \ReflectionException
     * @throws \MBH\Bundle\BaseBundle\Lib\Normalization\NormalizationException
     */
    public function testNormalizeSingleField()
    {
        $hotel = $this->getDm()->getRepository(Hotel::class)->findOneBy([]);
        $roomTypeIds = $this->getContainer()->get('mbh.helper')->toIds($hotel->getRoomTypes());
        $normalizedHotelRoomTypesField = $this->serializer
            ->normalizeSingleField($hotel->getRoomTypes(), new \ReflectionProperty(Hotel::class, 'roomTypes'));
        $this->assertEquals($roomTypeIds, $normalizedHotelRoomTypesField);
    }

    /**
     * @throws \ReflectionException
     * @throws \MBH\Bundle\BaseBundle\Lib\Normalization\NormalizationException
     */
    public function testDenormalize()
    {
        $fullTitle = 'some name';
        $hotel = $this->getDm()->getRepository(Hotel::class)->findOneBy([]);
        $description = 'some description';
        $places = 2;
        $roomViewType = $this->getDm()->getRepository(RoomViewType::class)->findOneBy([]);

        /** @var RoomType $roomType */
        $roomType = $this->serializer->denormalize([
            'fullTitle' => $fullTitle,
            'hotel' => $hotel->getId(),
            'description' => $description,
            'places' => $places,
            'roomViewsTypes' => [$roomViewType->getId()]
        ], new RoomType());

        $this->assertEquals($fullTitle, $roomType->getFullTitle());
        $this->assertEquals($hotel, $roomType->getHotel());
        $this->assertEquals($description, $roomType->getDescription());
        $this->assertEquals($places, $roomType->getPlaces());
        $this->assertEquals([$roomViewType], $roomType->getRoomViewsTypes());
    }
}