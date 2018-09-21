<?php


namespace MBH\Bundle\SearchBundle\Lib\Combinations;


use Symfony\Component\DependencyInjection\ContainerInterface;

class CombinationCreator
{
    public const NO_CHILDREN_AGES = 'no_children_ages';

    public const WITH_CHILDREN_AGES = 'with_children_ages';

    /** @var ContainerInterface */
    private $container;

    /**
     * CombinationCreator constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param string $type
     * @return CombinationInterface|object
     */
    public function getCombinationType(string $type): CombinationInterface
    {
        return $this->container->get('mbh_search.combinations.'.$type);
    }
}