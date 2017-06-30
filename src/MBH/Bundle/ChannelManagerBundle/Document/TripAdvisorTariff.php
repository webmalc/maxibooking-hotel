<?php

namespace MBH\Bundle\ChannelManagerBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Validator\Constraints as Assert;
use MBH\Bundle\PriceBundle\Document\Tariff;

/**
 * @ODM\EmbeddedDocument()
 * Class TripAdvisorTariff
 * @package MBH\Bundle\ChannelManagerBundle\Document
 */
class TripAdvisorTariff
{
    /**
     * @var bool
     * @ODM\Field(type="bool")
     */
    protected $isEnabled = false;

    /**
     * @var Tariff
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\PriceBundle\Document\Tariff")
     * @Assert\NotNull()
     */
    protected $tariff;

    /**
     * @var string
     * @ODM\Field(type="string")
     * @Assert\Choice(callback="getRefundableTypes")
     */
    protected $refundableType;
    
    /**
     * @var int
     * @ODM\Field(type="int")
     */
    protected $deadline;

    /**
     * @var bool
     * @ODM\Field(type="bool")
     */
    protected $isPenaltyExists = false;

    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $policyInfo;
//
//    /**
//     * @var TripAdvisorFee[]
//     * @ODM\ReferenceMany(targetDocument="MBH\Bundle\ChannelManagerBundle\Model\TripAdvisor\TripAdvisorFee")
//     */
//    protected $fees;

//    public function __construct()
//    {
//        $this->fees = new ArrayCollection();
//    }

//    /**
//     * @return TripAdvisorFee[]
//     */
//    public function getFees()
//    {
//        return $this->fees;
//    }
//
//    /**
//     * @param TripAdvisorFee $fee
//     * @return TripAdvisorTariff
//     */
//    public function addFee(TripAdvisorFee $fee)
//    {
//        $this->fees->add($fee);
//
//        return $this;
//    }
//
//    /**
//     * @param TripAdvisorFee $fee
//     * @return TripAdvisorTariff
//     */
//    public function removeFee(TripAdvisorFee $fee)
//    {
//        $this->fees->remove($fee);
//
//        return $this;
//    }

    /**
     * @return Tariff
     */
    public function getTariff(): ?Tariff
    {
        return $this->tariff;
    }

    /**
     * @param Tariff $tariff
     * @return TripAdvisorTariff
     */
    public function setTariff(Tariff $tariff): TripAdvisorTariff
    {
        $this->tariff = $tariff;

        return $this;
    }

    /**
     * @return string
     */
    public function getRefundableType(): ?string
    {
        return $this->refundableType;
    }

    /**
     * @param string $refundableType
     * @return TripAdvisorTariff
     */
    public function setRefundableType(string $refundableType): TripAdvisorTariff
    {
        $this->refundableType = $refundableType;

        return $this;
    }

    /**
     * @return int
     */
    public function getDeadline(): ?int
    {
        return $this->deadline;
    }

    /**
     * @param int $deadline
     * @return TripAdvisorTariff
     */
    public function setDeadline(int $deadline): TripAdvisorTariff
    {
        $this->deadline = $deadline;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsPenaltyExists(): bool
    {
        return $this->isPenaltyExists;
    }

    /**
     * @param bool $isPenaltyExists
     * @return TripAdvisorTariff
     */
    public function setIsPenaltyExists(bool $isPenaltyExists): TripAdvisorTariff
    {
        $this->isPenaltyExists = $isPenaltyExists;

        return $this;
    }

    /**
     * @return string
     */
    public function getPolicyInfo(): ?string
    {
        return $this->policyInfo;
    }

    /**
     * @param string $policyInfo
     * @return TripAdvisorTariff
     */
    public function setPolicyInfo(string $policyInfo): TripAdvisorTariff
    {
        $this->policyInfo = $policyInfo;

        return $this;
    }

    public static function getRefundableTypes()
    {
        return [
            'none',
            'partial',
            'full'
        ];
    }

    /**
     * @return bool
     */
    public function getIsEnabled(): ?bool
    {
        return $this->isEnabled;
    }

    /**
     * @param bool $isEnabled
     * @return TripAdvisorTariff
     */
    public function setIsEnabled(bool $isEnabled): TripAdvisorTariff
    {
        $this->isEnabled = $isEnabled;

        return $this;
    }
}