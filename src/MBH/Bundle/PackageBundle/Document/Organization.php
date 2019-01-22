<?php

namespace MBH\Bundle\PackageBundle\Document;

use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique as MongoDBUnique;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Doctrine\ODM\MongoDB\Mapping\Annotations\PrePersist;
use Doctrine\ODM\MongoDB\Mapping\Annotations\PreUpdate;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use MBH\Bundle\BaseBundle\Document\ProtectedFile;
use MBH\Bundle\BaseBundle\Document\Traits\BlameableDocument;
use MBH\Bundle\BaseBundle\Service\Messenger\RecipientInterface;
use MBH\Bundle\HotelBundle\Document\City;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\PackageBundle\Document\Partials\InnTrait;
use MBH\Bundle\PackageBundle\Lib\AddressInterface;
use MBH\Bundle\PackageBundle\Lib\PayerInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Organization
 * @ODM\Document(collection="Organizations", repositoryClass="MBH\Bundle\PackageBundle\Document\OrganizationRepository")
 * @Gedmo\Loggable
 * @ODM\HasLifecycleCallbacks
 * @MongoDBUnique(fields="inn", message="mbhpackagebundle.document.organization.takoy.inn.uzhe.sushchestvuyet")
 */
class Organization implements PayerInterface, RecipientInterface, AddressInterface
{
    use TimestampableDocument;
    use BlameableDocument;
    use InnTrait;

    /**
     * @ODM\Id
     */
    protected $id;
    /**
     * @ODM\Field(type="string")
     * @Assert\NotBlank
     */
    protected $name;
    /**
     * @ODM\Field(type="string")
     * @Assert\Length(max="50")
     */
    protected $shortName;
    /**
     * @ODM\Field(type="string")
     */
    protected $directorFio;
    /**
     * @ODM\Field(type="string")
     */
    protected $accountantFio;
    /**
     * @ODM\Field(type="string")
     */
    protected $phone;
    /**
     * @ODM\Field(type="string")
     */
    protected $email;

    /**
     * @ODM\Field(type="string")
     * @Assert\Length(min=9,max=9)
     * @Assert\Type(type="digit", message="document.organiztion.kpp.value_must_by_digit")
     */
    protected $kpp;

    /**
     * @var \DateTime
     * @ODM\Field(type="date")
     * @Assert\Date()
     */
    protected $registrationDate;

    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $registrationNumber;
    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $activityCode;
    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $okpoCode;
    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $writerFio;
    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $reason;
    /**
     * @Gedmo\Versioned
     * @ODM\Field(type="string")
     */
    protected $countryTld;
    /**
     * @Gedmo\Versioned
     * @ODM\Field(type="int")
     */
    protected $regionId;
    /**
     * @Assert\NotBlank
     * @ODM\Field(type="int")
     */
    protected $cityId;
    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $street;
    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $house;
    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $corpus;
    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $flat;
    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $index;
    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $bank;
    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $bankBik;
    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $bankAddress;
    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $correspondentAccount;
    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $checkingAccount;
    /**
     * @ODM\Field(type="string")
     * @Assert\NotBlank
     * @Assert\Choice(
     *      choices = {"contragents", "my"}
     * )
     */
    protected $type;

    /**
     * @ODM\ReferenceMany(targetDocument="MBH\Bundle\HotelBundle\Document\Hotel", inversedBy="organization")
     * @ODM\Index()
     */
    protected $hotels;
    /**
     * @ODM\Field(type="string")
     */
    protected $comment;

    /**
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\BaseBundle\Document\ProtectedFile", cascade={"persist"})
     */
    protected $stamp;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getShortName()
    {
        return $this->shortName;
    }

    /**
     * @param mixed $shortName
     */
    public function setShortName($shortName)
    {
        $this->shortName = $shortName;
    }

    /**
     * @return mixed
     */
    public function getDirectorFio()
    {
        return $this->directorFio;
    }

    /**
     * @param mixed $directorFio
     */
    public function setDirectorFio($directorFio)
    {
        $this->directorFio = $directorFio;
    }

    /**
     * @return mixed
     */
    public function getAccountantFio()
    {
        return $this->accountantFio;
    }

    /**
     * @param mixed $accountantFio
     */
    public function setAccountantFio($accountantFio)
    {
        $this->accountantFio = $accountantFio;
    }

    /**
     * @return mixed
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param mixed $phone
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getKpp()
    {
        return $this->kpp;
    }

    /**
     * @param mixed $kpp
     */
    public function setKpp($kpp)
    {
        $this->kpp = $kpp;
    }

    /**
     * @return string
     */
    public function getLocation()
    {
        return $this->getStreet() . ' ' . $this->getHouse();
    }

    /**
     * @return mixed
     */
    public function getRegistrationDate()
    {
        return $this->registrationDate;
    }

    /**
     * @param mixed $registrationDate
     */
    public function setRegistrationDate($registrationDate)
    {
        $this->registrationDate = $registrationDate;
    }

    /**
     * @return mixed
     */
    public function getRegistrationNumber()
    {
        return $this->registrationNumber;
    }

