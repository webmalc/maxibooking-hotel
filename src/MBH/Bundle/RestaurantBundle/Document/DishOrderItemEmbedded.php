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
     * @return DishMenuItem
     */
    public function getDishMenuItem()
    {
        return $this->dishMenuItem;
    }

    /**
     * @param DishMenuItem $dishMenuItem
     */
    public function setDishMenuItem(DishMenuItem $dishMenuItem)
    {
        $this->dishMenuItem = $dishMenuItem;
    }


    /**
     * @return mixed
     */
    public function getPrice()
    {
        return $this->price * $this->amount;
    }

    //Этот метод вызываем в пост сабмит формы
    public function setPrice()
    {
        $this->price = $this->getDishMenuItem()->getActualPrice();
    }

    //Этот метод вызывает "чистую цену" на момент установки ее при добавлении
    public function getFixedPrice()
    {
        return $this->price;
    }


}