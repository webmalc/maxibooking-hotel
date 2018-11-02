<?php

namespace MBH\Bundle\BaseBundle\EventListener\OnRemoveSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\ODM\MongoDB\Events;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use MBH\Bundle\BaseBundle\Document\Base;
use MBH\Bundle\PackageBundle\Lib\DeleteException;

use Symfony\Component\DependencyInjection\ContainerInterface;

class RemoveSubscriber implements EventSubscriber
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

    /**
     * @param LifecycleEventArgs $args
     * @throws DeleteException
     */
    public function preRemove(LifecycleEventArgs $args)
    {
        /** @var Base $document */
        $document = $args->getDocument();

        if ($this->container->get('mbh.helper')->hasDocumentClassTrait(SoftDeleteableDocument::class, $document)
            && !empty($document->getDeletedAt())
        ) {
            throw new DeleteException('exception.relation_delete.document_already_deleted');
        }

        $relatedDocumentsData = $this->container
            ->get('mbh.helper')
            ->getRelatedDocuments($document);

        foreach ($relatedDocumentsData as $relatedDocumentData) {
            $quantity = $relatedDocumentData['quantity'];
            /** @var Relationship $relation */
            $relation = $relatedDocumentData['relation'];

            if ($quantity > 0) {
                $message = $relation->getErrorMessage() ? $relation->getErrorMessage() : 'exception.relation_delete.message'; // have existing relation
                throw new DeleteException($message, $quantity);
            }
        }
    }
}