<?php

namespace MBH\Bundle\UserBundle\Document;

use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique as MongoDBUnique;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use FOS\UserBundle\Model\User as BaseUser;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use MBH\Bundle\BaseBundle\Document\Traits\BlameableDocument;
use MBH\Bundle\BaseBundle\Service\Messenger\RecipientInterface;
use MBH\Bundle\PackageBundle\Document\AddressObjectDecomposed;
use MBH\Bundle\PackageBundle\Document\DocumentRelation;
use MBH\Bundle\UserBundle\Validator\Constraints as MBHValidator;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ODM\Document(collection="Users", repositoryClass="MBH\Bundle\UserBundle\Document\UserRepository")
 * @Gedmo\Loggable
 * @MBHValidator\User
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 * @MongoDBUnique(fields="email", message="Такой e-mail уже зарегистрирован")
 * @MongoDBUnique(fields="username", message="Такой логин уже зарегистрирован")
 */
class User extends BaseUser implements RecipientInterface
{
    const ROLE_DEFAULT = 'ROLE_BASE_USER';
    const TWO_FACTOR_TYPES = ['email', 'google'];

    /**
     * @var string
     * @ODM\Id
     */
    protected $id;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string", name="firstName", type="string")
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
     * @ODM\Field(type="string", name="lastName", type="string")
     * @Assert\Length(
     *      min=2,
     *      minMessage="validator.document.user.min_surname",
     *      max=100,
     *      maxMessage="validator.document.user.max_surname"
     * )
     */
    protected $lastName;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string", name="patronymic")
     * @Assert\Length(
     *      min=2,
     *      max=100
     * )
     */
    protected $patronymic;

    /**
     * @var \DateTime
     * @Gedmo\Versioned
     * @ODM\Date(name="birthday")
     * @Assert\Date()
     */
    protected $birthday;

    /**
     * @var boolean
     * @Gedmo\Versioned
     * @ODM\Boolean()
     * @Assert\NotNull()
     * @Assert\Type(type="boolean")
     */
    protected $notifications = true;

    /**
     * @var boolean
     * @Gedmo\Versioned
     * @ODM\Boolean()
     * @Assert\NotNull()
     * @Assert\Type(type="boolean")
     */
    protected $taskNotify = true;

    /**
     * @var boolean
     * @Gedmo\Versioned
     * @ODM\Boolean()
     * @Assert\NotNull()
     * @Assert\Type(type="boolean")
     */
    protected $errors = true;


    /**
     * @var boolean
     * @Gedmo\Versioned
     * @ODM\Boolean()
     * @Assert\NotNull()
     * @Assert\Type(type="boolean")
     */
    protected $reports = true;

    /**
     * @var DocumentRelation
     * @ODM\EmbedOne(targetDocument="MBH\Bundle\PackageBundle\Document\DocumentRelation")
     */
    protected $documentRelation;

    /**
     * @var AddressObjectDecomposed
     * @ODM\EmbedOne(targetDocument="MBH\Bundle\PackageBundle\Document\AddressObjectDecomposed")
     */
    protected $addressObjectDecomposed;

    /**
     * @ODM\ReferenceMany(targetDocument="Group")
     */
    protected $groups;

    /**
     * @var boolean
     * @Gedmo\Versioned
     * @ODM\Boolean()
     * @Assert\Type(type="boolean")
     */
    protected $defaultNoticeDoc = false;

    /**
     * @var boolean
     * @Gedmo\Versioned
     * @ODM\Boolean()
     * @Assert\NotNull()
     * @Assert\Type(type="boolean")
     */
    protected $isEnabledWorkShift = false;

    /**
     * @var string $twoFactorAuthentication google or email
     * @ODM\Field(type="string")
     * @Assert\Choice(callback = "getTwoFactorTypes")
     */
    protected $twoFactorAuthentication;

    /**
     * @var string $googleAuthenticatorCode Stores the secret code
     * @ODM\Field(type="string")
     */
    private $googleAuthenticatorCode;

    /**
     * @var integer $twoFactorCode Current authentication code
     * @ODM\Field(type="integer")
     */
    protected $twoFactorCode;

    /**
     * @ODM\Field(type="boolean")
     */
    protected $locked;

    /** @var \DateTime
     * @ODM\Field(type="date")
     * @Assert\DateTime()
     */
    protected $expiresAt;

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
     * @return mixed
     */
    public function isLocked()
    {
        return $this->locked;
    }

    /**
     * @param mixed $locked
     */
    public function setLocked($locked)
    {
        $this->locked = $locked;
    }

    /**
     * @return \DateTime
     */
    public function getExpiresAt()
    {
        return $this->expiresAt;
    }

