<?php

namespace Tests\Bundle\HotelBundle\Controller;

use MBH\Bundle\BaseBundle\Lib\Test\Traits\CrudWebTestCaseTrait;
use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;

class HotelControllerTest extends WebTestCase
{
    use CrudWebTestCaseTrait;

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
        $this
            ->setFormName('mbh_bundle_hotelbundle_hoteltype')
            ->setNewTitle('Test hotel')
            ->setEditTitle('Test hotel edited')
            ->setNewUrl('/management/hotel/new')
            ->setListUrl('/management/hotel/')
            ->setNewFormValues(['fullTitle' => $this->getNewTitle()])
            ->setNewFormErrors(['data.fullTitle'])
            ->setEditFormValues(['fullTitle' => $this->getEditTitle()])
            ->setListItemsCount(2)
        ;
    }

    public function testExtended()
    {

        $fixtures = $this->loadFixtures([
            'MBH\Bundle\UserBundle\DataFixtures\MongoDB\GroupsData',
            'MBH\Bundle\UserBundle\DataFixtures\MongoDB\UserData',
            'MBH\Bundle\HotelBundle\DataFixtures\MongoDB\HotelData'
        ], null, 'doctrine_mongodb')->getReferenceRepository();

        $hotel = $fixtures->getReference('hotel-one');
        $formName = 'mbh_bundle_hotelbundle_hotel_extended_type';
        $url = '/management/hotel/' . $hotel->getId() . '/edit/extended';
        $newValues = self::prepareFormValues($formName, [
            'settlement' => 'Test settlement', 'longitude' => 33.2
        ]);

        $crawler = $this->client->request('GET', $url);
        $this->assertStatusCode(200, $this->client);
        $formClass = 'form[name="' . $formName . '"]';

        $form = $crawler->filter($formClass)->form();
        $form->setValues($newValues);
        $this->client->submit($form);
        $this->assertStatusCode(302, $this->client);

        $crawler = $this->client->request('GET', $url);
        $this->assertStatusCode(200, $this->client);
        $this->assertTrue(self::checkValuesInForm($crawler->filter($formClass)->form(), $newValues));
    }
}