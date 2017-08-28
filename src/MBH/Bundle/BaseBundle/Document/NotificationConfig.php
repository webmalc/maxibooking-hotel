<?php


namespace MBH\Bundle\BaseBundle\Document;


use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use MBH\Bundle\BaseBundle\Document\Traits\BlameableDocument;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use MBH\Bundle\BaseBundle\Lib\MessageTypes;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ODM\Document(collection="NotificationConfig", repositoryClass="MBH\Bundle\BaseBundle\Document\NotificationConfigRepository")
 * @Gedmo\Loggable
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class NotificationConfig extends Base
{
    const RECEIVER_CLIENT = 'client';
    const RECEIVER_STUFF = 'stuff';

    const RECEIVERS_GROUP = [
        self::RECEIVER_CLIENT,
        self::RECEIVER_STUFF,
    ];

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
     * @var  array
     * @ODM\Field(type="collection")
     * @Assert\Choice(
     *     callback={"MBH\Bundle\BaseBundle\Lib\MessageTypes","getStuffOptionsList"},
     *     multiple=true
     *     )
     */
    protected $email_stuff = MessageTypes::STUFF_GROUP;

    /**
     * @var  array
     * @ODM\Field(type="collection")
     * @Assert\Choice(
     *     callback={"MBH\Bundle\BaseBundle\Lib\MessageTypes","getClientOptionsList"},
     *     multiple=true
     * )
     */
    protected $email_client = MessageTypes::CLIENT_GROUP;

    /**
     * @return array
     */
    public function getEmailStuff(): array
    {
        return $this->email_stuff;
    }

    /**
     * @param array $email_stuff
     * @return NotificationConfig
     */
    public function setEmailStuff(array $email_stuff): NotificationConfig
    {
        $this->email_stuff = $email_stuff;

        return $this;
    }

    /**
     * @return array
     */
    public function getEmailClient(): array
    {
        return $this->email_client;
    }

    /**
     * @param array $email_client
     * @return NotificationConfig
     */
    public function setEmailClient(array $email_client): NotificationConfig
    {
        $this->email_client = $email_client;

        return $this;
    }


}