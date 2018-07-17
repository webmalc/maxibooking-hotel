<?php
/**
 * Created by PhpStorm.
 * Date: 06.06.18
 */

namespace MBH\Bundle\ClientBundle\Lib\PaymentSystem\NewRbk;


class TaxMode implements \JsonSerializable
{
    const TAXATION_RATE_CODE = [
        '0'   => '0%',
        '10'  => '10%',
        '18'  => '18%',
        '110' => '10/110',
        '118' => '18/118',
    ];
    
    /**
     * Тип схемы налогообложения
     *
     * @var string
     * Required
     */
    private $type = 'InvoiceLineTaxVAT';

    /**
     * Ставка налога
     *
     * @var string
     * Required
     * "0%" "10%" "18%" "10/110" "18/118"
     */
    private $rate;

    public static function create($data)
    {
        $self = new self();

        if (!empty($data['type'])) {
            $self->setType($data['type']);
        }

        if (!empty($data['rate'])) {
            $self->setRate($data['rate']);
        }

        return $self;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getRate(): string
    {
        return $this->rate;
    }

    /**
     * @param string $rate
     */
    public function setRate(string $rate): void
    {
        $this->rate = $rate;
    }

    public function jsonSerialize()
    {
        return [
            'type' => $this->getType(),
            'rate' => $this->getRate(),
        ];
    }
}