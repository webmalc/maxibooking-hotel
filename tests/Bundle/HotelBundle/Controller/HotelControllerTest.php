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
}