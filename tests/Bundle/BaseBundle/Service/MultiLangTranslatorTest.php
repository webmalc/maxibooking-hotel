<?php

namespace Tests\Bundle\BaseBundle\Service;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Lib\Test\UnitTestCase;
use MBH\Bundle\BaseBundle\Service\MultiLangTranslator;
use MBH\Bundle\HotelBundle\Document\Hotel;
use Symfony\Component\DependencyInjection\ContainerInterface;

class MultiLangTranslatorTest extends UnitTestCase
{
    const TRANSLATIONS_BY_FIELD_WITH_LANG = [
        'description' => [
            'ru' => 'Русское описание',
            'en' => 'English description'
        ]
    ];

    /** @var ContainerInterface */
    private $container;
    /** @var DocumentManager */
    private $dm;
    /** @var MultiLangTranslator */
    private $multiLangTranslator;

    public static function setUpBeforeClass()
    {
        self::baseFixtures();
    }

    public function setUp()
    {
        parent::setUp();

        self::bootKernel();
        $this->container = self::getContainerStat();
        $this->dm = $this->container->get('doctrine.odm.mongodb.document_manager');
        $this->multiLangTranslator = $this->container->get('mbh.multi_lang_translator');
    }

    public static function tearDownAfterClass()
    {
        self::clearDB();
    }

    public function testSaveMultiLanguagesFields()
    {
        $this->container->get('mbh.client_config_manager')->fetchConfig()->setLanguages(['ru', 'en']);
        $descriptionTranslation = $this->dm
            ->getRepository('GedmoTranslatable:Translation')
            ->findBy(['locale' => 'en', 'objectClass' => 'MBH\Bundle\HotelBundle\Document\Hotel', 'field' => 'description']);
        $this->assertEmpty($descriptionTranslation);

        /** @var Hotel $hotel */
        $hotel = $this->dm
            ->getRepository('MBHHotelBundle:Hotel')
            ->findOneBy(['fullTitle' => $this->container->get('translator')->trans('mbhhotelbundle.hotelData.hotelOne')]);

        $this->multiLangTranslator
            ->saveByMultiLanguagesFields($hotel, self::TRANSLATIONS_BY_FIELD_WITH_LANG);
        $hotel->setLocale('en');
        $this->dm->refresh($hotel);
        $this->assertEquals('English description', $hotel->getDescription());
    }

    /**
     * @depends testSaveMultiLanguagesFields
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function testGetTranslationsByLanguages()
    {
        /** @var Hotel $hotel */
        $hotel = $this->dm
            ->getRepository('MBHHotelBundle:Hotel')
            ->findOneBy(['fullTitle' => $this->container->get('translator')->trans('mbhhotelbundle.hotelData.hotelOne')]);

        $translatableField = 'description';
        $translationsByLanguages = $this->multiLangTranslator->getTranslationsByLanguages($hotel, $translatableField, ['ru', 'en']);
        $this->assertNotEmpty($translationsByLanguages);
        $this->assertEquals(self::TRANSLATIONS_BY_FIELD_WITH_LANG[$translatableField], $translationsByLanguages);
    }
}