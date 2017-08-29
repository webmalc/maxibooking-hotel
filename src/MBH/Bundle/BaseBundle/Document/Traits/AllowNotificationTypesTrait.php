<?php


namespace MBH\Bundle\BaseBundle\Document\Traits;


use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Doctrine\ODM\MongoDB\PersistentCollection;
use MBH\Bundle\BaseBundle\Document\NotificationType;

trait AllowNotificationTypesTrait
{
    /**
     * @var  array
     * @ODM\Field(type="collection")
     * @ODM\ReferenceMany(targetDocument="MBH\Bundle\BaseBundle\Document\NotificationType")
     */
    protected $allowNotificationTypes;

    /**
     * @return array
     */
    public function getAllowNotificationTypes()
    {
        return $this->allowNotificationTypes;
    }

    /**
     * @param array $allowNotificationTypes
     * @return AllowNotificationTypesTrait
     */
    public function setAllowNotificationTypes(array $allowNotificationTypes = [])
    {
        $this->allowNotificationTypes = $allowNotificationTypes;

        return $this;
    }

    /**
     * @param string $notificationTypeName
     * @return bool
     */
    public function isNotificationTypeExists(string $notificationTypeName = null): bool
    {
        $result = false;
        $types = $this->allowNotificationTypes;
        if ($types instanceof PersistentCollection) {
            $result = $types->filter(function($type) use ($notificationTypeName) {
                /** @var NotificationType $type */
                return $type->getType() === $notificationTypeName;
            });
        }

        return (bool)count($result);
    }


}