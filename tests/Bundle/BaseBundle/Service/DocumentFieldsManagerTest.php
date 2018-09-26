<?php

namespace Tests\Bundle\BaseBundle\Service;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use MBH\Bundle\BaseBundle\Document\Image;
use MBH\Bundle\BaseBundle\Service\DocumentFieldsManager;
use MBH\Bundle\HotelBundle\Document\Hotel;
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
        $this->assertEquals($descriptionTransId, 'site_manager.description.hotel');

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
}