<?php

namespace Tests\Bundle\PriceBundle\Controller;

use MBH\Bundle\BaseBundle\Lib\Test\CrudWebTestCase;

class TariffControllerTest extends CrudWebTestCase
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
            ->setFormName('mbh_bundle_pricebundle_tariff_main_type')
            ->setNewTitle('Test tariff')
            ->setEditTitle('Test tariff edited')
            ->setNewUrl('/price/management/tariff/new')
            ->setListUrl('/price/management/tariff/')
            ->setAjaxList()
            ->setNewFormValues(['fullTitle' => $this->getNewTitle()])
            ->setNewFormErrors(['data.fullTitle'])
            ->setEditFormValues(['fullTitle' => $this->getEditTitle()])
            ->setListItemsCount(2)
        ;
    }
}
