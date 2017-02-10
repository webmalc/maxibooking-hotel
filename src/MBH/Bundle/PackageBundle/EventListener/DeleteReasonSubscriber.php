<?php
namespace MBH\Bundle\PackageBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Event\OnFlushEventArgs;
use Doctrine\ODM\MongoDB\Events;
use MBH\Bundle\PackageBundle\Document\DeleteReason;

class DeleteReasonSubscriber implements EventSubscriber
{
    public function getSubscribedEvents()
    {
        return array(
            Events::onFlush => 'onFlush'
        );
    }

    public function onFlush(OnFlushEventArgs $args)
    {
        $dm = $args->getDocumentManager();
        $uow = $dm->getUnitOfWork();

        $docs = array_merge(
            $uow->getScheduledDocumentUpdates(),
            $uow->getScheduledDocumentInsertions()
        );

        $lastEntity = $dm->getRepository('MBHPackageBundle:DeleteReason')
            ->createQueryBuilder()
            ->field('isDefault')->exists(true)->equals(true)
            ->getQuery()
            ->getSingleResult();

        foreach ($docs as $doc) {
            if ($doc instanceof DeleteReason && $doc->getIsDefault() && !is_null($lastEntity)) {
                $lastEntity->setIsDefault(false);
                $uow->recomputeSingleDocumentChangeSet($dm->getClassMetadata(get_class($lastEntity)), $lastEntity);
            }
        }
    }
}