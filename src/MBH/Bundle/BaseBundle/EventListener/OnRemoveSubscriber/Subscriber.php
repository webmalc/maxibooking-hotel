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
        $this->translator = $this->container->get('translator');
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

        $this->container->get('mbh.cache')->clear('accommodation_rooms');
        $this->container->get('mbh.cache')->clear('room_cache_fetch');

        $ch = $this->container->get('mbh.channelmanager');

        if ($entity instanceof RoomType) {
            $this->removeDoc('Room', 'RoomType', $entity);
            $ch->updateInBackground();
        }

        if ($entity instanceof Tariff) {
            if ($entity->getIsDefault()) {
                throw new DeleteException('subscriber.cannot_delete_default_tariff_error');
            }
            $this->removeDoc('Tariff', 'Tariff', $entity);
            $ch->updateInBackground();
        }

        if($entity instanceof Room) {
            $this->container->get('mbh.cache')->clear('accommodation_rooms');
        }

        if ($entity instanceof Ingredient) {
            $repository = $dm->getRepository('MBHRestaurantBundle:DishMenuItem');
            $mongoId = new \MongoId($entity->getId());
            $dishMenuItems = $repository->findBy(['dishIngredients.ingredient.$id' => $mongoId]);
            if ($dishMenuItems) {
                throw new DeleteException('exception.ingredient_relation_delete.message.dishIngredient', count($dishMenuItems));
            }
        }

        if ($entity instanceof Task) {
            /** @var UnitOfWork $uow */
            $uow = $dm->getUnitOfWork();
            /** @var Task $entity */
            $room = $entity->getRoom();

            if ($entity->getStatus() === Task::STATUS_PROCESS) {
                if (!count($this->checkRemainsProcess($dm, $entity))) {
                    $room->removeStatus($entity->getType()->getRoomStatus());
                    $uow->recomputeSingleDocumentChangeSet($dm->getClassMetadata(get_class($room)), $room);
                }
            }
        }


        //Delete packages from order
        if ($entity instanceof Order)
        {
            foreach($entity->getPackages() as $package) {

                foreach ($package->getServices() as $packageService) {
                    $packageService->setDeletedAt(new \DateTime());
                    $dm->persist($packageService);
                }

                $package->setServicesPrice(0);
                $package->setDeletedAt(new \DateTime());
                $dm->persist($package);
                $end = clone $package->getEnd();
                $this->container->get('mbh.room.cache')->recalculate(
                    $package->getBegin(), $end->modify('-1 day'), $package->getRoomType(), $package->getTariff(), false
                );
                $this->container->get('mbh.cache')->clear('accommodation_rooms');
                $this->container->get('mbh.cache')->clear('room_cache_fetch');
            }
            $entity->setPrice(0);
            $dm->persist($entity);
            $dm->flush();

            $this->container->get('mbh.channelmanager')->updateRoomsInBackground();
        }

        //Calc paid
        if($entity instanceof CashDocument && $entity->getOrder()) {
            try {
                /** @var CashDocument $entity */
                $order = $entity->getOrder();
                $this->container->get('mbh.calculation')->setPaid($order, null, $entity);
                $dm->persist($order);
                $dm->flush();
            } catch (\Exception $e) {

            }
        }

        //Calc services price
        if($entity instanceof PackageService) {
            try {
                /** @var Package $package */
                $package = $entity->getPackage();
                $this->container->get('mbh.calculation')->setServicesPrice($package, null, $entity);
                $dm->persist($package);
                $dm->flush();
            } catch (\Exception $e) {

            }
        }

        //Calc package
        if($entity instanceof Package) {
            try {
                foreach ($entity->getServices() as $packageService) {
                    $packageService->setDeletedAt(new \DateTime());
                    $dm->persist($packageService);
                }
                $entity->setServicesPrice(0);
                $dm->persist($entity);
                $order = $entity->getOrder()->calcPrice($entity);
                $dm->persist($order);
                $dm->flush();
            } catch (\Exception $e) {

            }
            $this->container->get('mbh.cache')->clear('accommodation_rooms');
            $this->container->get('mbh.cache')->clear('room_cache_fetch');
        }
        if ($entity instanceof RoomCache && $entity->getPackagesCount() > 0) {

            throw new DeleteException('subscriber.cannot_delete_room_in_sale_error');
        }
    }

    /**
     * @param string $name
     * @param string $deleted
     * @param object $doc
     */
    private function removeDoc($name, $deleted, $doc)
    {
        $dm = $this->container->get('doctrine_mongodb')->getManager();
        $helper = $this->container->get('mbh.helper');
        $classes = $helper->getClassesByInterface('MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface');

        foreach ($classes as $class) {
            foreach($dm->getRepository($class)->findAll() as $config) {

                $method = 'get' . $name . 's';

                foreach($config->$method() as $object) {

                    $method = 'get' . $deleted;

                    if ($object->$method()->getId() == $doc->getId()) {

                        $method = 'remove' . $name;

                        $config->$method($object);
                        $dm->persist($config);
                    }
                }
            }
        }
        $dm->flush();
    }

    private function checkRemainsProcess(DocumentManager $dm, Task $task)
    {
        $taskRepository = $dm->getRepository('MBHHotelBundle:Task');
        $taskRepository->setContainer($this->container);

        return $taskRepository->getTaskInProcessedByRoom($task);
    }
}