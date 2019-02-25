<?php

namespace Tests\Bundle\PackageBundle\Controller;


use MBH\Bundle\BaseBundle\Lib\Test\CrudWebTestCase;

class OrganizationControllerTest extends  CrudWebTestCase
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
            ->setFormName('organization')
            ->setNewTitle('Test hotel')
            ->setEditTitle('Test hotel edited')
            ->setNewUrl('/package/organizations/create?type=contragents')
            ->setListUrl('/package/organizations/json')
            ->setAjaxList()
            ->setNewFormValues([
                'name' => $this->getNewTitle(),
                'inn' => '12345678',
                'cityId' => 8670

            ])
            ->setNewFormErrors([
                'data.cityId',
                'data.inn',
                'data.name',
            ])
            ->setEditFormValues([
                'name' => $this->getEditTitle()
            ])
            ->setListItemsCount(1);
    }
    /**
     * @param string|null $title
     * @throws \Exception
     */
    protected function checkSavedObject(string $title): void
    {
        $this->assertSame(1, $this
            ->getListCrawlerJsonResponse(null, 'GET', ['type' => 'contragents'])
            ->filter($this->getListContainer() . 'a:contains("' . $title . '")')
            ->count()
        );
    }

    /**
     * @param array|null $values
     * @param string|null $url
     * @param string|null $title
     * @param string|null $titleEdited
     * @param string|null $formName
     * @throws \Exception
     */
    protected function editFormBaseTest(
        array $values = null,
        string $url = null,
        string $title = null,
        string $titleEdited = null,
        string $formName = null
    )
    {
        $title = $title ?? $this->getNewTitle();
        $titleEdited = $titleEdited ?? $this->getEditTitle();
        $formName = $formName ?? $this->getFormName();
        $values = $values ?? $this->getEditFormValues();

        $crawler = $this->clickLinkInListWithParams(
            $url,
            'a:contains("' . $title . '")',
            false,
            ['type' => 'contragents']
        );

        $form = $crawler->filter('form[name="' . $formName . '"]')->form();

        $form->setValues(self::prepareFormValues($formName, $values));

        $this->client->submit($form);
        $this->client->followRedirect();

        //check saved object
        $this->assertSame(1, $this
            ->getListCrawlerJsonResponse($url, 'GET', ['type' => 'contragents'])
            ->filter($this->getListContainer() . 'a:contains("' . $titleEdited . '")')
            ->count()
        );
    }

    /**
     * @param string|null $url
     * @param string|null $title
     * @param int|null $count
     * @throws \Exception
     */
    protected function deleteBaseTest(string $url = null, string $title = null, int $count = null)
    {
        $url = $url ?? $this->getListUrl();
        $count = $count ?? $this->getListItemsCount();

        $this->clickLinkInListWithParams(
            $url,
            'a[class="btn btn-danger btn-xs delete-link"]',
            true,
            ['type' => 'contragents']
        );

        $this->assertSame(
            $count - 1,
            $this
                ->getListCrawlerJsonResponse($url, 'GET', ['type' => 'contragents'])
                ->filter($this->getListContainer() . 'a[rel="main"]')
                ->count()
        );
    }

    /**
     * @param string|null $url
     * @param string|null $title
     * @param int|null $count
     * @throws \Exception
     */
    protected function listBaseTest(string $url = null, string $title = null, int $count = null)
    {
        $title = $title ?? $this->getNewTitle();
        $count = $count ?? $this->getListItemsCount();

        $crawler = $this->getListCrawlerJsonResponse($url, 'GET', ['type' => 'contragents']);

        $this->assertSame($count, $crawler->filter(
            $this->getListContainer() . 'a:contains("' . $title . '")')
            ->count());
        $this->assertSame($count, $crawler->filter($this->getListContainer() . 'a[rel="main"]')->count());
    }

}

