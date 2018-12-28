<?php
/**
 * Created by PhpStorm.
 * Date: 13.06.18
 */

class PaymentFormControllerTest extends \MBH\Bundle\BaseBundle\Lib\Test\CrudWebTestCase
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
            ->setFormName(\MBH\Bundle\OnlineBundle\Form\PaymentFormType::PREFIX)
            ->setNewTitle('not used')
            ->setEditTitle('not used')
            ->setNewUrl('/management/online/payment_form/new')
            ->setListUrl('/management/online/payment_form/');

        $this
            ->setNewFormValues(['frameWidth' => 100500])
            ->setEditFormValues(['frameWidth' => 200500])
            ->setNewFormErrors(['data.frameWidth']);

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
        $this->assertSame(1, $this->getListCrawler()->filter($this->getListContainer() . 'tbody tr')->count());
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

    protected function sendInvalidForm(\Symfony\Component\DomCrawler\Crawler $crawler, string $formClass, array $errors): void
    {
        $form = $crawler->filter($formClass)->form();
        $form->setValues([
            $this->getFormName() . '[frameWidth]' => 0,
        ]);
        $crawler = $this->client->submit($form);
        $this->assertStatusCode(200, $this->client);
        $this->assertValidationErrors($errors, $this->client->getContainer());
    }

    protected function checkSavedObject(string $title): void
    {
        $this->assertSame(2, $this->getListCrawler()->filter($this->getListContainer() . 'tbody tr')->count());
    }
}