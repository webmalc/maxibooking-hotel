<?php

namespace MBH\Bundle\ChannelManagerBundle\Model\TripAdvisor;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\EmbeddedDocument()
 * Class TripAdvisorFee
 * @package MBH\Bundle\ChannelManagerBundle\Model\TripAdvisor
 */
class TripAdvisorFee
{
    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $currency;

    /**
     * @var string
     * @Assert\Choice(callback="getFeeAmountTypes")
     * @ODM\Field(type="string")
     */
    protected $amountType;

    /**
     * @var int
     * @ODM\Field(type="int")
     */
    protected $daysBeforeArrival;

    /**
     * @var float
     * @ODM\Field(type="float")
     */
    protected $amount;

    /**
     * @return mixed
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param mixed $currency
     * @return TripAdvisorFee
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * @return string
     */
    public function getAmountType(): ?string
    {
        return $this->amountType;
    }

    /**
     * @param string $amountType
     * @return TripAdvisorFee
     */
    public function setAmountType(string $amountType): TripAdvisorFee
    {
        $this->amountType = $amountType;

        return $this;
    }

    /**
     * @return int
     */
    public function getDaysBeforeArrival(): int
    {
        return $this->daysBeforeArrival;
    }

    /**
     * @param int $daysBeforeArrival
     * @return TripAdvisorFee
     */
    public function setDaysBeforeArrival(int $daysBeforeArrival): TripAdvisorFee
    {
        $this->daysBeforeArrival = $daysBeforeArrival;

        return $this;
    }

    /**
     * @return float
     */
    public function getAmount(): ?float
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     * @return TripAdvisorFee
     */
    public function setAmount(float $amount): TripAdvisorFee
    {
        $this->amount = $amount;

        return $this;
    }

    public static function getFeeAmountTypes()
    {
        return [
            'fixed',
            'percent',
            'numNights'
        ];
    }
    //TODO: Пока что у нас налоги нигде не учитываются, посему поле tax_inclusive не использую
}