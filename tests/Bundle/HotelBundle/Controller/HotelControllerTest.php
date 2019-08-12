<?php

namespace Tests\Bundle\HotelBundle\Controller;

use MBH\Bundle\BaseBundle\Lib\Test\CrudWebTestCase;


class HotelControllerTest extends CrudWebTestCase
{

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

    public function testNew()
    {
        $this->newFormBaseTest();
    }

    /**
     * @depends testNew
     */
    public function testIndex()
    {
        $this->listBaseTest();
    }

    /**
     * @depends testIndex
     */
    public function testEdit()
    {
        $this->editFormBaseTest();
    }

    /**
     * @depends testEdit
     */
    public function testDelete()
    {
        $url = $this->getListUrl();
        $title = $this->getEditTitle();
        $count = $this->getListItemsCount() + 1;

        $result = $this->clickLinkInList($url, ' a[data-text="Вы действительно хотите удалить запись «' . $title . '»?"]', true);

        $this->assertContains(
            'Невозможно удалить данные об отеле, так как для него существуют записи о тарифах',
            $result->filter('#messages')->text()
        );
        $this->assertSame($count, $result->filter($this->getListContainer() . 'a[rel="main"]')->count());
    }

    /**
     * @depends testDelete
     */
    public function testExtendedInformationForm()
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
            'rating' => '4', 'checkinoutPolicy' => 'test policy'
        ]);

        $crawler = $this->client->request('GET', $url);
        self::assertStatusCode(200, $this->client);
        $formClass = 'form[name="' . $formName . '"]';

        $form = $crawler->filter($formClass)->form();
        $form->setValues($newValues);
        $this->client->submit($form);
        self::assertStatusCode(302, $this->client);

        $crawler = $this->client->request('GET', $url);
        self::assertStatusCode(200, $this->client);
        $this->assertTrue(self::checkValuesInForm($crawler->filter($formClass)->form(), $newValues));
    }
}
