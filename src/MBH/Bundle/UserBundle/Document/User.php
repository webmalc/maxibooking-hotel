<?php

namespace MBH\Bundle\UserBundle\Document;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique as MongoDBUnique;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Blameable\Traits\BlameableDocument;

/**
 * @ODM\Document(collection="Users")
 * @Gedmo\Loggable
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 * @MongoDBUnique(fields="email", message="Такой e-mail уже зарегистрирован")
 * @MongoDBUnique(fields="username", message="Такой логин уже зарегистрирован")
 */
class User extends BaseUser
{
    /**
     * @var string
     * @ODM\Id
     */
    protected $id;
    
    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String(name="firstName", type="string")
     * @Assert\Length(
     *      min=2,
     *      minMessage="validator.document.user.min_name",
     *      max=100,
     *      maxMessage="validator.document.user.max_name"
     * )
     */
    protected $firstName;
    
    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String(name="lastName", type="string")
     * @Assert\Length(
     *      min=2,
     *      minMessage="validator.document.user.min_surname",
     *      max=100,
     *      maxMessage="validator.document.user.max_surname"
     * )
     */
    protected $lastName;

    /**
     * @var boolean
     * @Gedmo\Versioned
     * @ODM\Boolean()
     * @Assert\NotNull()
     * @Assert\Type(type="boolean")
     */
    protected $emailNotifications = true;
    
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
    
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Set firstName
     *
     * @param string $firstName
     * @return self
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
        return $this;
    }

    /**
     * Get firstName
     *
     * @return string $firstName
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set lastName
     *
     * @param string $lastName
     * @return self
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
        return $this;
    }

    /**
     * Get lastName
     *
     * @return string $lastName
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Return user full name
     * @param boolean $full
     * @return string
     */
    public function getFullName($full = false)
    {
        $username = $this->username;
        
        if (!empty($this->firstName)) {
            
            $username = $this->firstName;
            
            if (!empty($this->lastName) && $full) {
                $username = $this->lastName . ' ' . $this->firstName;
            }
        }
        
        return $username;
    }

    /**
     * Set emailNotifications
     *
     * @param boolean $emailNotifications
     * @return self
     */
    public function setEmailNotifications($emailNotifications)
    {
        $this->emailNotifications = $emailNotifications;
        return $this;
    }

    /**
     * Get emailNotifications
     *
     * @return boolean $emailNotifications
     */
    public function getEmailNotifications()
    {
        return $this->emailNotifications;
    }
}
