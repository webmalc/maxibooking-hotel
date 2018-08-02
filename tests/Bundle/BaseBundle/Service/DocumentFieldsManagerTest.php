<?php

namespace Tests\Bundle\BaseBundle\Service;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use MBH\Bundle\BaseBundle\Document\Image;
use MBH\Bundle\BaseBundle\Lib\Normalization\BooleanFieldType;
use MBH\Bundle\BaseBundle\Lib\Normalization\DocumentFieldType;
use MBH\Bundle\BaseBundle\Lib\Normalization\DocumentsCollectionFieldType;
use MBH\Bundle\BaseBundle\Service\DocumentFieldsManager;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\Room;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PackageBundle\Lib\SearchResult;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Translation\TranslatorInterface;

class DocumentFieldsManagerTest extends WebTestCase
{
    /** @var DocumentFieldsManager */
    private $documentFieldsManager;
    /** @var TranslatorInterface */
    private $translator;

    protected function setUp()
    {
        /** @var ContainerInterface $container */
        $container = $this->getContainer();
        $this->documentFieldsManager = $container->get('mbh.document_fields_manager');
        $this->translator = $container->get('translator');
    }

    public function testGetFieldName()
    {
        $descriptionTransId = $this->documentFieldsManager->getFieldName(Hotel::class, 'description', false);
        $this->assertEquals($descriptionTransId, 'form.hotelType.description');

        $latitudeFieldName = $this->translator->trans('form.hotelExtendedType.latitude');
        $expectedLatitudeFieldName = $this->documentFieldsManager->getFieldName(Hotel::class, 'latitude');
        $this->assertEquals($expectedLatitudeFieldName, $latitudeFieldName);

        $this->expectException(\InvalidArgumentException::class);
        $this->documentFieldsManager->getFieldName('some nonexistent doc type', 'nonexistent field');
    }

    public function testGetFieldsByCorrectnessStatuses()
    {
        $testDocument = (new Hotel())
            ->setCityId(123)
            ->setCountryTld('sadf')
            ->addImage(new Image())
        ;
        $testedFieldNames = ['cityId', 'countryTld', 'images', 'description', 'contactInformation'];

        $fieldsByCorrectnessStatuses
            = $this->documentFieldsManager->getFieldsByCorrectnessStatuses($testedFieldNames, $testDocument);

        $this->assertEquals([
            DocumentFieldsManager::CORRECT_FIELD_STATUS => ['cityId', 'countryTld', 'images'],
            DocumentFieldsManager::EMPTY_FIELD_STATUS => ['description', 'contactInformation']
        ], $fieldsByCorrectnessStatuses);
    }

    public function testFillByDocumentFieldsWithFieldNameKeys()
    {
        $testHotel = (new Hotel())
            ->setTitle('test')
            ->setCityId(123)
            ->setCountryTld('ru');
        $testFields = ['title', 'cityId', 'countryTld'];

        $this->assertEquals([
            'title' => 'test',
            'cityId' => 123,
            'countryTld' => 'ru'
        ], $this->documentFieldsManager->fillByDocumentFieldsWithFieldNameKeys($testHotel, $testFields));
    }

    /**
     * @throws \ReflectionException
     */
    public function testGetClassFullNameByShortNameFromUseStatementsInTheSameNamespace()
    {
        $shortName = 'RoomType';
        $reflClass = new \ReflectionClass(Hotel::class);

        $this->assertEquals(RoomType::class, $this->documentFieldsManager->getClassFullNameByShortNameFromUseStatements($reflClass, $shortName));
    }

    /**
     * @throws \ReflectionException
     */
    public function testGetClassFullNameByShortNameFromUseStatementsInTheAnotherNamespace()
    {
        $shortName = 'Image';
        $reflClass = new \ReflectionClass(Hotel::class);

        $this->assertEquals(Image::class, $this->documentFieldsManager->getClassFullNameByShortNameFromUseStatements($reflClass, $shortName));
    }

    /**
     * @throws \ReflectionException
     */
    public function testGetClassFullNameByShortNameFromUseStatementsForMissingClass()
    {
        $shortName = 'PriceCache';
        $reflClass = new \ReflectionClass(Hotel::class);

        $this->expectException(\InvalidArgumentException::class);
        $this->documentFieldsManager->getClassFullNameByShortNameFromUseStatements($reflClass, $shortName);
    }

    /**
     * @throws \ReflectionException
     */
    public function testGetFieldTypeForSpecialField()
    {
        $testedProperty = new \ReflectionProperty(SearchResult::class, 'roomType');
        $fieldType = $this->documentFieldsManager->getFieldType($testedProperty);

        $this->assertInstanceOf(DocumentFieldType::class, $fieldType);
        $this->assertEquals(RoomType::class, $fieldType->getClass());
    }

    /**
     * @throws \ReflectionException
     */
    public function testGetFieldTypeWithFieldAnnotation()
    {
        $testedProperty = new \ReflectionProperty(Hotel::class, 'isDefault');

        $this->assertInstanceOf(BooleanFieldType::class, $this->documentFieldsManager->getFieldType($testedProperty));
    }

    /**
     * @throws \ReflectionException
     * @depends testGetClassFullNameByShortNameFromUseStatementsInTheSameNamespace
     */
    public function testGetFieldTypeWithDocAnnotation()
    {
        $testedProperty = new \ReflectionProperty(Hotel::class, 'rooms');
        $fieldType = $this->documentFieldsManager->getFieldType($testedProperty);

        $this->assertInstanceOf(DocumentsCollectionFieldType::class, $fieldType);
        $this->assertEquals(Room::class, $fieldType->getClass());
    }
}