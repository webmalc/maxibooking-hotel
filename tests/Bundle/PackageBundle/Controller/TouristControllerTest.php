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
            ->setListItemsCount(7);
    }

    /**
     * @param string $method
     * @param string $url
     * @return \Symfony\Component\DomCrawler\Crawler
     * @throws \Exception
     */
    public function getListCrawler($url = null, $method = 'POST') : Crawler
    {
        $url = $url ?? $this->getListUrl();

        $crawler = $this->client->request('GET', '/package/tourist/', [], [], $this->getListHeaders());

        $token = $crawler->filter('input[id="mbhpackage_bundle_tourist_filter_form__token"]')->extract(['value'])[0];

        $form = [
            'form' => [
                '_token' => $token,
                'begin' => '',
                'end' => '',
                'citizenship' => '',
                'search' => '',
                'hotels' => '',
            ]
        ];

        $this->client->request($method, $url, $form, [], []);
        $response = $this->client->getResponse()->getContent();

        if(!isset(((array)json_decode($response))['data'])) {
            throw new \Exception('Data key is not defined in response json.');
        }

        $htmlData = $this->arraysFromValidJsonToString( ((array)json_decode($response))['data'] );

        return new Crawler($htmlData, 'http://localhost');
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
    ) {
        $title = $title ?? $this->getNewTitle();
        $titleEdited = $titleEdited ?? $this->getEditTitle();
        $formName = $formName ?? $this->getFormName();
        $values = $values ?? $this->getEditFormValues();

        $crawler = $this->clickLinkInList($url, 'a:contains("' . $title . '")', false, 'POST');

        $form = $crawler->filter('form[name="' . $formName . '"]')->form();
        $form->setValues(self::prepareFormValues($formName, $values));

        $this->client->submit($form);
        $this->client->followRedirect();

        //check saved object
        $this->assertSame(
            1,
            $this->getListCrawler()
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

        $this->clickLinkInList(
            $url,
            'a[class="btn btn-danger btn-xs delete-link"]',
            true,
            'POST'
        );

        $this->assertSame(
            $count,
            $this->getListCrawler()
                ->filter($this->getListContainer() . 'a[rel="main"]')
                ->count()
        );
    }

}