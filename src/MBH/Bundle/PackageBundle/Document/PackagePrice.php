<?php

namespace MBH\Bundle\PackageBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use MBH\Bundle\OnlineBundle\Services\ApiHandler;
use MBH\Bundle\PriceBundle\Document\Promotion;
use MBH\Bundle\PriceBundle\Document\Special;
use MBH\Bundle\PriceBundle\Document\Tariff;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ODM\EmbeddedDocument
 */
class PackagePrice
{
    /**
     * @var \DateTime
     * @ODM\Field(type="date")
     * @Assert\NotNull()
     * @Assert\Date()
     */
    protected $date;

    /**
     * @var float
     * @ODM\Field(type="float")
     * @Assert\NotNull()
     * @Assert\Type(type="numeric")
     * @Assert\Range(min=0)
     */
    protected $price;

    /**
     * @var Tariff
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\PriceBundle\Document\Tariff")
     * @Assert\NotNull()
     */
    protected $tariff;

    /**
     * @var Promotion
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\PriceBundle\Document\Promotion")
     */
    protected $promotion;

    /**
     * @var Special
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\PriceBundle\Document\Special")
     */
    protected $special;

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    public function __construct(\DateTime $date, $price, Tariff $tariff, Promotion $promotion = null, Special $special = null)
    {
        $this->setDate($date)
            ->setPrice($price)
            ->setTariff($tariff)
            ->setPromotion($promotion)
            ->setSpecial($special)
        ;
    }

    /**
     * @param \DateTime $date
     * @return PackagePrice
     */
    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param float $price
     * @return PackagePrice
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return Tariff
     */
    public function getTariff()
    {
        return $this->tariff;
    }

    /**
     * @param Tariff $tariff
     * @return PackagePrice
     */
    public function setTariff(Tariff $tariff)
    {
        $this->tariff = $tariff;

        return $this;
    }

    /**
     * @return Promotion
     */
    public function getPromotion()
    {
        return $this->promotion;
    }

    /**
     * @param Promotion $promotion
     * @return Promotion
     */
    public function setPromotion($promotion)
    {
        $this->promotion = $promotion;

        return $this;
    }

    /**
     * @return Special
     */
    public function getSpecial(): ?Special
    {
        return $this->special;
    }

    /**
     * @param Special $special
     * @return PackagePrice
     */
    public function setSpecial(Special $special = null): PackagePrice
    {
        $this->special = $special;
        return $this;
    }

    /**
     * @return float
     */
    public function getPriceWithoutPromotionDiscount()
    {
        if (!$this->getPromotion()) {
            return $this->getPrice();
        }

        if ($this->getPromotion()->getIsPercentDiscount()) {
            return $this->getPromotion()->getDiscount() < 100
                ? $this->getPrice() / (1 - ($this->getPromotion()->getDiscount() / 100))
                : 0;
        }

        return $this->getPrice() + $this->getPromotion()->getDiscount();
    }

    /**
     * @return array
     */
    public function getJsonSerialized()
    {
        return [
            'date' => $this->getDate()->format(ApiHandler::DATE_FORMAT),
            'price' => $this->getPrice(),
            'tariff' => $this->getTariff()->getId(),
            'promotion' => $this->getPromotion() ? $this->getPromotion()->getId() : null
        ];
    }
}