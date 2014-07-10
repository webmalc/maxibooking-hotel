<?php

namespace MBH\Bundle\PackageBundle\Document;

use MBH\Bundle\BaseBundle\Document\Base;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Blameable\Traits\BlameableDocument;

/**
 * @ODM\Document(collection="Tourists")
 * @Gedmo\Loggable
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 * @ODM\HasLifecycleCallbacks
 */
class Tourist extends Base
{
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
     * @ODM\ReferenceMany(targetDocument="Package", mappedBy="tourists")
     */
    public $packages;
    
    /**
     * @ODM\ReferenceMany(targetDocument="Package", mappedBy="tourist")
     */
    public $mainPackages;
    
    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String(name="firstName")
     * @Assert\NotNull(message="Имя обязательно для заполнения")
     * @Assert\Length(
     *      min=2,
     *      minMessage="Слишком короткое имя",
     *      max=100,
     *      maxMessage="Слишком длинное имя"
     * )
     */
    protected $firstName;
    
    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String(name="lastName")
     * @Assert\NotNull(message="Фамилия обязательна для заполнения")
     * @Assert\Length(
     *      min=2,
     *      minMessage="Слишком короткая фамилия",
     *      max=100,
     *      maxMessage="Слишком длинная фамилия"
     * )
     */
    protected $lastName;
    
    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String(name="patronymic")
     * @Assert\Length(
     *      min=2,
     *      minMessage="Слишком короткое отчество",
     *      max=100,
     *      maxMessage="Слишком длинное отчество"
     * )
     */
    protected $patronymic;
    
    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String(name="fullName")
     */
    protected $fullName;
    
    /**
     * @var \DateTime
     * @Gedmo\Versioned
     * @ODM\Date(name="birthday")
     * @Assert\Date()
     */
    protected $birthday;
    
    /**
     * @var \DateTime
     * @Gedmo\Versioned
     * @ODM\String(name="sex")
     * @Assert\Choice(
     *      choices = {"male", "female", "unknown"}, 
     *      message = "Неверный пол."
     * )
     */
    protected $sex = 'unknown';
    
    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String(name="address")
     * @Assert\Length(
     *      min=2,
     *      minMessage="Слишком короткий адрес",
     *      max=100,
     *      maxMessage="Слишком длинный адрес"
     * )
     */
    protected $address;
    
    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String(name="document")
     * @Assert\Length(
     *      min=2,
     *      minMessage="Слишком короткий документ",
     *      max=100,
     *      maxMessage="Слишком длинный документ"
     * )
     */
    protected $document;
    
    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String(name="phone")
     * @Assert\Length(
     *      min=2,
     *      minMessage="Слишком короткий телефон",
     *      max=100,
     *      maxMessage="Слишком длинный телефон"
     * )
     */
    protected $phone;
    
    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String(name="email")
     * @Assert\Email()
     */
    protected $email;
    
    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String(name="note")
     * @Assert\Length(
     *      min=2,
     *      minMessage="Слишком короткое примечание",
     *      max=100,
     *      maxMessage="Слишком длинное примечание"
     * )
     */
    protected $note;

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
     * @param string $fullName
     * @return self
     */
    public function setFullName($fullName)
    {
        $this->fullName = $fullName;
        return $this;
    }

