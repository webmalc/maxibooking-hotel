<?php

namespace Tests\Bundle\BaseBundle\Lib\Normalization;

use MBH\Bundle\BaseBundle\Lib\Normalization\DocumentsCollectionFieldType;
use MBH\Bundle\BaseBundle\Lib\Normalization\NormalizationException;
use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;

class DocumentsCollectionFieldTypeTest extends WebTestCase
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
        $roomTypes = $this->getDm()->getRepository(RoomType::class)->findAll();
        $roomTypeIds = $this->getContainer()->get('mbh.helper')->toIds($roomTypes);
        $fieldType = new DocumentsCollectionFieldType(RoomType::class);
        $this->assertEquals($roomTypeIds, $fieldType->normalize($roomTypes));

        $this->expectException(NormalizationException::class);
        $this->expectExceptionMessage('DateTime is not an instance of Base');
        $fieldType->normalize([new \DateTime(), new \DateTime()]);

        $this->expectException(NormalizationException::class);
        $this->expectExceptionMessage('Passed value is not iterable');
        $fieldType->normalize(1231);
    }

    /**
     * @throws NormalizationException
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function testDenormalize()
    {
        $roomTypes = $this->getDm()->getRepository(RoomType::class)->findAll();
        $roomTypeIds = $this->getContainer()->get('mbh.helper')->toIds($roomTypes);
        $fieldType = new DocumentsCollectionFieldType(RoomType::class);
        $this->assertEquals($roomTypes, $fieldType->denormalize($roomTypeIds, ['dm' => $this->getDm()]));

        $this->expectException(NormalizationException::class);
        $this->expectExceptionMessage('Passed value is not iterable');
        $fieldType->denormalize(new Hotel(), ['dm' => $this->getDm()]);
    }
}