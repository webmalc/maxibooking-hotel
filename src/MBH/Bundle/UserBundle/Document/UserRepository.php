<?php


namespace MBH\Bundle\UserBundle\Document;


use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\BaseBundle\Document\NotificationType;

/**
 * Class UserRepository
 * @package MBH\Bundle\UserBundle\Document
 */
class UserRepository extends DocumentRepository
{

    /**
     * @param string $notificationType
     * @return mixed
     */
    public function getRecipients(string $notificationTypeName)
    {
        $notificationType = $this->getNotificationType($notificationTypeName);
        $qb = $this->createQueryBuilder();
        $qb
            ->field('enabled')->equals(true)
            ->field('locked')->equals(false)
            ->field('username')->notEqual('mb');
        /** If notificationType is not exists, return all recipients */
        if ($notificationType instanceof NotificationType) {
            $qb->field('allowNotificationTypes')->includesReferenceTo($notificationType);
        }

        return $qb->getQuery()->execute();

    }

    /**
     * @param string $notificationTypeName
     * @return NotificationType|null|object
     */
    private function getNotificationType(string $notificationTypeName)
    {
        return $this->getDocumentManager()
            ->getRepository('MBHBaseBundle:NotificationType')
            ->findOneBy(['type' => $notificationTypeName]);
    }
}