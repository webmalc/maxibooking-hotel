<?php

namespace Tests\Bundle\PriceBundle\Controller;

use MBH\Bundle\BaseBundle\Lib\Test\CrudWebTestCase;


class SpecialControllerTest extends CrudWebTestCase
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
            ->setFormName('mbh_bundle_pricebundle_special_type')
            ->setNewTitle('Special from test')
            ->setEditTitle('Special from test edited')
            ->setNewUrl('/price/management/special/new')
            ->setListUrl('/price/management/special/')
            ->setAjaxList()
            ->setNewFormValues([
                'fullTitle' => $this->getNewTitle(),
                'begin' => new \DateTime('midnight + 1 day'),
                'end' => new \DateTime('midnight + 10 days'),
                'discount' => 20,
                'displayFrom' => new \DateTime('midnight -30 days'),
                'displayTo' => new \DateTime('midnight'),
                'limit' => 10
            ])
            ->setNewFormErrors([
                'data.begin', 'data.discount', 'data.end', 'data.displayFrom',
                'data.displayTo', 'data.limit', 'data.fullTitle'
            ])
            ->setEditFormValues(['fullTitle' => $this->getEditTitle()])
            ->setListItemsCount(3)
        ;
    }
}
