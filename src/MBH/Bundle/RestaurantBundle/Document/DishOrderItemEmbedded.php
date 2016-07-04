<?php
/**
 * Created by PhpStorm.
 * User: zalex
 * Date: 04.07.16
 * Time: 10:53
 */

namespace MBH\Bundle\RestaurantBundle\Document;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Class DishOrderItemEmbedded
 * @package MBH\Bundle\RestaurantBundle\Document
 * @ODM\EmbeddedDocument()
 * @Gedmo\Loggable()
 */

class DishOrderItemEmbedded
{
    /**
     * @var
     * @ODM\Field(type="int", name="amount")
     * @Gedmo\Versioned()
     */
    protected $amount;

    /**
     * @var DishMenuItem $dishMenuItem
     * @ODM\ReferenceOne(targetDocument="DishMenuItem")
     * @Gedmo\Versioned()
     */
    protected $dishMenuItem;

    /**
     * @ODM\Field(type="float", name="dishesPrice")
     * @Gedmo\Versioned()
     */
    protected $price;

    /**
     * @return mixed
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param mixed $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * @return mixed
     */
    public function getDishMenuItem(): DishMenuItem
    {
        return $this->dishMenuItem;
    }

    /**
     * @param mixed $dishMenuItem
     */
    public function addDishMenuItem(DishMenuItem $dishMenuItem)
    {
        $this->dishMenuItem = $dishMenuItem;
    }

    /**
     * @return mixed
     */
    public function getPrice()
    {
        return $this->price;
    }

    /* Не используемый $price не ошибка, при обработке формы он нужен для того чтоб вызывался метод без ошибок*/
    public function setPrice($price = 0)
    {
        $this->price = $this->getDishMenuItem()->getActualPrice() * $this->amount;
    }


}