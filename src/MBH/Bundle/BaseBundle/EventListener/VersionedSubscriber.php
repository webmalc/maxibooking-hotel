<?php

namespace MBH\Bundle\BaseBundle\EventListener;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Event\OnFlushEventArgs;
use Doctrine\ODM\MongoDB\Events;
use Doctrine\ODM\MongoDB\PersistentCollection;
use Gedmo\Loggable\Document\LogEntry;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class VersionedSubscriber

 */
class VersionedSubscriber implements EventSubscriber
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getSubscribedEvents()
    {
        return [
//            Events::onFlush => 'onFlush'
        ];
    }

    public function onFlush(OnFlushEventArgs $args)
    {
        $dm = $args->getDocumentManager();
        $uow = $dm->getUnitOfWork();
        $reader = new AnnotationReader();
        $username = $this->container->get('security.token_storage')->getToken()->getUser()->getUsername();

        $docs = array_merge(
            $uow->getScheduledDocumentUpdates(),
            $uow->getScheduledDocumentInsertions(),
            $uow->getScheduledDocumentDeletions()
        );

        foreach ($docs as $doc) {

            $meta = $dm->getClassMetadata(get_class($doc));
            $data = [];
            foreach ($meta->getReflectionProperties() as $property) {
                if ($reader->getPropertyAnnotation($property, 'MBH\Bundle\BaseBundle\Annotations\Versioned')) {

                    $method = 'get' . ucwords($property->getName());
                    if (method_exists($doc, $method) && !empty($doc->$method())) {
                        $count = $doc->$method()->count();
                        $data = array_merge($data, [$property->getName() => $count]);
                    }
                }
            }

            if (!empty($data)) {
                $log = new LogEntry();
                $log->setAction('update');
                $log->setData($data);
                $log->setLoggedAt(new \DateTime());
                $log->setObjectId($doc->getId());
                $log->setObjectClass($meta->getName());
                $log->setVersion(1);
                $log->setUsername($username);
                $uow->persist($log);
                $uow->recomputeSingleDocumentChangeSet($dm->getClassMetadata(LogEntry::class), $log);
            }
        }
    }
}