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

    /**
     * @var \Closure|null
     */
    private $individualErrorResponse;

    /**
     * @var \Closure|null
     */
    private $individualSuccessResponse;

    public function parseData(array $data): self
    {
        foreach ($data as $key => $value){
            $setter = 'set' . ucfirst($key);
            if (method_exists($this,$setter)) {
                $this->$setter($value);
            }
        }

        return $this;
    }

    /**
     * @return \Closure|null
     */
    public function getIndividualSuccessResponse($param = null): ?\Closure
    {
        if ($this->individualSuccessResponse !== null) {
            $func = $this->individualSuccessResponse;

            return $func($param);
        }

        return null;
    }

    /**
     * @param \Closure|null $individualSuccessResponse
     */
    public function setIndividualSuccessResponse(?\Closure $individualSuccessResponse): void
    {
        $this->individualSuccessResponse = $individualSuccessResponse;
    }


    /**
     * @return \Closure|null
     */
    public function getIndividualErrorResponse(): ?\Closure
    {
        if ($this->individualErrorResponse !== null) {
            $func = $this->individualErrorResponse;

            return $func();
        }

        return null;
    }

    /**
     * @param \Closure $individualResponse
     */
    public function setIndividualErrorResponse(\Closure $individualErrorResponse): void
    {
        $this->individualErrorResponse = $individualErrorResponse;
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
        $r = true;

        if ($this->doc === null) {
            $r = false;
        }

        if ($this->individualErrorResponse !== null) {
            $r = false;
        }

        return $r;
    }
}