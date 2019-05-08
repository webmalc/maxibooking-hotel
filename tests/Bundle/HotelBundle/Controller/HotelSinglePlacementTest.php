<?php

namespace Tests\Bundle\HotelBundle\Controller;

use MBH\Bundle\BaseBundle\Lib\Test\CrudWebTestCase;
use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;

class HotelSinglePlacementTest extends WebTestCase
{

    protected $formName = 'mbh_bundle_hotelbundle_room_type_type';

    protected $formUrl = 'management/hotel/roomtype/new';

    protected $baseAfterRedirectUrl = 'http://localhost/management/hotel/roomtype/?tab=';

    protected $formValuesFalse = [
        'fullTitle' => 'testTitle',
        'isSinglePlacement' => false,
    ];

    protected $formValuesTrue = [
        'fullTitle' => 'testTitleTrue',
        'isSinglePlacement' => true,
    ];

    protected $needle = 'Цена 1-местного размещения';

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
    }

    public function testFalseCheckbox()
    {
        $afterRedirectUrl = $this->submitFormGetRedirectUrl($this->formValuesFalse);

        $newRoomTypeId = $this->getRoomTypeByTitle($this->formValuesFalse['fullTitle'])->getId();

        $this->assertEquals($this->baseAfterRedirectUrl . $newRoomTypeId, $afterRedirectUrl);
    }

    /**
     * @depends testFalseCheckbox
     */
    public function testPriceCacheFalseList()
    {
        $this->assertEquals(0, $this->countPriceCacheListNeedle());
    }

    /**
     * @depends testPriceCacheFalseList
     */
    public function testTrueCheckbox()
    {
        $afterRedirectUrl = $this->submitFormGetRedirectUrl($this->formValuesTrue);
        
        $newRoomTypeId = $this->getRoomTypeByTitle($this->formValuesTrue['fullTitle'])->getId();

        $this->assertEquals($this->baseAfterRedirectUrl . $newRoomTypeId, $afterRedirectUrl);
    }

    /**
     * @depends testTrueCheckbox
     */
    public function testPriceCacheTrueList()
    {
        $this->assertEquals(2, $this->countPriceCacheListNeedle());
    }

    protected function submitFormGetRedirectUrl($formValues)
    {
        $crawler = $this->client->request('GET', $this->formUrl);
        $formClass = 'form[name="' . $this->formName . '"]';

        $form = $crawler->filter($formClass)->form();
        $form->setValues(CrudWebTestCase::prepareFormValues($this->formName, $formValues));
        $this->client->submit($form);

        return $this->client->followRedirect()->getUri();
    }

    protected function countPriceCacheListNeedle()
    {
        return $this
            ->getListCrawler("/price/price_cache/table?begin=15.03.2019&end=29.04.2019")
            ->filter('small:contains("' . $this->needle . '")')
            ->count();
    }

    protected function getRoomTypeByTitle($title)
    {
        return $this
            ->getContainer()
            ->get('doctrine.odm.mongodb.document_manager')
            ->getRepository('MBHHotelBundle:RoomType')
            ->findOneBy(['fullTitle' => $title]);
    }
}

