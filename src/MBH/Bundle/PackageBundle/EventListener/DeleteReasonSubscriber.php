<?php

namespace MBH\Bundle\PackageBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\ODM\MongoDB\Event\OnFlushEventArgs;
use Doctrine\ODM\MongoDB\Event\PreUpdateEventArgs;
use Doctrine\ODM\MongoDB\Events;
use MBH\Bundle\PackageBundle\Document\DeleteReason;

class DeleteReasonSubscriber implements EventSubscriber
{
    public function getSubscribedEvents()
    {
        return [
            Events::preUpdate => 'preUpdate',
            Events::prePersist => 'prePersist'
        ];
    }

    public function preUpdate(PreUpdateEventArgs $args)
    {
        $doc = $args->getDocument();
        if ($doc instanceof DeleteReason && $args->hasChangedField('isDefault') && $doc->getIsDefault()) {
            $this->setOtherReasonsNotDefault($args, $doc);
        }
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        if ($args->getDocument() instanceof DeleteReason && $args->getDocument()->getIsDefault()) {
            $this->setOtherReasonsNotDefault($args, $args->getDocument());
        }
    }

    /**
     * @param LifecycleEventArgs $args
     * @param DeleteReason $reason
     */
    private function setOtherReasonsNotDefault(LifecycleEventArgs $args, DeleteReason $reason): void
    {
        $dm = $args->getDocumentManager();
        $meta = $dm->getClassMetadata(DeleteReason::class);

        $defaultReasons = $args
            ->getDocumentManager()
            ->getRepository('MBHPackageBundle:DeleteReason')
            ->findBy(['isDefault' => true, 'id' => ['$ne' => $reason->getId()]]);
        foreach ($defaultReasons as $defaultReason) {
            $defaultReason->setIsDefault(false);
            $dm->getUnitOfWork()->computeChangeSet($meta, $defaultReason);
        }
    }
}
