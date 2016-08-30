<?php

namespace MBH\Bundle\FMSBundle\Document;

use MBH\Bundle\BaseBundle\Document\Base;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * Class KonturOfficialFMSOrgan
 * @package MBH\Bundle\FMSBundle\Document
 * @ODM\Document(collection="kontur_fms_organs")
 */
class KonturFMSOrgan extends Base
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @var string
     * @ODM\Field(type="string")
     * @Gedmo\Versioned
     */
    protected $name;

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode(string $code)
    {
        $this->code = $code;
    }

    /**
     * @var string
     * @ODM\Field(type="string")
     * @Gedmo\Versioned
     */
    protected $code;

    /**
     * @return \DateTime
     */
    public function getEndDate(): \DateTime
    {
        return $this->endDate;
    }

    /**
     * @param \DateTime $endDate
     */
    public function setEndDate(\DateTime $endDate)
    {
        $this->endDate = $endDate;
    }

    /**
     * @var \DateTime
     * @ODM\Date()
     * @Gedmo\Versioned
     */
    protected $endDate;
}