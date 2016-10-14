<?php

namespace MBH\Bundle\BaseBundle\EventListener\OnRemoveSubscriber;

use Documents\CustomRepository\Repository;
use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\HotelBundle\Document\Task;
use MBH\Bundle\PackageBundle\Document\Order;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use MBH\Bundle\PackageBundle\Document\PackageService;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\RestaurantBundle\Document\Ingredient;
use Symfony\Component\DependencyInjection\ContainerInterface;
use MBH\Bundle\PackageBundle\Lib\DeleteException;
use Doctrine\ODM\MongoDB\UnitOfWork;
use MBH\Bundle\PriceBundle\Document\RoomCache;

use Doctrine\ODM\MongoDB\Events;

use Doctrine\Common\EventSubscriber;

class Subscriber implements EventSubscriber
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @var \Symfony\Component\Translation\IdentityTranslator
     */
    protected $translator;

    public function getSubscribedEvents()
    {
        return [
            Events::preRemove => 'preRemove',
        ];
    }

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function preRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getDocument();
        $dm = $this->container->get('doctrine_mongodb')->getManager();
        $settings = DocumentsRelationships::getRelationships();
        if (array_key_exists(get_class($entity), $settings)) {
            $settings = $settings[get_class($entity)];
            foreach ($settings as $setting) {
                /** @var Relationship $setting */
                /** @var Repository $repository */
                $repository = $dm->getRepository($setting->getDocumentClass());
                if ($setting->IsMany()) {
                    $count = $repository->createQueryBuilder()
                        ->field($setting->getFieldName())->includesReferenceTo($entity)
                        ->field('deletedAt')->exists(false)
                        ->getQuery()
                        ->count();
                } else {
                    $query = $repository->createQueryBuilder()
                        ->field($setting->getFieldName())->references($entity)
                        ->field('deletedAt')->exists(false)
                        ->getQuery();
                    $count = $query->count();

                }
                if ($count > 0) {
                    $message = $setting->getErrorMessage() ? $setting->getErrorMessage() : 'exception.relation_delete.message'; // have existing relation
                    throw new DeleteException($message, $count);
                }
            }
        }
    }
}