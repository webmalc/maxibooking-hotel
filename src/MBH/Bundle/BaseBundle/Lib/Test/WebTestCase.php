<?php
namespace MBH\Bundle\BaseBundle\Lib\Test;

use Liip\FunctionalTestBundle\Test\WebTestCase as Base;
use MBH\Bundle\BaseBundle\Lib\Test\Traits\FixturesTestTrait;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Form;

abstract class WebTestCase extends Base
{
    use FixturesTestTrait;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var array
     */
    protected $listHeaders = [];

    /**
     * @var string
     */
    protected $listUrl;

    /**
     * Base setup
     */
    public function setUp()
    {
        $this->client = self::makeClient(true);
    }

    /**
     * @return array
     */
    public function getListHeaders(): array
    {
        return $this->listHeaders;
    }

    /**
     * @param array $listHeaders
     * @return static
     */
    public function setListHeaders(array $listHeaders): self
    {
        $this->listHeaders = $listHeaders;
        return $this;
    }

    /**
     * @param string $method
     * @param string $url
     * @return \Symfony\Component\DomCrawler\Crawler
     */
    public function getListCrawler($url = null, $method = 'GET'): Crawler
    {
        $url = $url ?? $this->getListUrl();
        return $this->client->request($method, $url, [], [], $this->getListHeaders());
    }

    /**
     * @return string
     */
    public function getListUrl(): string
    {
        if (!$this->listUrl) {
            throw new \InvalidArgumentException('not valid listUrl');
        }
        return $this->listUrl;
    }

    /**
     * @param string $listUrl
     * @return static
     */
    public function setListUrl(string $listUrl): self
    {
        $this->listUrl = $listUrl;
        return $this;
    }
}