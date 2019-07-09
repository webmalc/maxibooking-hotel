<?php


namespace MBH\Bundle\SearchBundle\Services;


use MBH\Bundle\SearchBundle\Document\SearchConfig;
use MBH\Bundle\SearchBundle\Document\SearchConfigRepository;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchConfigException;

/**
 * Class SearchConfigService
 * @package MBH\Bundle\SearchBundle\Services
 */
class SearchConfigService
{
    /** @var SearchConfigRepository */
    private $searchConfigRepository;

    /**
     * SearchConfigService constructor.
     * @param SearchConfigRepository $searchConfigRepository
     */
    public function __construct(SearchConfigRepository $searchConfigRepository)
    {
        $this->searchConfigRepository = $searchConfigRepository;
    }

    /**
     * @return SearchConfig
     * @throws SearchConfigException
     */
    public function getConfig(): SearchConfig
    {
        $config = $this->searchConfigRepository->findAll();
        if (is_array($config) && count($config) > 1) {
            throw new SearchConfigException('Config more than one!');
        }

        if ($config && is_array($config)) {
            $config = reset($config);
        } else {
            $config = new SearchConfig();
        }

        return $config;
    }


}