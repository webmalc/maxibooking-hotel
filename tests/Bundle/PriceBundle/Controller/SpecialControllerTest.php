<?php

namespace Tests\Bundle\TariffBundle\Controller;

use MBH\Bundle\BaseBundle\Lib\Test\Traits\CrudWebTestCaseTrait;
use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;

class SpecialControllerTest extends WebTestCase
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
            ->setFormName('mbh_bundle_pricebundle_special_type')
            ->setNewTitle('Test special')
            ->setEditTitle('Test special edited')
            ->setNewUrl('/price/management/special/new')
            ->setListUrl('/price/management/special/')
            ->setNewFormValues(['fullTitle' => $this->getNewTitle()])
            ->setNewFormErrors(['data.fullTitle'])
            ->setEditFormValues(['fullTitle' => $this->getEditTitle()])
            ->setListItemsCount(0)
        ;
    }
}