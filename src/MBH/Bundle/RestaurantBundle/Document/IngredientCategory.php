<?php
/**
 * Created by PhpStorm.
 * User: zalex
 * Date: 17.06.16
 * Time: 12:10
 */

namespace MBH\Bundle\RestaurantBundle\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\PersistentCollection;
use MBH\Bundle\BaseBundle\Document\Base;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use MBH\Bundle\BaseBundle\Annotations as MBH;
use MBH\Bundle\HotelBundle\Document\Hotel;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use MBH\Bundle\BaseBundle\Document\Traits\BlameableDocument;
/**
 * @ODM\Document(collection="IngredientCategories")
 * @Gedmo\Loggable
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */

class IngredientCategory extends Base
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
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string", name="fullTitle")
     * @Assert\NotNull()
     * @Assert\Length(
     *      min=2,
     *      minMessage="validator.document.ingredientcategory.min_name",
     *      max=100,
     *      maxMessage="validator.document.ingredientcategory.max_name"
     * )
     */
    protected $fullTitle;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string", name="title")
     * @Assert\Length(
     *      min=2,
     *      minMessage="validator.document.ingredientcategory.min_name",
     *      max=100,
     *      maxMessage="validator.document.ingredientcategory.max_name"
     * )
     */
    protected $title;

    /**
     * @Gedmo\Versioned
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\HotelBundle\Document\Hotel", inversedBy="ingredientCategories")
     * @Assert\NotNull(message="validator.document.ingredientcategory.hotel")
     */
    protected $hotel;

    /**
     * @ODM\ReferenceMany(targetDocument="MBH\Bundle\RestaurantBundle\Document\Ingredient", mappedBy="category", cascade={"remove"} )
     * @MBH\Versioned()
     */
    protected $ingredients;

    /**
     * IngredientCategory constructor.
     */
    public function __construct()
    {
        $this->ingredients = new ArrayCollection();
    }

    public function addIngredient(Ingredient $ingredient)
    {
        $this->ingredients->add($ingredient);
    }

    public function removeIngredient(Ingredient $ingredient)
    {
        $this->ingredients->removeElement($ingredient);
    }

    /**
     * @return PersistentCollection|ArrayCollection
     */
    public function getIngredients()
    {
        return $this->ingredients;
    }

    /**
     * @return string
     */
    public function getFullTitle()
    {
        return $this->fullTitle;
    }

    /**
     * @param string $fullTitle
     * @return self
     */
    public function setFullTitle($fullTitle)
    {
        $this->fullTitle = (string)$fullTitle;
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
     * @return self
     */
    public function setTitle($title)
    {
        $this->title = (string)$title;
        return $this;
    }

    /**
     * @return Hotel
     */
    public function getHotel()
    {
        return $this->hotel;
    }

    /**
     * @param Hotel $hotel
     * @return self
     */
    public function setHotel(Hotel $hotel)
    {
        $this->hotel = $hotel;
        return $this;
    }

}