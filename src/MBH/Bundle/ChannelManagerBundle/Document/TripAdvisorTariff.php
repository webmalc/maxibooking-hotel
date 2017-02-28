<?php

namespace MBH\Bundle\ChannelManagerBundle\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use MBH\Bundle\ChannelManagerBundle\Model\TripAdvisor\TripAdvisorFee;
use Symfony\Component\Validator\Constraints as Assert;
use MBH\Bundle\PriceBundle\Document\Tariff;

/**
 *
 * Class TripAdvisorTariff
 * @package MBH\Bundle\ChannelManagerBundle\Document
 */
class TripAdvisorTariff
{
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
     * @Assert\NotNull()
     */
    protected $refundableType;
    
    /**
     * @var \DateTime
     * @ODM\Field(type="date")
     * @Assert\NotNull()
     */
    protected $deadline;

    /**
     * @var bool
     * @ODM\Field(type="bool")
     * @Assert\NotNull()
     */
    protected $isPenaltyExists = false;

    /**
     * @var string
     * @ODM\Field(type="string")
     * @Assert\NotNull()
     */
    protected $policyInfo;

    /**
     * @var TripAdvisorFee[]
     * @ODM\ReferenceMany(targetDocument="MBH\Bundle\ChannelManagerBundle\Model\TripAdvisor\TripAdvisorFee")
     */
    protected $fees;

    public function __construct()
    {
        $this->fees = new ArrayCollection();
    }

    /**
     * @return TripAdvisorFee[]
     */
    public function getFees(): array
    {
        return $this->fees;
    }

    /**
     * @param TripAdvisorFee $fee
     * @return TripAdvisorTariff
     */
    public function addFee(TripAdvisorFee $fee)
    {
        $this->fees->add($fee);

        return $this;
    }

    /**
     * @param TripAdvisorFee $fee
     * @return TripAdvisorTariff
     */
    public function removeFee(TripAdvisorFee $fee)
    {
        $this->fees->remove($fee);

        return $this;
    }

    /**
     * @return Tariff
     */
    public function getTariff(): Tariff
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
     * @return \DateTime
     */
    public function getDeadline(): ?\DateTime
    {
        return $this->deadline;
    }

    /**
     * @param \DateTime $deadline
     * @return TripAdvisorTariff
     */
    public function setDeadline(\DateTime $deadline): TripAdvisorTariff
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
}