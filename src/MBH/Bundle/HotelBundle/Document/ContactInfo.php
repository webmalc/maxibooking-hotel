<?php

namespace MBH\Bundle\HotelBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use MBH\Bundle\PackageBundle\Document\Tourist;
use Symfony\Component\Validator\Constraints as Assert;

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
     * @Assert\Length(
     *     min=4,
     *      minMessage="validator.document.contact_info.email.to_short",
     *      max=256,
     *      maxMessage="validator.document.contact_info.email.too_long"
     * )
     */
    protected $email;

    /**
     * @var string
     * @ODM\Field(type="string")
     * @Assert\Length(
     *     min=3,
     *      minMessage="validator.document.contact_info.phone_number.to_short",
     *      max=50,
     *      maxMessage="validator.document.contact_info.phone_number.too_long"
     * )
     */
    protected $phoneNumber;

    /**
     * @return string
     */
    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    /**
     * @param string $fullName
     * @return ContactInfo
     */
    public function setFullName(string $fullName = null): ContactInfo
    {
        $this->fullName = $fullName;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return ContactInfo
     */
    public function setEmail(string $email = null): ContactInfo
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @param bool $original
     * @return string
     */
    public function getPhoneNumber($original = false): ?string
    {
        return $this->phoneNumber !== null ? Tourist::formatPhone($this->phoneNumber, $original) : null;
    }

    /**
     * @param string $phoneNumber
     * @return ContactInfo
     */
    public function setPhoneNumber(string $phoneNumber = null): ContactInfo
    {
        if (!is_null($phoneNumber)) {
            $this->phoneNumber = Tourist::cleanPhone($phoneNumber);
        } else {
            $this->phoneNumber = null;
        }

        return $this;
    }
}