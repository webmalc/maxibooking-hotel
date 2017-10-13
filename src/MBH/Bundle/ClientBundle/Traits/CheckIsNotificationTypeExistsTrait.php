<?php


namespace MBH\Bundle\ClientBundle\Traits;


use Doctrine\ODM\MongoDB\PersistentCollection;
use MBH\Bundle\BaseBundle\Document\NotificationType;

trait CheckIsNotificationTypeExistsTrait
{
    /**
     * @param string $notificationTypeName
     * @return bool
     */
    public function isNotificationTypeExists(string $notificationTypeName): bool
    {
        $result = false;
        $types = $this->allowNotificationTypes;
        if ($types instanceof PersistentCollection) {
            $result = $types->filter(function($type) use ($notificationTypeName) {
                /** @var NotificationType $type */
                return $type->getType() === $notificationTypeName;
            });
        }

        return (bool)$result;
    }
}