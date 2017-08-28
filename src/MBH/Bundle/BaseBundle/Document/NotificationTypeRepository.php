<?php


namespace MBH\Bundle\BaseBundle\Document;


use Doctrine\ODM\MongoDB\DocumentRepository;

class NotificationTypeRepository extends DocumentRepository
{
    public function getClientTypes()
    {
        return $this
            ->createQueryBuilder()
            ->field('owner')
            ->in(
                [NotificationType::OWNER_ALL, NotificationType::OWNER_STUFF]
            )
            ->getQuery()
            ->execute()
            ;
    }
}