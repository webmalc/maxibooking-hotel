<?php
/**
 * Created by PhpStorm.
 * Date: 10.10.18
 */

namespace MBH\Bundle\PriceBundle\Lib;


use MBH\Bundle\PriceBundle\Document\PriceCache;

abstract class PriceCacheKit
{
    /**
     * @var float[]
     */
    protected $additionalPrices = [];

    /**
     * @var float[]
     */
    protected $additionalChildrenPrices = [];

    /**
     * @var string[]
     */
    protected $additionalPricesRawFields = [];

    /**
     * @var string[]
     */
    protected $additionalChildrenPricesRawFields = [];

    /**
     * @var float|null
     */
    protected $price;

    /**
     * @var bool
     */
    protected $isPersonPrice = false;

    /**
     * @var float|null
     */
    protected $singlePrice;

    /**
     * @var float|null
     */
    protected $childPrice;

    /**
     * @return float|null
     */
    public function getChildPrice(): ?float
    {
        return $this->childPrice;
    }

    /**
     * @param float|null $childPrice
     */
    public function setChildPrice(?float $childPrice): void
    {
        $this->childPrice = $childPrice;
    }

    /**
     * @return float|null
     */
    public function getPrice(): ?float
    {
        return $this->price;
    }

    /**
     * @param float|null $price
     */
    public function setPrice(?float $price): void
    {
        $this->price = $price;
    }

    /**
     * @return bool
     */
    public function isPersonPrice(): bool
    {
        return $this->isPersonPrice;
    }

    /**
     * @param bool $personPrice
     */
    public function setIsPersonPrice(bool $personPrice): void
    {
        $this->isPersonPrice = $personPrice;
    }

    /**
     * @return float|null
     */
    public function getSinglePrice(): ?float
    {
        return $this->singlePrice;
    }

    /**
     * @param float|null $singlePrice
     */
    public function setSinglePrice(?float $singlePrice): void
    {
        $this->singlePrice = $singlePrice;
    }

    public function getAdditionalPrice(): ?float
    {
        return $this->additionalPrices['additionalPrice'] ?? null;
    }

    public function getAdditionalChildrenPrice(): ?float
    {
        return $this->additionalChildrenPrices['additionalChildrenPrice'] ?? null;
    }

    /**
     * @return float[]
     */
    public function getAdditionalPrices(): array
    {
        ksort($this->additionalPrices, SORT_NATURAL);

        return array_values($this->additionalPrices);
    }

    public function getAdditionalChildrenPrices(): array
    {
        ksort($this->additionalChildrenPrices, SORT_NATURAL);

        return array_values($this->additionalChildrenPrices);
    }

    public function createPriceCache(): PriceCache
    {
        $priceCache = new PriceCache();
        $priceCache
            ->setPrice($this->getPrice())
            ->setChildPrice($this->getChildPrice())
            ->setIsPersonPrice($this->isPersonPrice())
            ->setSinglePrice($this->getSinglePrice())
            ->setAdditionalPrice($this->getAdditionalPrice())
            ->setAdditionalChildrenPrice($this->getAdditionalChildrenPrice())
            ->setAdditionalPrices($this->getAdditionalPrices())
            ->setAdditionalChildrenPrices($this->getAdditionalChildrenPrices());

        return $priceCache;
    }

    public function __get($name)
    {
        if (isset($this->additionalChildrenPricesRawFields[$name])) {
            return $this->additionalChildrenPricesRawFields[$name];
        }
        if (isset($this->additionalChildrenPrices[$name])) {
            return $this->additionalChildrenPrices[$name];
        }
        if (isset($this->additionalPricesRawFields[$name])) {
            return $this->additionalPricesRawFields[$name];
        }
        if (isset($this->additionalPrices[$name])) {
            return $this->additionalPrices[$name];
        }

        if (strpos($name, 'additionalChildrenPrice') !== false
            || strpos($name, 'additionalPrice') !== false) {
            return null;
        }

        throw new \RuntimeException(sprintf('A property "%s" in the class "%s" not found.', $name, static::class));
    }

    public function __isset($name)
    {
        if (isset($this->additionalChildrenPricesRawFields[$name])) {
            return true;
        }
        if (isset($this->additionalChildrenPrices[$name])) {
            return true;
        }
        if (isset($this->additionalPricesRawFields[$name])) {
            return true;
        }
        if (isset($this->additionalPrices[$name])) {
            return true;
        }

        return false;
    }

    public function __set($name, $value): void
    {
        if ($value === '') {
            return;
        }
        if (strpos($name, 'additionalChildrenPriceFake') !== false ) {
            $this->additionalChildrenPricesRawFields[$name] = $value;
        } elseif (strpos($name, 'additionalChildrenPrice') !== false) {
            $this->additionalChildrenPrices[$name] = (float) $value;
        } elseif (strpos($name, 'additionalPriceFake') !== false) {
            $this->additionalPricesRawFields[$name] = $value;
        } elseif (strpos($name, 'additionalPrice') !== false) {
            $this->additionalPrices[$name] = (float) $value;
        }
    }
}