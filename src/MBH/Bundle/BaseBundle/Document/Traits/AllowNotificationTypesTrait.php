<?php


namespace MBH\Bundle\BaseBundle\Document\Traits;


use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

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


}