    /**
     * @return string $fullName
     */
    public function getFullName()
    {
        return $this->fullName;
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
     * Set patronymic
     *
     * @param string $patronymic
     * @return self
     */
    public function setPatronymic($patronymic)
    {
        $this->patronymic = $patronymic;
        return $this;
    }

    /**
     * Get patronymic
     *
     * @return string $patronymic
     */
    public function getPatronymic()
    {
        return $this->patronymic;
    }

    /**
     * Set birthday
     *
     * @param date $birthday
     * @return self
     */
    public function setBirthday($birthday)
    {
        $this->birthday = $birthday;
        return $this;
    }

    /**
     * Get birthday
     *
     * @return date $birthday
     */
    public function getBirthday()
    {
        return $this->birthday;
    }

    /**
     * Set sex
     *
     * @param date $sex
     * @return self
     */
    public function setSex($sex)
    {
        $this->sex = $sex;
        return $this;
    }

    /**
     * Get sex
     *
     * @return date $sex
     */
    public function getSex()
    {
        return $this->sex;
    }

    /**
     * Set address
     *
     * @param string $address
     * @return self
     */
    public function setAddress($address)
    {
        $this->address = $address;
        return $this;
    }

    /**
     * Get address
     *
     * @return string $address
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set document
     *
     * @param string $document
     * @return self
     */
    public function setDocument($document)
    {
        $this->document = $document;
        return $this;
    }

    /**
     * Get document
     *
     * @return string $document
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * Set phone
     *
     * @param string $phone
     * @return self
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
        return $this;
    }

    /**
     * Get phone
     *
     * @return string $phone
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return self
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * Get email
     *
     * @return string $email
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set note
     *
     * @param string $note
     * @return self
     */
    public function setNote($note)
    {
        $this->note = $note;
        return $this;
    }

    /**
     * Get note
     *
     * @return string $note
     */
    public function getNote()
    {
        return $this->note;
    }
    
    /**
     * Gender guess
     * @return "unknown"|"male"|"female"
     */
    public function guessGender()
    {
        $end = mb_substr($this->getPatronymic(), -2, 2, 'UTF-8');
        
        $gender = 'unknown';
        if ($end == 'ич' || $end == 'лы') {
            $gender = 'male';
        }
        if ($end == 'на' || $end == 'зы') {
            $gender = 'female';
        }
        
        return $gender;
    }
    
    /**
     * @ODM\PrePersist
     */
    public function prePersist()
    {
        if(empty($this->sex) || $this->sex  == 'unknown') {
            $this->sex = $this->guessGender();
        }
        
        $this->fullName = $this->generateFullName();
    }
    
    /**
     * @ODM\preUpdate
     */
    public function preUpdate()
    {
        $this->fullName = $this->generateFullName();
    }
    
    /**
     * @return string
     */
    public function generateFullName()
    {
        $name = $this->lastName . ' ' . $this->firstName;
        
        return (empty($this->patronymic)) ? $name : $name . ' ' . $this->patronymic;
    }

    public function __construct()
    {
        $this->packages = new \Doctrine\Common\Collections\ArrayCollection();
        $this->mainPackages = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add package
     *
     * @param MBH\Bundle\PackageBundle\Document\Package $package
     */
    public function addPackage(\MBH\Bundle\PackageBundle\Document\Package $package)
    {
        $this->packages[] = $package;
    }

    /**
     * Remove package
     *
     * @param MBH\Bundle\PackageBundle\Document\Package $package
     */
    public function removePackage(\MBH\Bundle\PackageBundle\Document\Package $package)
    {
        $this->packages->removeElement($package);
    }

    /**
     * Get packages
     *
     * @return Doctrine\Common\Collections\Collection $packages
     */
    public function getPackages()
    {
        return $this->packages;
    }

    /**
     * Add mainPackage
     *
     * @param MBH\Bundle\PackageBundle\Document\Package $mainPackage
     */
    public function addMainPackage(\MBH\Bundle\PackageBundle\Document\Package $mainPackage)
    {
        $this->mainPackages[] = $mainPackage;
    }

    /**
     * Remove mainPackage
     *
     * @param MBH\Bundle\PackageBundle\Document\Package $mainPackage
     */
    public function removeMainPackage(\MBH\Bundle\PackageBundle\Document\Package $mainPackage)
    {
        $this->mainPackages->removeElement($mainPackage);
    }

    /**
     * Get mainPackages
     *
     * @return Doctrine\Common\Collections\Collection $mainPackages
     */
    public function getMainPackages()
    {
        return $this->mainPackages;
    }

    /**
     * Get id
     *
     * @return id $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set createdAt
     *
     * @param date $createdAt
     * @return self
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * Get createdAt
     *
     * @return date $createdAt
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param date $updatedAt
     * @return self
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return date $updatedAt
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set deletedAt
     *
     * @param date $deletedAt
     * @return self
     */
    public function setDeletedAt($deletedAt)
    {
        $this->deletedAt = $deletedAt;
        return $this;
    }

    /**
     * Get deletedAt
     *
     * @return date $deletedAt
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    /**
     * Set createdBy
     *
     * @param string $createdBy
     * @return self
     */
    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;
        return $this;
    }

    /**
     * Get createdBy
     *
     * @return string $createdBy
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set updatedBy
     *
     * @param string $updatedBy
     * @return self
     */
    public function setUpdatedBy($updatedBy)
    {
        $this->updatedBy = $updatedBy;
        return $this;
    }

    /**
     * Get updatedBy
     *
     * @return string $updatedBy
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }
}
