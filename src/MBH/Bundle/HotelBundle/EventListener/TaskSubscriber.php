<?php

namespace MBH\Bundle\HotelBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\DocumentRepository;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use MBH\Bundle\HotelBundle\Document\Task;

/**
 * Class TaskSubscriber
 * @package MBH\Bundle\HotelBundle\EventListener
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
class TaskSubscriber implements EventSubscriber
{
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
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $document = $args->getDocument();
        if (!$document instanceof Task)
            return;

        $this->generateIncrementNumber($document, $args->getDocumentManager()->getRepository(get_class($document)));
    }

    private function generateIncrementNumber(Task $document,DocumentRepository $repository)
    {
        $result = $repository->createQueryBuilder()->select('number')->sort('createdAt', -1)->limit(1)->getQuery()->getSingleResult();
        $number = $result ? $result->getNumber() : 0;
        $document->setNumber(++$number);
    }
}