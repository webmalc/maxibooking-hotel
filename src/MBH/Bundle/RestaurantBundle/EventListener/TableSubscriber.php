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
use Doctrine\ODM\MongoDB\Event\OnFlushEventArgs;
use Doctrine\ODM\MongoDB\Events;
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
            Events::prePersist => 'prePersist',
            Events::preUpdate => 'onFlush',
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

    public function onFlush(OnFlushEventArgs $args)
    {
        $dm = $args->getDocumentManager();
        $uow = $dm->getUnitOfWork();
        $docs_tables = array();

        $shiftTables=$uow->getScheduledCollectionUpdates();
        foreach ($shiftTables as $shiftTable ) {
            $docs_tables = $shiftTable->toArray();
        }

        $docs = array_merge(
            $uow->getScheduledDocumentUpdates(),
            $docs_tables
        );

        foreach ($docs as $doc) {

            if ($doc instanceof Table) {

                foreach ($doc->getWithShifted() as $item )
                {
                    $doc->addShifted($item);
                }
                $uow->recomputeSingleDocumentChangeSet($dm->getClassMetadata(get_class($doc)), $doc);
            }

        }

    }

}