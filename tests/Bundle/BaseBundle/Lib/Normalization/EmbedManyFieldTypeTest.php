<?php

namespace Tests\Bundle\BaseBundle\Lib\Normalization;

use MBH\Bundle\BaseBundle\Lib\Normalization\EmbedManyFieldType;
use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;
use MBH\Bundle\BaseBundle\Service\MBHSerializer;
use MBH\Bundle\PackageBundle\Document\PackagePrice;
use MBH\Bundle\PriceBundle\Document\Tariff;

class EmbedManyFieldTypeTest extends WebTestCase
{
    public static function setUpBeforeClass()
    {
        self::baseFixtures();
    }

    public static function tearDownAfterClass()
    {
        self::clearDB();
    }

    /**
     * @throws \MBH\Bundle\BaseBundle\Lib\Normalization\NormalizationException
     */
    public function testNormalize()
    {
        $fieldType = new EmbedManyFieldType(PackagePrice::class);
        $tariff = $this->getDm()->getRepository(Tariff::class)->findOneBy([]);
        $begin = new \DateTime('midnight');
        $end = new \DateTime('midnight + 2 days');

        $prices = [];
        $normalizedPrices = [];
        /** @var \DateTime $day */
        foreach (new \DatePeriod($begin, new \DateInterval('P1D'), $end) as $dayNumber => $day) {
            $price = 123 + 10 * $dayNumber;
            $prices[] = (new PackagePrice($day, $price, $tariff));
            $normalizedPrices[] = [
                'date' => $day->format('d.m.Y'),
                'price' => $price,
                'tariff' => $tariff->getId(),
                'promotion' => null,
                'special' => null
            ];
        }

        $serializerMock = $this
            ->getMockBuilder(MBHSerializer::class)
            ->disableOriginalConstructor()
            ->setMethods(['normalize'])
            ->getMock();
        $serializerMock
            ->expects($this->any())
            ->method('normalize')
            ->will($this->returnCallback(function(PackagePrice $packagePrice) {
                return [
                    'date' => $packagePrice->getDate()->format('d.m.Y'),
                    'price' => $packagePrice->getPrice(),
                    'tariff' => $packagePrice->getTariff()->getId(),
                    'promotion' => null,
                    'special' => null
                ];
            }));

        $this->assertEquals($normalizedPrices, $fieldType->normalize($prices, ['serializer' => $serializerMock]));
    }

    public function testDenormalize()
    {
        $fieldType = new EmbedManyFieldType(PackagePrice::class);
        $tariff = $this->getDm()->getRepository(Tariff::class)->findOneBy([]);
        $begin = new \DateTime('midnight');
        $end = new \DateTime('midnight + 2 days');

        $pricesData = [];
        $denormalizedPrices = [];
        /** @var \DateTime $day */
        foreach (new \DatePeriod($begin, new \DateInterval('P1D'), $end) as $dayNumber => $day) {
            $price = 123 + 10 * $dayNumber;
            $denormalizedPrices[] = (new PackagePrice($day, $price, $tariff));
            $pricesData[] = [
                'date' => $day->format('d.m.Y'),
                'price' => $price,
                'tariff' => $tariff->getId(),
                'promotion' => null,
                'special' => null
            ];
        }

        $this->assertEquals($denormalizedPrices, $fieldType->denormalize($pricesData, ['serializer' => $this->getContainer()->get('mbh.serializer')]));
    }
}