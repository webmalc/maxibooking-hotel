<?php

namespace MBH\Bundle\BaseBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\ODM\MongoDB\Events;
use MBH\Bundle\BaseBundle\Document\Traits\InternableDocument;
use MBH\Bundle\BaseBundle\Service\Helper;

/**
 * Class GenerateInternationalListener
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
class GenerateInternationalListener implements EventSubscriber
{
    public function getSubscribedEvents()
    {
        return [
            Events::preUpdate => 'preUpdate'
        ];
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        try {
            $document = $args->getDocument();
            $traits = class_uses($document);

            if (in_array(InternableDocument::class, $traits) && !$document->getInternationalTitle()) {
                /** @var InternableDocument $document */
                $document->setInternationalTitle(Helper::translateToLat($document->getFullTitle()));
                $meta = $args->getDocumentManager()->getClassMetadata(get_class($document));
                $args->getDocumentManager()->getUnitOfWork()->recomputeSingleDocumentChangeSet($meta, $document);
            };
        } catch (\Exception $e) {
        }
    }
}