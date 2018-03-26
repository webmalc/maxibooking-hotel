<?php

namespace MBH\Bundle\PackageBundle\EventListener;


use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use MBH\Bundle\PackageBundle\Document\PackageAccommodation;
use MBH\Bundle\PackageBundle\Lib\DeleteException;

class PackageAccommodationSubscriber implements EventSubscriber
{
    public function getSubscribedEvents()
    {
        return array(
            'preRemove'
        );
    }

    public function preRemove(LifecycleEventArgs $eventArgs)
    {
        $dm = $eventArgs->getDocumentManager();
        $filter = $dm->getFilterCollection()->isEnabled('softdeleteable');
        if (!$filter) {
            $dm->getFilterCollection()->enable('softdeleteable');
        }

        $document = $eventArgs->getDocument();
        if ($document instanceof PackageAccommodation) {
            $package = $dm->getRepository('MBHPackageBundle:Package')
                ->getPackageByPackageAccommodationId($document->getId());
            $package->removeAccommodation($document);
            $class = $dm->getClassMetadata(get_class($package));
            $dm->getUnitOfWork()->recomputeSingleDocumentChangeSet($class, $package);
        }

        if (!$filter && $dm->getFilterCollection()->enable('softdeleteable')) {
            $dm->getFilterCollection()->disable('softdeleteable');
        }
    }

}