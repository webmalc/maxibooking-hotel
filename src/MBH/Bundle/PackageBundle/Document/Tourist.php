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
 * @ODM\Document(collection="Tourists", repositoryClass="MBH\Bundle\PackageBundle\Document\TouristRepository")
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
     * @ODM\ReferenceMany(targetDocument="Order", nullable="true", mappedBy="mainTourist")
     */
    public $orders;

    /**
     * @ODM\ReferenceMany(targetDocument="Package", nullable="true", mappedBy="tourists")
     */
    public $packages;
    
    /**
     * @ODM\ReferenceMany(targetDocument="Package", nullable="true", mappedBy="mainTourist")
     */
    public $mainPackages;

    /** @ODM\ReferenceMany(targetDocument="MBH\Bundle\CashBundle\Document\CashDocument", mappedBy="payer") */
    protected $cashDocuments;
    
    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String(name="firstName")
     * @Assert\NotNull(message= "validator.document.Tourist.name_required")
     * @Assert\Length(
     *      min=2,
     *      minMessage= "validator.document.Tourist.min_name",
     *      max=100,
     *      maxMessage= "validator.document.Tourist.max_name"
     * )
     */
    protected $firstName;
    
    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String(name="lastName")
     * @Assert\NotNull(message= "validator.document.Tourist.surname_required")
     * @Assert\Length(
     *      min=2,
     *      minMessage= "validator.document.Tourist.min_surname",
     *      max=100,
     *      maxMessage= "validator.document.Tourist.max_surname"
     * )
     */
    protected $lastName;
    
    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String(name="patronymic")
     * @Assert\Length(
     *      min=2,
     *      minMessage= "validator.document.Tourist.min_second_name",
     *      max=100,
     *      maxMessage= "validator.document.Tourist.max_second_name"
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
     * @var \string
     * @Gedmo\Versioned
     * @ODM\String(name="sex")
     * @Assert\Choice(
     *      choices = {"male", "female", "unknown"}, 
     *      message =  "validator.document.Tourist.wrong_gender"
     * )
     */
    protected $sex = 'unknown';
    
    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String(name="address")
     * @Assert\Length(
     *      min=2,
     *      minMessage= "validator.document.Tourist.min_address",
     *      max=100,
     *      maxMessage= "validator.document.Tourist.max_address"
     * )
     */
    protected $address;
    
    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String(name="document")
     * @Assert\Length(
     *      min=2,
     *      minMessage= "validator.document.Tourist.min_document",
     *      max=100,
     *      maxMessage= "validator.document.Tourist.max_document"
     * )
     */
    protected $document;
    
    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String(name="phone")
     * @Assert\Length(
     *      min=2,
     *      minMessage= "validator.document.Tourist.min_phone",
     *      max=100,
     *      maxMessage= "validator.document.Tourist.max_phone"
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
     *      minMessage= "validator.document.Tourist.min_note",
     *      max=100,
     *      maxMessage= "validator.document.Tourist.max_note"
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
        if ($this->firstName == 'н/д') {
            return '';
        }
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
        if ($this->lastName == 'н/д') {
            return '';
        }

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
     * @param \DateTime $birthday
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
     * @param string $sex
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
        $name = $this->getLastName() . ' ' . $this->getFirstName();
        
        return (empty($this->getPatronymic())) ? $name : $name . ' ' . $this->getPatronymic();
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
     * Get age
     * @return int 
     */
    public function getAge()
    {
        if ($this->getBirthday()) {
            $now = new \DateTime();
            $diff = $now->diff($this->getBirthday());
            
            return $diff->y;
        }
        
        return null;
    }

    public function __toString()
    {
        return $this->getFullName();
    }

    /**
     * Add cashDocument
     *
     * @param \MBH\Bundle\CashBundle\Document\CashDocument $cashDocument
     */
    public function addCashDocument(\MBH\Bundle\CashBundle\Document\CashDocument $cashDocument)
    {
        $this->cashDocuments[] = $cashDocument;
    }

    /**
     * Remove cashDocument
     *
     * @param \MBH\Bundle\CashBundle\Document\CashDocument $cashDocument
     */
    public function removeCashDocument(\MBH\Bundle\CashBundle\Document\CashDocument $cashDocument)
    {
        $this->cashDocuments->removeElement($cashDocument);
    }

    /**
     * Get cashDocuments
     *
     * @return \Doctrine\Common\Collections\Collection $cashDocuments
     */
    public function getCashDocuments()
    {
        return $this->cashDocuments;
    }

    public function getLastNameWithInitials()
    {
        $result = $this->getLastName() . ' ' . mb_substr($this->getFirstName(), 0, 1) . '.';

        if (!empty($this->getPatronymic())) {
            $result .= mb_substr($this->getPatronymic(), 0, 1) . '.';
        }

        return $result;
    }

    /**
     * Add order
     *
     * @param \MBH\Bundle\PackageBundle\Document\Order $order
     */
    public function addOrder(\MBH\Bundle\PackageBundle\Document\Order $order)
    {
        $this->orders[] = $order;
    }

    /**
     * Remove order
     *
     * @param \MBH\Bundle\PackageBundle\Document\Order $order
     */
    public function removeOrder(\MBH\Bundle\PackageBundle\Document\Order $order)
    {
        $this->orders->removeElement($order);
    }

    /**
     * Get orders
     *
     * @return \Doctrine\Common\Collections\Collection $orders
     */
    public function getOrders()
    {
        return $this->orders;
    }
}