    public function setExpiresAt($dateTime)
    {
        $this->expiresAt = $dateTime;

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
     * Get lastName
     *
     * @return string $lastName
     */
    public function getLastName()
    {
        return $this->lastName;
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
     * @return string
     */
    public function getPatronymic()
    {
        return $this->patronymic;
    }

    /**
     * @param string $patronymic
     */
    public function setPatronymic($patronymic)
    {
        $this->patronymic = $patronymic;
    }

    /**
     * @return \DateTime
     */
    public function getBirthday()
    {
        return $this->birthday;
    }

    /**
     * @param \DateTime $birthday
     */
    public function setBirthday($birthday)
    {
        $this->birthday = $birthday;
    }

    public function __toString()
    {
        return $this->username;
    }

    public function getName()
    {
        return $this->getFullName(true);
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
     * Get emailNotifications
     *
     * @return boolean $emailNotifications
     */
    public function getNotifications()
    {
        return $this->notifications;
    }

    /**
     * Set emailNotifications
     *
     * @param boolean $notifications
     * @return self
     */
    public function setNotifications($notifications)
    {
        $this->notifications = $notifications;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getTaskNotify()
    {
        return $this->taskNotify;
    }

    /**
     * @return boolean
     */
    public function isTaskNotify()
    {
        return $this->taskNotify;
    }

    /**
     * @param boolean $taskNotify
     */
    public function setTaskNotify($taskNotify)
    {
        $this->taskNotify = $taskNotify;
    }

    /**
     * Get emailErrors
     *
     * @return boolean $emailErrors
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Set emailErrors
     *
     * @param boolean $errors
     * @return self
     */
    public function setErrors($errors)
    {
        $this->errors = $errors;
        return $this;
    }

    /**
     * Get emailReports
     *
     * @return boolean $emailReports
     */
    public function getReports()
    {
        return $this->reports;
    }

    /**
     * Set emailReports
     *
     * @param boolean $reports
     * @return self
     */
    public function setReports($reports)
    {
        $this->reports = $reports;
        return $this;
    }

    /**
     * @return DocumentRelation
     */
    public function getDocumentRelation()
    {
        return $this->documentRelation;
    }

    /**
     * @param DocumentRelation $documentRelation
     */
    public function setDocumentRelation(DocumentRelation $documentRelation = null)
    {
        $this->documentRelation = $documentRelation;
    }

    /**
     * @return AddressObjectDecomposed
     */
    public function getAddressObjectDecomposed()
    {
        return $this->addressObjectDecomposed;
    }

    /**
     * @param AddressObjectDecomposed $addressObjectDecomposed
     */
    public function setAddressObjectDecomposed(AddressObjectDecomposed $addressObjectDecomposed = null)
    {
        $this->addressObjectDecomposed = $addressObjectDecomposed;
    }

    /**
     * @return array
     */
    public function getRolesWithoutGroups()
    {
        return $this->roles;
    }

    /**
     * @param $roles
     * @return $this
     */
    public function setRolesWithoutGroups($roles)
    {
        $this->setRoles($roles);
    }


    function equals(UserInterface $user){
        return false;
    }

    /**
     * @return string|null
     */
    public function getCommunicationLanguage()
    {
        return null;
    }

    /**
     * @return boolean
     */
    public function getIsEnabledWorkShift()
    {
        return $this->isEnabledWorkShift;
    }

    /**
     * @param boolean $isEnabledWorkShift
     */
    public function setIsEnabledWorkShift($isEnabledWorkShift)
    {
        $this->isEnabledWorkShift = $isEnabledWorkShift;
    }

    /**
     * @return boolean
     */
    public function getDefaultNoticeDoc()
    {
        return $this->defaultNoticeDoc;
    }

    /**
     * @param boolean $defaultNoticeDoc
     */
    public function setDefaultNoticeDoc($defaultNoticeDoc)
    {
        $this->defaultNoticeDoc = $defaultNoticeDoc;
    }

    /**
     * @return string
     */
    public function getTwoFactorAuthentication()
    {
        return $this->twoFactorAuthentication;
    }

    /**
     * @param string $twoFactorAuthentication
     * @return User
     */
    public function setTwoFactorAuthentication($twoFactorAuthentication): User
    {
        $this->twoFactorAuthentication = $twoFactorAuthentication;

        return $this;
    }

    /**
     * @return int
     */
    public function getTwoFactorCode(): int
    {
        return $this->twoFactorCode;
    }

    /**
     * @param int $twoFactorCode
     * @return User
     */
    public function setTwoFactorCode(int $twoFactorCode): User
    {
        $this->twoFactorCode = $twoFactorCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getGoogleAuthenticatorCode()
    {
        return $this->googleAuthenticatorCode;
    }

    /**
     * @param string $googleAuthenticatorCode
     * @return User
     */
    public function setGoogleAuthenticatorCode(string $googleAuthenticatorCode): User
    {
        $this->googleAuthenticatorCode = $googleAuthenticatorCode;
        return $this;
    }

    /**
     * @return array
     */
    public static function getTwoFactorTypes(): array
    {
        return self::TWO_FACTOR_TYPES;
    }
}