    /**
     * @param mixed $registrationNumber
     */
    public function setRegistrationNumber($registrationNumber)
    {
        $this->registrationNumber = $registrationNumber;
    }

    /**
     * @return mixed
     */
    public function getActivityCode()
    {
        return $this->activityCode;
    }

    /**
     * @param mixed $activityCode
     */
    public function setActivityCode($activityCode)
    {
        $this->activityCode = $activityCode;
    }

    /**
     * @return mixed
     */
    public function getOkpoCode()
    {
        return $this->okpoCode;
    }

    /**
     * @param mixed $okpoCode
     */
    public function setOkpoCode($okpoCode)
    {
        $this->okpoCode = $okpoCode;
    }

    /**
     * @return mixed
     */
    public function getWriterFio()
    {
        return $this->writerFio;
    }

    /**
     * @param mixed $writerFio
     */
    public function setWriterFio($writerFio)
    {
        $this->writerFio = $writerFio;
    }

    /**
     * @return mixed
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * @param mixed $reason
     */
    public function setReason($reason)
    {
        $this->reason = $reason;
    }

    /**
     * @return int
     */
    public function getCityId()
    {
        return $this->cityId;
    }

    /**
     * @param City|null $cityId
     */
    public function setCityId($cityId = null)
    {
        $this->cityId = $cityId;
    }

    /**
     * @return string
     */
    public function getCountryTld()
    {
        return $this->countryTld;
    }

    /**
     * @param string $countryTld
     */
    public function setCountryTld($countryTld)
    {
        $this->countryTld = $countryTld;
    }

    /**
     * @return int
     */
    public function getRegionId()
    {
        return $this->regionId;
    }

    /**
     * @param int $regionId
     */
    public function setRegionId($regionId)
    {
        $this->regionId = $regionId;
    }

    /**
     * @return mixed
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * @param mixed $street
     */
    public function setStreet($street)
    {
        $this->street = $street;
    }

    /**
     * @return mixed
     */
    public function getHouse()
    {
        return $this->house;
    }

    /**
     * @param mixed $house
     */
    public function setHouse($house)
    {
        $this->house = $house;
    }

    /**
     * @return mixed
     */
    public function getCorpus()
    {
        return $this->corpus;
    }

    /**
     * @param mixed $corpus
     */
    public function setCorpus($corpus)
    {
        $this->corpus = $corpus;
    }

    /**
     * @return mixed
     */
    public function getFlat()
    {
        return $this->flat;
    }

    /**
     * @param mixed $flat
     */
    public function setFlat($flat)
    {
        $this->flat = $flat;
    }

    /**
     * @return mixed
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * @param mixed $index
     */
    public function setIndex($index)
    {
        $this->index = $index;
    }

    /**
     * @return mixed
     */
    public function getBank()
    {
        return $this->bank;
    }

    /**
     * @param mixed $bank
     */
    public function setBank($bank)
    {
        $this->bank = $bank;
    }

    /**
     * @return mixed
     */
    public function getBankBik()
    {
        return $this->bankBik;
    }

    /**
     * @param mixed $bankBik
     */
    public function setBankBik($bankBik)
    {
        $this->bankBik = $bankBik;
    }

    /**
     * @return mixed
     */
    public function getBankAddress()
    {
        return $this->bankAddress;
    }

    /**
     * @param mixed $bankAddress
     */
    public function setBankAddress($bankAddress)
    {
        $this->bankAddress = $bankAddress;
    }

    /**
     * @return string
     */
    public function getCorrespondentAccount()
    {
        return $this->correspondentAccount;
    }

    /**
     * @param string $correspondentAccount
     */
    public function setCorrespondentAccount($correspondentAccount)
    {
        $this->correspondentAccount = $correspondentAccount;
    }

    /**
     * @return string
     */
    public function getCheckingAccount()
    {
        return $this->checkingAccount;
    }

    /**
     * @param string $checkingAccount
     */
    public function setCheckingAccount($checkingAccount)
    {
        $this->checkingAccount = $checkingAccount;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return Hotel[]
     */
    public function getHotels()
    {
        return $this->hotels;
    }

    public function setHotels($hotels)
    {
        $this->hotels = $hotels;
    }

    /**
     * @param Hotel $hotel
     */
    public function addHotel(Hotel $hotel)
    {
        $this->hotels[] = $hotel;
    }

    /**
     * @return mixed
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param mixed $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    }

    /**
     * @return ProtectedFile
     */
    public function getStamp()
    {
        return $this->stamp;
    }



    public function setStamp($stamp)
    {
        $this->stamp = $stamp;
    }

    /**
     * @PrePersist
     */
    public function prePersist()
    {
        if (!$this->getShortName()) {
            $this->setShortName($this->getName());
        }
    }

    /**
     * @PreUpdate
     */
    public function preUpdate()
    {
        if (!$this->getShortName()) {
            $this->setShortName($this->getName());
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }

    /**
     * @return string|null
     */
    public function getCommunicationLanguage()
    {
        return null;
    }

    /**
     * @return null|string
     */
    public function getZipCode()
    {
        return null;
    }
}