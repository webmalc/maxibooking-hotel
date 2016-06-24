<?php
/**
 * Created by PhpStorm.
 * User: zalex
 * Date: 23.06.16
 * Time: 15:27
 */

namespace MBH\Bundle\RestaurantBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Class DishMenuIngredientEmbed
 * @package MBH\Bundle\RestaurantBundle\Document
 * @ODM\EmbeddedDocument()
 * @Gedmo\Loggable()
 */
class DishMenuIngredientEmbedded
{

    /**
     * @var
     * @ODM\Field(type="float", name="amount")
     * @Gedmo\Versioned()
     */
    protected $amount;

    /**
     *
     * @ODM\ReferenceOne(targetDocument="Ingredient")
     * @Gedmo\Versioned()
     */
    protected $ingredient;

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
    public function getIngredient()
    {
        return $this->ingredient;
    }

    /**
     * @param Ingredient $ingredient
     */
    public function setIngredient(Ingredient $ingredient)
    {
        $this->ingredient = $ingredient;
    }


}