<?php


namespace MBH\Bundle\PackageBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Blameable\Traits\BlameableDocument;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use MBH\Bundle\BaseBundle\Document\Base;
use MBH\Bundle\HotelBundle\Document\Hotel;
use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique as MongoDBUnique;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ODM\Document(collection="DeleteReasonCategory", repositoryClass="DeleteReasonCategoryRepository")
 * @Gedmo\Loggable
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 * @MongoDBUnique(fields={"fullTitle"}, message="validator.document.category.unique")
 */
class DeleteReasonCategory extends Base
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
     * @ODM\ReferenceMany(targetDocument="MBH\Bundle\PackageBundle\Document\DeleteReason", mappedBy="category", cascade={"remove"} )
     */
    protected $deleteReasons;

    /**
     * DishMenuCategory constructor.
     *
     */
    public function __construct()
    {
        $this->deleteReasons = new ArrayCollection();
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
    public function getDeleteReasons()
    {
        return $this->deleteReasons;
    }

    /**
     * @param mixed $deleteReasons
     * @return $this
     */
    public function setDeleteReasons(ArrayCollection $deleteReasons)
    {
        foreach ($deleteReasons as $reason) {
            /** @var DeleteReason $reason */
            $reason->setCategory($this);
        }
        $this->deleteReasons = $deleteReasons;

        return $this;
    }
}