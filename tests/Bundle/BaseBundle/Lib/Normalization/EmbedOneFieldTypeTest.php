<?php

namespace Tests\Bundle\BaseBundle\Lib\Normalization;

use MBH\Bundle\BaseBundle\Lib\Normalization\EmbedOneFieldType;
use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;
use MBH\Bundle\BaseBundle\Service\MBHSerializer;
use MBH\Bundle\PackageBundle\Document\PackagePrice;
use MBH\Bundle\PriceBundle\Document\Tariff;

class EmbedOneFieldTypeTest extends WebTestCase
{
    public static function setUpBeforeClass()
    {
        self::baseFixtures();
    }

    public static function tearDownAfterClass()
    {
        self::clearDB();
    }

    public function testNormalize()
    {
        $fieldType = new EmbedOneFieldType(PackagePrice::class);
        $tariff = $this->getDm()->getRepository('MBHPriceBundle:Tariff')->findOneBy([]);
        $packagePrice = new PackagePrice(new \DateTime('midnight'), 123, $tariff);

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
        $this->assertEquals([
            'date' => (new \DateTime())->format('d.m.Y'),
            'price' => 123,
            'tariff' => $tariff->getId(),
            'promotion' => null,
            'special' => null
        ], $fieldType->normalize($packagePrice, ['serializer' => $serializerMock]));
    }

    /**
     * @throws \MBH\Bundle\BaseBundle\Lib\Normalization\NormalizationException
     * @throws \ReflectionException
     */
    public function testDenormalize()
    {
        $tariff = $this->getDm()->getRepository(Tariff::class)->findOneBy([]);
        $normalizedData = [
            'date' => (new \DateTime('midnight'))->format('d.m.Y'),
            'price' => 123.12,
            'tariff' => $tariff->getId()
        ];
        $fieldType = new EmbedOneFieldType(PackagePrice::class);
        $expected = new PackagePrice(new \DateTime('midnight'), 123.12, $tariff);
        $denormalized = $fieldType->denormalize($normalizedData, ['serializer' => $this->getContainer()->get('mbh.serializer')]);
        $this->assertEquals($expected, $denormalized);
    }
}