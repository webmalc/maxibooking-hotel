<?php

namespace MBH\Bundle\PackageBundle\Document;

use MBH\Bundle\BaseBundle\Document\Base;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use MBH\Bundle\HotelBundle\Document\City;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\PackageBundle\Document\Package;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Blameable\Traits\BlameableDocument;
use Doctrine\ODM\MongoDB\Mapping\Annotations\PrePersist;
use Doctrine\ODM\MongoDB\Mapping\Annotations\PreUpdate;
use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique as MongoDBUnique;

/**
 * Class Organization
 * @ODM\Document(collection="Organizations", repositoryClass="MBH\Bundle\PackageBundle\Document\OrganizationRepository")
 * @Gedmo\Loggable
 * @ODM\HasLifecycleCallbacks
 * @MongoDBUnique(fields="inn", message="Такой ИНН уже существует")
 */
class Organization
{
    use TimestampableDocument;
    use BlameableDocument;
    /**
     * @ODM\Id
     */
    protected $id;
    /**
     * @ODM\String
     * @Assert\NotBlank
     */
    protected $name;
    /**
     * @ODM\String
     */
    protected $short_name;
    /**
     * @ODM\String
     */
    protected $director_fio;
    /**
     * @ODM\String
     * @Assert\NotBlank
     */
    protected $phone;
    /**
     * @ODM\String
     */
    protected $email;
    /**
     * @ODM\String
     * @Assert\NotBlank
     * @Assert\Length(min=10,max=12)
     * @Assert\Type(type="digit", message="Значение должно быть числом")
     */
    protected $inn;
    /**
     * @ODM\String
     * @Assert\NotBlank
     * @Assert\Length(min=9,max=9)
     * @Assert\Type(type="digit", message="Значение должно быть числом")
     */
    protected $kpp;

    /**
     * @var \DateTime
     * @ODM\Date
     * @Assert\Date()
     */
    protected $registration_date;

    /**
     * @ODM\String
     */
    protected $registration_number;
    /**
     * @ODM\String
     */
    protected $activity_code;
    /**
     * @ODM\String
     */
    protected $okpo_code;
    /**
     * @ODM\String
     */
    protected $writer_fio;
    /**
     * @ODM\String
     */
    protected $reason;
    /**
     * @Gedmo\Versioned
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\HotelBundle\Document\Country")
     */
    protected $country;
    /**
     * @Gedmo\Versioned
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\HotelBundle\Document\Region")
     */
    protected $region;
    /**
     * @Assert\NotBlank
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\HotelBundle\Document\City")
     */
    protected $city;
    /**
     * @ODM\String
     */
    protected $street;
    /**
     * @ODM\String
     */
    protected $house;
    /**
     * @ODM\String
     */
    protected $corpus;
    /**
     * @ODM\String
     */
    protected $flat;
    /**
     * @ODM\String
     */
protected $index;
    /**
     * @ODM\String
     */
    protected $bank;
    /**
     * @ODM\String
     */
    protected $bank_bik;
    /**
     * @ODM\String
     */
    protected $bank_address;
    /**
     * @ODM\String
     */
    protected $checking_account;
    /**
     * @ODM\String
     * @Assert\NotBlank
     * @Assert\Choice(
     *      choices = {"contragents", "my"}
     * )
     */
    protected $type;

    /**
     * @ODM\ReferenceMany(targetDocument="MBH\Bundle\HotelBundle\Document\Hotel")
     */
    protected $hotels;
    /**
     * @ODM\String
     */
    protected $comment;

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
        return $this->short_name;
    }

    /**
     * @param mixed $short_name
     */
    public function setShortName($short_name)
    {
        $this->short_name = $short_name;
    }

    /**
     * @return mixed
     */
    public function getDirectorFio()
    {
        return $this->director_fio;
    }

    /**
     * @param mixed $director_fio
     */
    public function setDirectorFio($director_fio)
    {
        $this->director_fio = $director_fio;
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
    public function getInn()
    {
        return $this->inn;
    }

    /**
     * @param mixed $inn
     */
    public function setInn($inn)
    {
        $this->inn = $inn;
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
        return $this->getCity(). ', '. $this->getStreet() . ' '. $this->getHouse();
    }

    /**
     * @return mixed
     */
    public function getRegistrationDate()
    {
        return $this->registration_date;
    }

    /**
     * @param mixed $registration_date
     */
    public function setRegistrationDate($registration_date)
    {
        $this->registration_date = $registration_date;
    }

    /**
     * @return mixed
     */
    public function getRegistrationNumber()
    {
        return $this->registration_number;
    }

    /**
     * @param mixed $registration_number
     */
    public function setRegistrationNumber($registration_number)
    {
        $this->registration_number = $registration_number;
    }

    /**
     * @return mixed
     */
    public function getActivityCode()
    {
        return $this->activity_code;
    }

    /**
     * @param mixed $activity_code
     */
    public function setActivityCode($activity_code)
    {
        $this->activity_code = $activity_code;
    }

    /**
     * @return mixed
     */
    public function getOkpoCode()
    {
        return $this->okpo_code;
    }

    /**
     * @param mixed $okpo_code
     */
    public function setOkpoCode($okpo_code)
    {
        $this->okpo_code = $okpo_code;
    }

    /**
     * @return mixed
     */
    public function getWriterFio()
    {
        return $this->writer_fio;
    }

    /**
     * @param mixed $writer_fio
     */
    public function setWriterFio($writer_fio)
    {
        $this->writer_fio = $writer_fio;
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
     * @return City
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param City|null $city
     */
    public function setCity(City $city = null)
    {
        $this->city = $city;
    }

    /**
     * @return mixed
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param mixed $country
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }

    /**
     * @return mixed
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * @param mixed $region
     */
    public function setRegion($region)
    {
        $this->region = $region;
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
        return $this->bank_bik;
    }

    /**
     * @param mixed $bank_bik
     */
    public function setBankBik($bank_bik)
    {
        $this->bank_bik = $bank_bik;
    }

    /**
     * @return mixed
     */
    public function getBankAddress()
    {
        return $this->bank_address;
    }

    /**
     * @param mixed $bank_address
     */
    public function setBankAddress($bank_address)
    {
        $this->bank_address = $bank_address;
    }

    /**
     * @return mixed
     */
    public function getCheckingAccount()
    {
        return $this->checking_account;
    }

    /**
     * @param mixed $checking_account
     */
    public function setCheckingAccount($checking_account)
    {
        $this->checking_account = $checking_account;
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
     * @PrePersist
     */
    public function prePersist()
    {
        $this->fillLocationByCity();
    }

    /**
     * @PreUpdate
     */
    public function preUpdate()
    {
        $this->fillLocationByCity();
    }

    private function fillLocationByCity()
    {
        if($this->getCity()){
            $this->setCountry($this->getCity()->getCountry());
            $this->setRegion($this->getCity()->getRegion());
        }
    }
}