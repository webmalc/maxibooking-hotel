<?php
/**
 * Created by PhpStorm.
 * Date: 14.06.18
 */

class FormControllerTest extends \MBH\Bundle\BaseBundle\Lib\Test\CrudWebTestCase
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
            ->setFormName(\MBH\Bundle\OnlineBundle\Form\FormType::PREFIX)
            ->setNewTitle('not used')
            ->setEditTitle('not used')
            ->setNewUrl('/management/online/form/new')
            ->setListUrl('/management/online/form/');

        $this
            ->setNewFormValues([
                'resultsUrl'   => 'http://google.com',
                'paymentTypes' => [\MBH\Bundle\OnlineBundle\Document\SettingsOnlineForm\FormConfig::PAYMENT_TYPE_BY_RECEIPT_FULL],
                'frameWidth'   => 100500,
            ])
            ->setEditFormValues(['frameWidth' => 200500])
            ->setNewFormErrors(['data.resultsUrl']);

        $this->setListItemsCount(0);
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
        $this->checkSavedObject('not used');
    }

    /**
     * @depends testNew
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

        $this->clickLinkInList($url, ' a[data-text^="Вы действительно хотите удалить запись"]', true);
        $this->assertSame(0, $this->getListCrawler()->filter($this->getListContainer() . 'tbody tr')->count());
    }

    protected function editFormBaseTest(
        array $values = null,
        string $url = null,
        string $title = null,
        string $titleEdited = null,
        string $formName = null
    ) {
        $title = $title ?? $this->getNewTitle();
        $titleEdited = $titleEdited ?? $this->getEditTitle();
        $formName = $formName ?? $this->getFormName();
        $values = $values ?? $this->getEditFormValues();

        $crawler = $this->clickLinkInList($url, 'tbody tr td a');

        $form = $crawler->filter('form[name="' . $formName . '"]')->form();

        $form->setValues(self::prepareFormValues($formName, $values));

        $this->client->submit($form);
        $this->client->followRedirect();

        //check saved object
        $this->checkSavedObject('not used');
    }

    protected function checkSavedObject(string $title): void
    {
        $this->assertSame(1, $this->getListCrawler()->filter($this->getListContainer() . 'tbody tr')->count());
    }
}
