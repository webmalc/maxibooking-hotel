<?php


namespace MBH\Bundle\ChannelManagerBundle\Lib\ICalType;


class ICalTouristData
{
    /** @var ?string */
    private $payerSurname;

    /** @var?string */
    private $payerName;

    /** @var?string */
    private $payerEmail;

    /** @var ?string */
    private $payerPhone;

    /**
     * @return string|null string
     */
    public function getPayerSurname(): ?string
    {
        return $this->payerSurname;
    }

    /**
     * @param string|null $payerSurname
     * @return ICalTouristData
     */
    public function setPayerSurname(?string $payerSurname): self
    {
        $this->payerSurname = $payerSurname;
        
        return $this;
    }

    /**
     * @return string|null string
     */
    public function getPayerName(): ?string
    {
        return $this->payerName;
    }

    /**
     * @param string|null $payerName
     * @return ICalTouristData
     */
    public function setPayerName(?string $payerName): self
    {
        $this->payerName = $payerName;

        return $this;
    }

    /**
     * @return string|null string
     */
    public function getPayerEmail(): ?string
    {
        return $this->payerEmail;
    }

    /**
     * @param string|null $payerEmail
     * @return ICalTouristData
     */
    public function setPayerEmail(?string $payerEmail): self
    {
        $this->payerEmail = $payerEmail;

        return $this;
    }

    /**
     * @return string|null string
     */
    public function getPayerPhone(): ?string
    {
        return $this->payerPhone;
    }

    /**
     * @param string|null $payerPhone
     * @return ICalTouristData
     */
    public function setPayerPhone(?string $payerPhone): self
    {
        $this->payerPhone = $payerPhone;

        return $this;
    }
}
