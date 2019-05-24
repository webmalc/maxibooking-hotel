<?php
/**
 * Created by PhpStorm.
 * User: zalex
 * Date: 22.06.16
 * Time: 15:11
 */

namespace MBH\Bundle\RestaurantBundle\Document;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use MBH\Bundle\BaseBundle\Document\Base;
use MBH\Bundle\BaseBundle\Document\Traits\BlameableDocument;
use MBH\Bundle\RestaurantBundle\Form\DishMenuIngredientEmbeddedType;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ODM\Document(collection="DishMenuItem", repositoryClass="MBH\Bundle\RestaurantBundle\Document\DishMenuItemRepository")
 * @Gedmo\Loggable
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class DishMenuItem extends Base
{
    /**
     * Hook timestampable behavior
     * updates createdAt, updatedAt fields
     */
    use TimestampableDocument;

    /**
     * Hook softdeleteable behavior
     * deletedAt field
     */
    use SoftDeleteableDocument;

    /**
     * Hook blameable behavior
     * createdBy&updatedBy fields
     */
    use BlameableDocument;

    /**
     * @Gedmo\Versioned()
     * @ODM\ReferenceOne(targetDocument="DishMenuCategory", inversedBy="dishMenuItems")
     * @Assert\NotNull()
     */
    protected $category;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string", name="fullTitle")
     * @Assert\NotNull()
     * @Assert\Length(
     *      min=2,
     *      minMessage="validator.document.dishmenuitem.min_name",
     *      max=100,
     *      maxMessage="validator.document.dishmenuitem.max_name"
     * )
     */
    protected $fullTitle = '';

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string", name="title")
     * @Assert\Length(
     *      min=2,
     *      minMessage="validator.document.dishmenuitem.min_name",
     *      max=100,
     *      maxMessage="validator.document.dishmenuitem.max_name"
     * )
     */
    protected $title = '';

    /**
     * @var int
     * @Gedmo\Versioned()
     * @ODM\Field(type="float", name="price")
     * @Assert\Type(type="numeric")
     * @Assert\Range(
     *      min=0,
     *      minMessage="validator.document.dishmenuitem.null_price"
     * )
     */
    protected $price = 0;

    /**
     * @var int
     * @Gedmo\Versioned()
     * @ODM\Field(type="float", name="margin")
     * @Assert\Type(type="numeric")
     * @Assert\Range(
     *      min=0,
     *      minMessage="validator.document.dishmenuitem.null_margom"
     * )
     */
    protected $margin = 30;


    /**
     * @var boolean
     * @Gedmo\Versioned
     * @ODM\Field(type="boolean")
     * @Assert\NotNull()
     * @Assert\Type(type="boolean")
     */
    protected $isMargin = false;


    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string", name="description")
     * @Assert\Length(
     *      min=2,
     *      minMessage="validator.document.dishmenuitem.min_desc",
     *      max=300,
     *      maxMessage="validator.document.dishmenuitem.max_desc"
     * )
     */
    protected $description;

    /**
     * @ODM\EmbedMany(targetDocument="DishMenuIngredientEmbedded" )
     */
    protected $dishIngredients;

    /**
     * DishMenuItem constructor.
     *
     */
    public function __construct()
    {
        $this->dishIngredients = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getCategory(): DishMenuCategory
    {
        return $this->category;
    }

    /**
     * @param mixed $category
     * @return $this
     */
    public function setCategory(DishMenuCategory $category)
    {
        $this->category = $category;
        return $this;
    }

    /**
     * @return string
     */
    public function getFullTitle(): string
    {
        return $this->fullTitle;
    }

    /**
     * @param string $fullTitle
     * @return $this
     */
    public function setFullTitle($fullTitle)
    {
        $this->fullTitle = $fullTitle;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;
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
     * @param int $price
     * @return $this
     */
    public function setPrice($price)
    {
        $this->price = $price;
        return $this;
    }

    /**
     * Себестоимость
     * @return mixed
     */
    public function getCostPrice(): float
    {
        $costPrice = 0;
        /** @var  DishMenuIngredientEmbedded $ingredient */
        foreach ($this->getDishIngredients()->getValues() as $ingredient) {
            $ingredientCostPrice = $ingredient->getIngredient()->getCostPrice();
            $amount = $ingredient->getAmount();
            $costPrice += $ingredientCostPrice * $amount;
        }
        return (float) $costPrice;
    }

    public function setCostPrice($costPrice = null)
    {
        return $this;
    }


    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    public function getHotel()
    {
        return $this->getCategory()->getHotel();
    }

    /**
     * @return ArrayCollection
     */
    public function getDishIngredients()
    {
        return $this->dishIngredients;
    }

    /**
     * @param mixed $dishIngredients
     * @return $this
     */
    public function setDishIngredients(DishMenuIngredientEmbeddedType $dishIngredients)
    {
        $this->dishIngredients->add($dishIngredients);
        return $this;
    }


    public function addDishIngredients(DishMenuIngredientEmbedded $ingredient)
    {
        $this->dishIngredients->add($ingredient);
    }

    /**
     * @return int
     */
    public function getMargin()
    {
        return $this->margin;
    }

    /**
     * @param int $margin
     */
    public function setMargin($margin)
    {
        $this->margin = $margin;
    }

    /**
     * @return boolean
     */
    public function getIsMargin()
    {
        return $this->isMargin;
    }

    /**
     * @param boolean $isMargin
     */
    public function setIsMargin($isMargin)
    {
        $this->isMargin = $isMargin;
    }

    public function getMarginPrice()
    {
        $costPrice = $this->getCostPrice();
        $margin = $this->margin;
        $percent = $costPrice / 100 * $margin;
        return $costPrice + $percent;
    }
    /* Вернуть актуальную цену */
    public function getActualPrice()
    {
        if ($this->isMargin) {
            return $this->getMarginPrice();
        } else {
            return $this->getPrice();
        }
    }

}
