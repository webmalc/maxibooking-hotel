<?php

namespace MBH\Bundle\PackageBundle\Services\Search;

use MBH\Bundle\ClientBundle\Document\ClientConfig;
use MBH\Bundle\PackageBundle\Document\SearchQuery;
use MBH\Bundle\PackageBundle\Lib\SearchResult;
use MBH\Bundle\PriceBundle\Document\Tariff;
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
        /** @var ClientConfig $clientConfig */
        $clientConfig = $this->dm->getRepository(ClientConfig::class)->fetchConfig();
        $query->setSave(($clientConfig->isQueryStat() === true) && $query->isSave());

        $savedQueryId = $query->isSave() ? $this->saveQuery($query): null;
        $search = $this->search->search($query);
        if (null !== $savedQueryId ) {
            array_walk($search, [$this, 'injectQueryId'], $savedQueryId);
        }

        return $search;
    }

    /**
     * @param SearchQuery $query
     * @return array
     */
    public function searchTariffs(SearchQuery $query)
    {
        return $this->search->searchTariffs($query);
    }

    /**
     * @param SearchQuery $query
     * @return array
     */
    public function searchSpecials(SearchQuery $query)
    {
        return $this->search->searchSpecials($query);
    }

    public function searchStrictSpecials(SearchQuery $query)

    {
        return $this->search->searchStrictNowSpecials($query);
    }

    private function saveQuery(SearchQuery $query): string
    {
        if (!empty($query->tariff) && !$query->tariff instanceof Tariff) {
            $query->tariff = $this->dm->getRepository('MBHPriceBundle:Tariff')
                ->fetchById($query->tariff);
        }
        $this->dm->persist($query);
        $this->dm->flush($query);

        return $query->getId();
    }

    private function injectQueryId(&$result, $key, string $queryId)
    {
        if ($result instanceof SearchResult) {
            $result->setQueryId($queryId);
        }
        if (is_array($result) && isset ($result['results']) && is_array($result['results'])) {
            foreach ($result['results'] as $searchResult) {
                /** @var SearchResult $searchResult */
                $searchResult->setQueryId($queryId);
            }
        }
    }

    /**
     * @param SearchQuery $query
     * @param array $tariffs
     * @return array|SearchResult[]
     */
    public function searchBeforeResult(SearchQuery $query, array $tariffs)
    {
        usort($tariffs, function (Tariff $tariff1, Tariff $tariff2) {
            if ($tariff1->getIsDefault()) {
                return -2;
            }
            if ($tariff2->getIsDefault()) {
                return -2;
            }

            return $tariff1->getPosition() < $tariff2->getPosition() ? 1 : -1;
        });

        foreach ($tariffs as $tariff) {
            $query->tariff = $tariff;
            $searchResults = $this->search($query);
            if (!empty($searchResults)) {
                return $searchResults;
            }
        }

        return [];
    }
}
