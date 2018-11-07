<?php


namespace MBH\Bundle\SearchBundle\Services\Search\Determiners\Occupancies;


use MBH\Bundle\SearchBundle\Lib\Exceptions\OccupancyDeterminerException;
use MBH\Bundle\SearchBundle\Services\Search\Determiners\DeterminerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DeterminerFactory
{

    public const COMMON_DETERMINER = 'mbh_search.occupancy_determiner_common';

    public const NO_TRANSFORM_DETERMINER = 'mbh_search.occupancy_determiner_no_transform';
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
     * @return DeterminerInterface
     * @throws OccupancyDeterminerException
     */
    public function create(string $type): DeterminerInterface
    {
        if ($type === self::COMMON_DETERMINER) {
            return $this->container->get('mbh_search.occupancy_determiner_common');
        }
        if ($type === self::NO_TRANSFORM_DETERMINER) {
            return $this->container->get('mbh_search.occupancy_determiner_no_transform');
        }

        throw new OccupancyDeterminerException('Cannot create determiner');
    }
}