<?php

namespace MBH\Bundle\PackageBundle\Services\Search;

use Symfony\Component\DependencyInjection\ContainerInterface;
use MBH\Bundle\PackageBundle\Lib\SearchQuery;

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
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->search = $this->container->get('mbh.package.search_simple');
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
