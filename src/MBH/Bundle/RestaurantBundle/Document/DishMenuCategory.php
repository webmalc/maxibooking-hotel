<?php
/**
 * Created by PhpStorm.
 * User: zalex
 * Date: 22.06.16
 * Time: 15:11
 */

namespace MBH\Bundle\RestaurantBundle\Document;


use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique as MongoDBUnique;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use MBH\Bundle\BaseBundle\Document\Base;
use MBH\Bundle\BaseBundle\Document\Traits\BlameableDocument;
use MBH\Bundle\HotelBundle\Document\Hotel;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ODM\Document(collection="DishMenuCategory")
 * @Gedmo\Loggable
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 * @MongoDBUnique(fields={"fullTitle","hotel"}, message="validator.document.category.unique")
 * validator.document.category.unique
 */
class DishMenuCategory extends Base
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
     *      minMessage="validator.document.dishMenuCategory.min_name",
     *      max=100,
     *      maxMessage="validator.document.dishMenuCategory.max_name"
     * )
     */
    protected $fullTitle = '';

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string", name="title")
     * @Assert\Length(
     *      min=2,
     *      minMessage="validator.document.dishMenuCategory.min_name",
     *      max=100,
     *      maxMessage="validator.document.dishMenuCategory.max_name"
     * )
     */
    protected $title;
    
    /**
     * @var Hotel
     * @Gedmo\Versioned
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\HotelBundle\Document\Hotel", inversedBy="dishMenuCategories")
     * @Assert\NotNull(message="validator.document.dishMenuCategory.hotel")
     */
    protected $hotel;

    /**
     * @ODM\ReferenceMany(targetDocument="MBH\Bundle\RestaurantBundle\Document\DishMenuItem", mappedBy="category", cascade={"remove"} )
     */
    protected $dishMenuItems;

    /**
     * DishMenuCategory constructor.
     *
     */
    public function __construct()
    {
        $this->dishMenuItems = new ArrayCollection();
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
     * @return self
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
     * @return self
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getHotel()
    {
        return $this->hotel;
    }

    /**
     * @param mixed $hotel
     * @return $this
     */

    public function setHotel(Hotel $hotel)
    {
        $this->hotel = $hotel;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDishMenuItems()
    {
        return $this->dishMenuItems;
    }

    /**
     * @param mixed $dishMenuItems
     * @return $this
     */
    public function setDishMenuItems($dishMenuItems)
    {
        $this->dishMenuItems = $dishMenuItems;
        return $this;
    }

}