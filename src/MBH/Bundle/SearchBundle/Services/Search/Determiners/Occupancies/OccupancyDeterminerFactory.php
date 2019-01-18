<?php


namespace MBH\Bundle\SearchBundle\Services\Search\Determiners\Occupancies;


use MBH\Bundle\SearchBundle\Lib\Exceptions\OccupancyDeterminerException;
use MBH\Bundle\SearchBundle\Services\Search\Determiners\OccupancyDeterminerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class OccupancyDeterminerFactory
{

    public const COMMON_DETERMINER = 'mbh_search.occupancy_determiner_common';

    public const WARM_UP_DETERMINER = 'mbh_search.occupancy_warm_up_determiner';

    public const CHILD_FREE_TARIFF_DETERMINER = 'mbh_search.occupancy_determiner_child_free_tariff';
    /** @var ContainerInterface */
    private $container;

    /**
     * DeterminerFactory constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }


    /**
     * @param string $type
     * @return OccupancyDeterminerInterface
     * @throws OccupancyDeterminerException
     */
    public function create(string $type): OccupancyDeterminerInterface
    {
        if ($type === self::WARM_UP_DETERMINER) {
            return $this->container->get('mbh_search.occupancy_warm_up_determiner');
        }
        if ($type === self::COMMON_DETERMINER) {
            return $this->container->get('mbh_search.occupancy_determiner_common');
        }
        if ($type === self::CHILD_FREE_TARIFF_DETERMINER) {
            return $this->container->get('mbh_search.occupancy_determiner_child_free_tariff');
        }

        throw new OccupancyDeterminerException('Cannot create determiner');
    }
}