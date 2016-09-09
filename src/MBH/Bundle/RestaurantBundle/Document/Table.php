<?php
/**
 * Created by PhpStorm.
 * User: zalex
 * Date: 05.07.16
 * Time: 14:29
 */

namespace MBH\Bundle\RestaurantBundle\Document;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use MBH\Bundle\BaseBundle\Document\Base;
use MBH\Bundle\BaseBundle\Document\Traits\BlameableDocument;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\RestaurantBundle\Validator\Constraints as MBHValidator;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique as MongoDBUnique;


/**
 * @ODM\Document(collection="Tables")
 * @Gedmo\Loggable()
 * @MBHValidator\Table
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 * @MongoDBUnique(fields={"fullTitle"}, message="validator.document.table.unique")
 * @ODM\HasLifecycleCallbacks
 */
class Table extends Base
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
     *      min=1,
     *      minMessage="validator.document.table.min_name",
     *      max=100,
     *      maxMessage="validator.document.table.max_name"
     * )
     */
    protected $fullTitle;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string", name="title")
     * @Assert\Length(
     *      min=1,
     *      minMessage="validator.document.table.min_name",
     *      max=100,
     *      maxMessage="validator.document.table.max_name"
     * )
     */
    protected $title;
    /**
     * @var Hotel
     * @Gedmo\Versioned
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\HotelBundle\Document\Hotel")
     * @Assert\NotNull(message="validator.document.table.hotel")
     */
    protected $hotel;

    /**
     * @var ArrayCollection
     * @ODM\ReferenceMany(targetDocument="MBH\Bundle\RestaurantBundle\Document\Table", inversedBy="shifted", cascade="persist")
     */
    protected $withShifted;

    /**
     * @var ArrayCollection
     * @ODM\ReferenceMany(targetDocument="MBH\Bundle\RestaurantBundle\Document\Table", mappedBy="withShifted")
     */
    protected $shifted;

    /**
     * @var TableType $category
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\RestaurantBundle\Document\TableType", inversedBy="categories")
     * @Assert\NotNull()
     */
    protected $category;
    /**
     * @var Chair $category
     * @ODM\ReferenceMany(targetDocument="MBH\Bundle\RestaurantBundle\Document\Chair", mappedBy="table" , cascade="persist")
     * @Assert\NotNull()
     */
    protected $chairs;

    /**
     * TableType constructor.
     */
    public function __construct()
    {
        $this->withShifted= new ArrayCollection();
        $this->shifted = new ArrayCollection();
        $this->chairs = new ArrayCollection();
    }
    /**
     * @return ArrayCollection
     */
    public function getWithShifted()
    {
        return $this->withShifted;
    }
    /**
     * @return string
     */
    public function addWithShifted(Table $table)
    {
        $table->shifted[]=$this;
        $this->withShifted[]=$table;
    }
    /**
     * @return string
     */
    public function getShifted()
    {
        return $this->shifted;
    }

    /**
     * @param string $shifted
     */
    public function addShifted(Table $table)
    {
        $this->shifted[]=$table;
        $table->withShifted[]=$this;

    }
    /**
     * @return ArrayCollection
     */
    public function getChairs()
    {
        return $this->chairs;
    }

    /**
     * @param Chair $chairs
     */
    public function addChairs(Chair $chairs)
    {
        $this->chairs->add($chairs);
        $chairs->setTable($this);
    }

    /**
     * @return TableType
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param TableType $category
     */
    public function setCategory(TableType $category)
    {
        $this->category = $category;
    }
    /**
     * @param Hotel $hotel
     */
    public function setHotel(Hotel $hotel)
    {
        $this->hotel = $hotel;
    }
    public function getHotel(): Hotel
    {
        return $this->category->getHotel();
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
     */
    public function setFullTitle($fullTitle)
    {
        $this->fullTitle = $fullTitle;
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
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }


}