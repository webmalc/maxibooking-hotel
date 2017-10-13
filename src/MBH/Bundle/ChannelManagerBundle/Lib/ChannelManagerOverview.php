<?php

namespace MBH\Bundle\ChannelManagerBundle\Lib;

use MBH\Bundle\PriceBundle\Document\PriceCache;
use MBH\Bundle\PriceBundle\Document\Restriction;

class ChannelManagerOverview
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $prices = [];
    
    /**
     * @var array
     */
    private $restrictions = [];
    
    /**
     * @var \DateTime
     */
    private $end;

   /**
     * @var \DateTime
     */
    private $begin;

    /**
     * begin set
     *
     * @param \DateTime $begin
     * @return self
     */
    public function setBegin(\DateTime $begin): self
    {
        $this->begin = $begin;

        return $this;
    }

    /**
     * begin get
     *
     * @return \DateTime
     */
    public function getBegin(): \DateTime
    {
        return $this->begin;
    }

    /**
     * end set
     *
     * @param \DateTime $end
     * @return self
     */
    public function setEnd(\DateTime $end): self
    {
        $this->end = $end;

        return $this;
    }

    /**
     * end get
     *
     * @return \DateTime
     */
    public function getEnd(): \DateTime
    {
        return $this->end;
    }
    
    /**
     * add restriction to storage
     *
     * @param Restriction $restriction
     * @param string $message
     * @return self
     */
    public function addRestrictions(Restriction $restriction, string $message): self
    {
        $this->restrictions[$restriction->getDate()->format('d.m.Y')][] = [
            'restriction' => $restriction, 'message' => $message
        ];
        return $this;
    }
    
    /**
     * add price to storage
     *
     * @param PriceCache $price
     * @param string $message
     * @return self
     */
    public function addPrices(PriceCache $price, string $message): self
    {
        $this->prices[$price->getDate()->format('d.m.Y')][] = [
            'price' => $price, 'message' => $message
        ];
        return $this;
    }

    /**
     * name set
     *
     * @param string $name
     * @return self
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * name get
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * get period
     *
     * @return array
     */
    public function getPeriod(): array
    {
        $end = clone $this->end;
        $end->modify('+1 day');
        
        return iterator_to_array(
            new \DatePeriod($this->begin, \DateInterval::createFromDateString('1 day'), $end)
        );
    }

    /**
     * get restriction by date
     *
     * @param \DateTime $date
     * @return array
     */
    public function getRestrictions(\DateTime $date): array
    {
        return $this->restrictions[$date->format('d.m.Y')] ?? [];
    }
    
    /**
     * get price by date
     *
     * @param \DateTime $date
     * @return array
     */
    public function getPrices(\DateTime $date): array
    {
        return $this->prices[$date->format('d.m.Y')] ?? [];
    }
}
