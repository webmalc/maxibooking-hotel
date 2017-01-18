<?php

namespace MBH\Bundle\PackageBundle\Services\Search;

use MBH\Bundle\ClientBundle\Document\ClientConfig;
use MBH\Bundle\PackageBundle\Lib\SearchQuery;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 *  Search service
 */
class SearchFactory implements SearchInterface
{

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @var SearchInterface
     */
    protected $search;

    /**
     * @var \Doctrine\ODM\MongoDB\DocumentManager
     */
    protected $dm;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->dm = $this->container->get('doctrine_mongodb')->getManager();
        $this->search = $this->container->get('mbh.package.search_simple');
    }

    /**
     * @return $this
     */
    public function setWithTariffs()
    {
        $this->search = $this->container->get('mbh.package.search_with_tariffs')->setSearch($this->search);

        return $this;
    }

    /**
     * @var int $range
     * @return $this
     */
    public function setAdditionalDates(int $range = 0)
    {
        if ($range) {
            $this->search = $this->container->get('mbh.package.search_multiple_dates')->setSearch($this->search);
        }

        return $this;
    }

    /**
     * @param SearchQuery $query
     * @return \MBH\Bundle\PackageBundle\Lib\SearchResult[]
     */
    public function search(SearchQuery $query)
    {
        return $this->search->search($query);
    }

    /**
     * @param SearchQuery $query
     * @return array
     */
    public function searchTariffs(SearchQuery $query)
    {
        return $this->search->searchTariffs($query);
    }

}
