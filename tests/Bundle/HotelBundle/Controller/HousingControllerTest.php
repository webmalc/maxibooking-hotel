<?php

namespace Tests\Bundle\HotelBundle\Controller;

use MBH\Bundle\BaseBundle\Lib\Test\Traits\CrudWebTestCaseTrait;
use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;

class HousingControllerTest extends WebTestCase
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
            ->setFormName('mbh_corpus')
            ->setNewTitle('New name corpus')
            ->setEditTitle('Edit name corpus')
            ->setNewUrl('/management/hotel/housing/new')
            ->setListUrl('/management/hotel/housing/')
            ->setNewFormValues(['name' => $this->getNewTitle()])
            ->setNewFormErrors(['data.name'])
            ->setEditFormValues(['name' => $this->getEditTitle()])
            ->setListItemsCount(0);
    }

}
