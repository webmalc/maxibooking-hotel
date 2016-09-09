<?php
/**
 * Created by PhpStorm.
 * User: zalex
 * Date: 01.07.16
 * Time: 15:44
 */

namespace MBH\Bundle\RestaurantBundle\EventListener;


use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use MBH\Bundle\RestaurantBundle\Document\Table;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class RestaurantSubscriber
 * @package MBH\Bundle\RestaurantBundle\EventListener
 */
class TableSubscriber implements EventSubscriber
{

    /**
     * @var ContainerInterface
     */
    private $container;


    /**
     * RestaurantSubscriber constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            'prePersist'
        ];
    }



    /**
     * @param LifecycleEventArgs $args
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $doc = $args->getDocument();

        if ($doc instanceof Table) {

            foreach ($doc->getWithShifted() as $item )
            {
                $doc->addShifted($item);
            }
        }


    }

}