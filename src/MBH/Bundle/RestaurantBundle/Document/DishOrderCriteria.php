<?php
/**
 * Created by PhpStorm.
 * User: zalex
 * Date: 08.07.16
 * Time: 16:10
 */

namespace MBH\Bundle\RestaurantBundle\Document;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * Class DishOrderCriteria
 * @package MBH\Bundle\RestaurantBundle\Document
 */
class DishOrderCriteria
{
    const ORDER_PAID = 'true';
    const ORDER_NOT_PAID = 'false';

    /**
     * @var \DateTime
     * @Assert\Date()
     */
    public $begin;

    /**
     * @var \DateTime
     * @Assert\Date()
     */
    public $end;

    /**
     * @var boolean
     */
    public $isFreezed;

    /**
     * @var float
     */
    public $moneyBegin;

    /**
     * @var float
     */
    public $moneyEnd;

    public $search;

    /**
     *
     * @return \DateTime
     *
     */
    public function getBegin()
    {
        return $this->begin;
    }

    /**
     * @param \DateTime $begin
     */
    public function setBegin($begin)
    {
        $this->begin = $begin;
    }

    /**
     * @return \DateTime
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * @param \DateTime $end
     */
    public function setEnd($end)
    {
        $this->end = $end;
    }

    /**
     * @return boolean
     */
    public function isIsFreezed()
    {
        return $this->isFreezed;
    }

    /**
     * @param boolean $isFreezed
     */
    public function setIsFreezed($isFreezed)
    {
        $this->isFreezed = filter_var($isFreezed, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @return float
     */
    public function getMoneyBegin()
    {
        return $this->moneyBegin;
    }

    /**
     * @param float $moneyBegin
     */
    public function setMoneyBegin($moneyBegin)
    {
        $this->moneyBegin = $moneyBegin;
    }

    /**
     * @return float
     */
    public function getMoneyEnd()
    {
        return $this->moneyEnd;
    }

    /**
     * @param float $moneyEnd
     */
    public function setMoneyEnd($moneyEnd)
    {
        $this->moneyEnd = $moneyEnd;
    }

    public function endWholeDay()
    {
        if ($this->end instanceof \DateTime) {
            $end = clone $this->end;
            return $end->modify("tomorrow -1 minute");
        }
    }
}