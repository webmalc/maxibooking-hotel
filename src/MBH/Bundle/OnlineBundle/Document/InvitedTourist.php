<?php

namespace MBH\Bundle\OnlineBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ODM\EmbeddedDocument()
 * @Gedmo\Loggable
 * @ODM\HasLifecycleCallbacks
 */
class InvitedTourist
{
    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String()
     */
    protected $firstName;
    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String()
     */
    protected $lastName;
    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String()
     */
    protected $sex;
    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String()
     */
    protected $birthday;
    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String()
     */
    protected $birthplace;
    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String()
     */
    protected $citizenship;
    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String()
     */
    protected $passport;
    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String()
     */
    protected $expiry;

    /**
     * @return mixed
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param mixed $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @return mixed
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param mixed $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * @return mixed
     */
    public function getSex()
    {
        return $this->sex;
    }

    /**
     * @param mixed $sex
     */
    public function setSex($sex)
    {
        $this->sex = $sex;
    }

    /**
     * @return mixed
     */
    public function getBirthday()
    {
        return $this->birthday;
    }

    /**
     * @param mixed $birthday
     */
    public function setBirthday($birthday)
    {
        $this->birthday = $birthday;
    }

    /**
     * @return mixed
     */
    public function getBirthplace()
    {
        return $this->birthplace;
    }

    /**
     * @param mixed $birthplace
     */
    public function setBirthplace($birthplace)
    {
        $this->birthplace = $birthplace;
    }

    /**
     * @return mixed
     */
    public function getCitizenship()
    {
        return $this->citizenship;
    }

    /**
     * @param mixed $citizenship
     */
    public function setCitizenship($citizenship)
    {
        $this->citizenship = $citizenship;
    }

    /**
     * @return mixed
     */
    public function getPassport()
    {
        return $this->passport;
    }

    /**
     * @param mixed $passport
     */
    public function setPassport($passport)
    {
        $this->passport = $passport;
    }

    /**
     * @return mixed
     */
    public function getExpiry()
    {
        return $this->expiry;
    }

    /**
     * @param mixed $expiry
     */
    public function setExpiry($expiry)
    {
        $this->expiry = $expiry;
    }

}