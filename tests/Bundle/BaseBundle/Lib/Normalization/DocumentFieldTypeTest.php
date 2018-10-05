<?php

namespace Tests\Bundle\BaseBundle\Lib\Normalization;

use MBH\Bundle\BaseBundle\Lib\Normalization\DocumentFieldType;
use MBH\Bundle\BaseBundle\Lib\Normalization\NormalizationException;
use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\PackageBundle\Document\Package;

class DocumentFieldTypeTest extends WebTestCase
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
        $package = $this->getDm()->getRepository('MBHPackageBundle:Package')->findOneBy([]);
        $fieldType = new DocumentFieldType(Package::class);
        $this->assertEquals($package->getId(), $fieldType->normalize($package));

        $this->expectException(NormalizationException::class);
        $fieldType->normalize(new \DateTime());
    }

    /**
     * @throws NormalizationException
     */
    public function testDenormalize()
    {
        $hotel = $this->getDm()->getRepository('MBHHotelBundle:Hotel')->findOneBy([]);
        $fieldType = new DocumentFieldType(Hotel::class);
        $this->assertEquals($hotel, $fieldType->denormalize($hotel->getId(), ['dm' => $this->getDm()]));

        $id = '123';
        $this->expectException(NormalizationException::class);
        $this->expectExceptionMessage($id . ' is not a valid ID');
        $fieldType->denormalize($id, ['dm' => $this->getDm()]);
    }
}