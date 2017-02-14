<?php
/**
 * Created by PhpStorm.
 * User: danya
 * Date: 14.02.17
 * Time: 11:57
 */

namespace MBH\Bundle\HotelBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\EmbeddedDocument()
 * Class ContactInfo
 * @package MBH\Bundle\HotelBundle\Document
 */
class ContactInfo
{
    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $fullName;

    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $email;

    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $phoneNumber;

    /**
     * @return string
     */
    public function getFullName(): string
    {
        return $this->fullName;
    }

    /**
     * @param string $fullName
     * @return ContactInfo
     */
    public function setFullName(string $fullName): ContactInfo
    {
        $this->fullName = $fullName;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return ContactInfo
     */
    public function setEmail(string $email): ContactInfo
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return string
     */
    public function getPhoneNumber(): string
    {
        return $this->phoneNumber;
    }

    /**
     * @param string $phoneNumber
     * @return ContactInfo
     */
    public function setPhoneNumber(string $phoneNumber): ContactInfo
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }
}