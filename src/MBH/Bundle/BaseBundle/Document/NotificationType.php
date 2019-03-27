<?php


namespace MBH\Bundle\BaseBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique as MongoDBUnique;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use MBH\Bundle\BaseBundle\Document\Traits\BlameableDocument;

/**
 * @ODM\Document(collection="NotificationType", repositoryClass="NotificationTypeRepository")
 * @Gedmo\Loggable
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 * @MongoDBUnique(fields="type", message="validator.document.table.unique")
 */
class NotificationType extends Base
{
    public const ONLINE_ORDER_TYPE = 'online_order';
    public const CHANNEL_MANAGER_TYPE = 'channel_manager_order';
    public const CHANNEL_MANAGER_ERROR_TYPE = 'channel_manager_error';
    public const CHANNEL_MANAGER_CONFIGURATION_TYPE = 'channel_manager_configuration';
    public const CASH_DOC_CONFIRMATION_TYPE = 'cash_confirm';
    public const ONLINE_PAYMENT_CONFIRM_TYPE = 'online_payment_confirm';
    public const ARRIVAL_TYPE = 'arrival';
    public const FEEDBACK_TYPE = 'feedback';
    public const UNPAID_TYPE = 'unpaid';
    public const CONFIRM_ORDER_TYPE = 'confirm_order';
    public const TASK_TYPE = 'task';
    public const EMAIL_RESETTING_TYPE = 'email_resetting';
    public const ERROR = 'error';
    public const AUTH_TYPE = 'auth';
    public const TECH_SUPPORT_TYPE = 'tech-support';

    public const OWNER_STUFF = 'stuff';
    public const OWNER_CLIENT = 'client';
    public const OWNER_ALL = 'all';

    /**
     * Hook timestampable behavior
     * updates createdAt, updatedAt fields
     */
    use TimestampableDocument;

    /**
     * Hook softdeleteable behavior
     * deletedAt field
     */
    use SoftDeleteableDocument;

    /**
     * Hook blameable behavior
     * createdBy&updatedBy fields
     */
    use BlameableDocument;

    /**
     * @var string
     * @ODM\Field(type="string")
     * @Assert\Choice(
     *     callback={"getNotificationTypes"}
     * )
     */
    protected $type;

    /**
     * @var string
     * @ODM\Field(type="string")
     * @Assert\Choice(
     *     callback={"getOwners"}
     * )
     */
    protected $owner;

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return NotificationType
     */
    public function setType(string $type): NotificationType
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getOwner(): string
    {
        return $this->owner;
    }

    /**
     * @param string $owner
     * @return NotificationType
     */
    public function setOwner(string $owner): NotificationType
    {
        $this->owner = $owner;

        return $this;
    }


    public static function getNotificationTypes(): array
    {
        return [
            self::ONLINE_ORDER_TYPE,
            self::CHANNEL_MANAGER_TYPE,
            self::CHANNEL_MANAGER_ERROR_TYPE,
            self::CASH_DOC_CONFIRMATION_TYPE,
            self::ONLINE_PAYMENT_CONFIRM_TYPE,
            self::ARRIVAL_TYPE,
            self::FEEDBACK_TYPE,
            self::UNPAID_TYPE,
            self::CONFIRM_ORDER_TYPE,
            self::TASK_TYPE,
            self::EMAIL_RESETTING_TYPE,
            self::ERROR
        ];
    }

    public static function getOwners(): array
    {
        return [
            self::OWNER_ALL,
            self::OWNER_CLIENT,
            self::OWNER_STUFF
        ];
    }

    public static function getSystemNotificationTypes()
    {
        return [
            self::EMAIL_RESETTING_TYPE,
            self::AUTH_TYPE,
            self::TECH_SUPPORT_TYPE
        ];
    }
}