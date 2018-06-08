<?php
/**
 * Created by PhpStorm.
 * Date: 06.06.18
 */

namespace MBH\Bundle\ClientBundle\Lib\PaymentSystem\NewRbk;


class Cart implements \JsonSerializable
{
    /**
     * Описание предлагаемого товара или услуги
     *
     * @var string
     * <= 1000 characters Required
     */
    private $product;

    /**
     * Количество единиц товаров или услуг, предлагаемых на продажу в этой позиции
     *
     * @var integer
     * <int64> >= 1 Required
     */
    private $quantity = 1;

    /**
     * Цена предлагаемого товара или услуги, в минорных денежных единицах, например в копейках в случае указания российских рублей в качестве валюты
     *
     * @var integer
     *  <int64> >= 1
     * Required
     */
    private $price;

    /**
     * Суммарная стоимость позиции с учётом количества единиц товаров или услуг
     *
     * @var integer
     * <int64> >= 1
     */
    private $cost;

    /**
     * Схема налогообложения предлагаемого товара или услуги.
     * Указывается, только если предлагаемый товар или услуга облагается налогом.
     *
     * @var TaxMode
     */
    private $taxMode;

    /**
     * @param $data
     * @return array
     */
    public static function create($data)
    {
        $self = [];

        if (!empty($data) && is_array($data)) {
            foreach ($data as $value) {
                $entity = new self();
                foreach ($value as $key => $val) {
                    if ($key === 'taxMode') {
                        $entity->taxMode = TaxMode::create($val);
                    } else {
                        if (property_exists($entity, $key)) {
                            $entity->$key = $value;
                        }
                    }
                }

                $self[] = $entity;
            }
        }

        return $self;
    }

    /**
     * @return string
     */
    public function getProduct(): string
    {
        return $this->product;
    }

    /**
     * @return int
     */
    public function getQuantity(): int
    {
        return $this->quantity;
    }

    /**
     * @return int
     */
    public function getPrice(): int
    {
        return $this->price;
    }

    /**
     * @return int
     */
    public function getCost(): int
    {
        if ($this->cost === null) {
            return $this->getPrice() * $this->getQuantity();
        }

        return $this->cost;
    }

    /**
     * @return TaxMode
     */
    public function getTaxMode(): TaxMode
    {
        return $this->taxMode;
    }

    /**
     * @param TaxMode $taxMode
     */
    public function setTaxMode(TaxMode $taxMode): void
    {
        $this->taxMode = $taxMode;
    }

    /**
     * @param string $product
     */
    public function setProduct(string $product): void
    {
        $this->product = $product;
    }

    /**
     * @param int $quantity
     */
    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
    }

    /**
     * @param int $price
     */
    public function setPrice(int $price): void
    {
        $this->price = $price;
    }

    /**
     * @param int $cost
     */
    public function setCost(int $cost): void
    {
        $this->cost = $cost;
    }

    public function jsonSerialize()
    {
        return [
//            'cost'     => $this->getCost(),
            'price'    => $this->getPrice(),
            'product'  => $this->getProduct(),
            'quantity' => $this->getQuantity(),
            'taxMode'  => $this->getTaxMode(),
        ];
    }
}