<?php

namespace Bundle\PackageBundle\Controller;

use MBH\Bundle\BaseBundle\Lib\Test\CrudWebTestCase;
use Symfony\Component\DomCrawler\Crawler;

class TouristControllerTest extends CrudWebTestCase
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
            ->setFormName('mbh_bundle_packagebundle_touristtype')
            ->setNewTitle('Test Tourist')
            ->setEditTitle('Test Edited')
            ->setNewUrl('/package/tourist/new')
            ->setListUrl('/package/tourist/json')
            ->setAjaxList()
            ->setNewFormValues([
                'firstName' => 'Tourist',
                'lastName' => 'Test',
            ])
            ->setNewFormErrors([
                'data.firstName',
                'data.lastName',
            ])
            ->setEditFormValues([
                'lastName' => $this->getEditTitle(),
            ])
            ->setListItemsCount(8);
    }
    /**
     * @param string|null $title
     * @throws \Exception
     */
    protected function checkSavedObject(string $title): void
    {
        $this->assertSame(1, $this
            ->getListCrawlerJsonResponse(null, 'POST')
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
            [],
            'POST'
        );

        $form = $crawler->filter('form[name="' . $formName . '"]')->form();

        $form->setValues(self::prepareFormValues($formName, $values));

        $this->client->submit($form);
        $this->client->followRedirect();

        //check saved object
        $this->assertSame(1, $this
            ->getListCrawlerJsonResponse($url, 'POST')
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
            [],
            'POST'
        );
        $this->assertSame(
            $count - 1,
            $this
                ->getListCrawlerJsonResponse($url, 'POST')
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

        $crawler = $this->getListCrawlerJsonResponse($url, 'POST');

        $this->assertSame(1, $crawler->filter(
            $this->getListContainer() . 'a:contains("' . $title . '")')
            ->count()
        );
        $this->assertSame($count, $crawler->filter($this->getListContainer() . 'a[rel="main"]')->count());

    }
}