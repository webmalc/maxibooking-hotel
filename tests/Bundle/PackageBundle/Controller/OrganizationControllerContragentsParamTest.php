<?php

namespace Tests\Bundle\PackageBundle\Controller;


use MBH\Bundle\BaseBundle\Lib\Test\CrudWebTestCase;
use Symfony\Component\DomCrawler\Crawler;

class OrganizationControllerContragentsParamTest extends  CrudWebTestCase
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
            ->setListItemsCount(0);
    }

    /**
     * @param string $method
     * @param string $url
     * @param $params array
     * @return \Symfony\Component\DomCrawler\Crawler
     * @throws \Exception
     */
    public function getListCrawler($url = null, $method = 'GET', $params = ['type' => 'contragents']) : Crawler
    {
        $url = $url ?? $this->getListUrl();
        $this->client->request($method, $url, $params, [], $this->getListHeaders());

        $response = $this->client->getResponse()->getContent();

        if(!isset(((array)json_decode($response))['data'])) {
            throw new \Exception('Data key is not defined in response json.');
        }

        $htmlData = $this->arraysFromValidJsonToString( ((array)json_decode($response))['data'] );

        return new Crawler($htmlData, 'http://localhost');
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
            true
        );

        $this->assertSame(
            $count,
            $this->getListCrawler()
                ->filter($this->getListContainer() . 'a[rel="main"]')
                ->count()
        );
    }
}

