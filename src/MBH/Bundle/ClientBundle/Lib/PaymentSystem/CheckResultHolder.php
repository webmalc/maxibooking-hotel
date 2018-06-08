<?php
/**
 * Created by PhpStorm.
 * Date: 08.06.18
 */

namespace MBH\Bundle\ClientBundle\Lib\PaymentSystem;


class CheckResultHolder
{
    /**
     * id CashDocument
     *
     * @var string
     */
    private $doc;

    /**
     * @var float|null
     */
    private $commission;

    /**
     * @var boolean
     */
    private $commissionPercent = false;

    /**
     * @var string
     */
    private $text;

    public function parseData(array $data): self
    {
        foreach ($data as $key => $value){
            $setter = 'set' . ucfirst($key);
            if (property_exists($this,$setter)) {
                $this->$setter($value);
            }
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getDoc(): string
    {
        return $this->doc;
    }

    /**
     * @param string $doc
     */
    public function setDoc(string $doc): void
    {
        $this->doc = $doc;
    }

    /**
     * @return float|null
     */
    public function getCommission(): ?float
    {
        return $this->commission;
    }

    /**
     * @param float|null $commission
     */
    public function setCommission(?float $commission): void
    {
        $this->commission = $commission;
    }

    /**
     * @return bool
     */
    public function getCommissionPercent(): bool
    {
        return $this->commissionPercent;
    }

    /**
     * @param bool|null $commissionPercent
     */
    public function setCommissionPercent(?bool $commissionPercent): void
    {
        $this->commissionPercent = $commissionPercent;
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @param string $text
     */
    public function setText(string $text): void
    {
        $this->text = $text;
    }

    /**
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->doc !== null;
    }
}