<?php

namespace MBH\Bundle\FMSBundle\Document;

use MBH\Bundle\BaseBundle\Document\Base;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * Class OKSMCountry
 * @package MBH\Bundle\FMSBundle\Document
 * @ODM\Document(collection="oksm_countries")
 * @Gedmo\Loggable
 */
class OKSMCountry extends Base
{
    /**
     * @var integer
     * @Gedmo\Versioned
     */
    protected $digitalCode;

    /**
     * @var string
     * @ODM\Field(type="string")
     * @Gedmo\Versioned
     */
    protected $shortName;

    /**
     * @var string
     * @ODM\Field(type="string")
     * @Gedmo\Versioned
     */
    protected $fullName;

    /**
     * @return int
     */
    public function getDigitalCode(): int
    {
        return $this->digitalCode;
    }

    /**
     * @param int $digitalCode
     */
    public function setDigitalCode(int $digitalCode)
    {
        $this->digitalCode = $digitalCode;
    }

    /**
     * @return string
     */
    public function getShortName(): string
    {
        return $this->shortName;
    }

    /**
     * @param string $shortName
     */
    public function setShortName(string $shortName)
    {
        $this->shortName = $shortName;
    }

    /**
     * @return string
     */
    public function getFullName(): string
    {
        return $this->fullName;
    }

    /**
     * @param string $fullName
     */
    public function setFullName(string $fullName)
    {
        $this->fullName = $fullName;
    }

    /**
     * @return int
     */
    public function getAlpha2Code(): int
    {
        return $this->alpha2Code;
    }

    /**
     * @param int $alpha2Code
     */
    public function setAlpha2Code(int $alpha2Code)
    {
        $this->alpha2Code = $alpha2Code;
    }

    /**
     * @return int
     */
    public function getAlpha3Code(): int
    {
        return $this->alpha3Code;
    }

    /**
     * @param int $alpha3Code
     */
    public function setAlpha3Code(int $alpha3Code)
    {
        $this->alpha3Code = $alpha3Code;
    }

    /**
     * @var integer
     * @ODM\Field(type="integer")
     * @Gedmo\Versioned
     */
    protected $alpha2Code;

    /**
     * @var integer
     * @ODM\Field(type="integer")
     * @Gedmo\Versioned
     */
    protected $alpha3Code;
}