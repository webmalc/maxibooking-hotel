<?php


namespace MBH\Bundle\BaseBundle\Document;


use Doctrine\ODM\MongoDB\DocumentRepository;

class NotificationTypeRepository extends DocumentRepository
{
    public function getStuffType()
    {
        return $this->createResults(
            [
                NotificationType::OWNER_ALL,
                NotificationType::OWNER_STUFF,
            ]
        );
    }

    public function getClientType()
    {
        return $this->createResults(
            [
                NotificationType::OWNER_ALL,
                NotificationType::OWNER_CLIENT
            ]
        );
    }

    public function getErrorType()
    {
        return $this->createResults(
            [
                NotificationType::OWNER_ERROR,
            ]
        );
    }

    private function createResults(array $owners)
    {
        return $this
            ->createQueryBuilder()
            ->field('owner')
            ->in($owners)
            ->getQuery()
            ->execute();
    }
}