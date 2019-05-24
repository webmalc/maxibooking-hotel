<?php

namespace MBH\Bundle\PriceBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;
use MBH\Bundle\BaseBundle\Document\Base;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class TariffService
 * @Gedmo\Loggable()
 * @ODM\EmbeddedDocument()
 * @Gedmo\Loggable()
 */
class TariffService extends Base
{
    /**
     * @var \MBH\Bundle\PriceBundle\Document\Service
     * @Gedmo\Versioned
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\PriceBundle\Document\Service")
     * @Assert\NotNull(message="validator.document.packageService.no_service_selected")
     */
    protected $service;

    /**
     * @var int
     * @Gedmo\Versioned
     * @ODM\Field(type="integer")
     * @Assert\Type(type="numeric")
     * @Assert\Range(
     *      min=1,
     *      minMessage= "validator.document.packageService.amount_less_1"
     * )
     * @Assert\NotNull()
     */
    protected $amount;

    /**
     * @var int
     * @Gedmo\Versioned
     * @ODM\Field(type="integer")
     * @Assert\Type(type="numeric")
     * @Assert\Range(
     *      min=1,
     *      minMessage= "validator.document.packageService.person_amount_less_1"
     * )
     */
    protected $persons;

    /**
     * @var int
     * @Gedmo\Versioned
     * @ODM\Field(type="integer")
     * @Assert\Type(type="numeric")
     * @Assert\Range(
     *      min=1,
     *      minMessage= "validator.document.packageService.nights_amount_less_1"
     * )
     */
    protected $nights;

    /**
     * @return Service
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * @param Service $service
     */
    public function setService($service)
    {
        $this->service = $service;
    }

    /**
     * @return int
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param int $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * @return int
     */
    public function getPersons()
    {
        return $this->persons;
    }

    /**
     * @param int $persons
     */
    public function setPersons($persons)
    {
        $this->persons = $persons;
    }

    /**
     * @return int
     */
    public function getNights()
    {
        return $this->nights;
    }

    /**
     * @param int $nights
     */
    public function setNights($nights)
    {
        $this->nights = $nights;
    }
}
