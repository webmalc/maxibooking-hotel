<?php
/**
 * Created by PhpStorm.
 * User: zalex
 * Date: 17.06.16
 * Time: 12:10
 */

namespace MBH\Bundle\RestaurantBundle\Document;


use MBH\Bundle\BaseBundle\Document\Base;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use MBH\Bundle\BaseBundle\Document\Traits\BlameableDocument;
use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique as MongoDBUnique;

/**
 * @ODM\Document(collection="Ingredients")
 * @Gedmo\Loggable
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 * @MongoDBUnique(fields="fullTitle", message="validator.document.ingredient.notunique")
 */
class Ingredient extends Base
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
     * @ODM\ReferenceOne()
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
     *      minMessage="Слишком короткое имя",
     *      max=100,
     *      maxMessage="Слишком длинное имя"
     * )
     */
    protected $fullTitle;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string", name="title")
     * @Assert\Length(
     *      min=2,
     *      minMessage="Слишком короткое имя",
     *      max=100,
     *      maxMessage="Слишком длинное имя"
     * )
     */
    protected $title;

    /**
     * @var int
     * @Gedmo\Versioned()
     * @ODM\Field(type="float", name="price")
     * @Assert\Type(type="numeric")
     * @Assert\Range(
     *      min=0,
     *      minMessage="Цена не может быть меньше нуля"
     * )
     */
    protected $price = 0;

    /**
     * @var int
     * @Gedmo\Versioned()
     * @ODM\Field(type="float", name="output")
     * @Assert\Type(type="numeric")
     * @Assert\Range(
     *      min=1,
     *      max=100,
     *      minMessage="Выход продукта не может быть 0%",
     *      maxMessage="Выход продукта не может быть больше 100%"
     *
     * )
     */
    protected $output = 100;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string")
     * @Assert\NotNull()
     * @Assert\Choice(choices = {"per_kg", "per_grm", "per_ml", "per_l", "per_piece"})
     */
    protected $calcType;

    /**
     * @return string
     */
    public function getCalcType()
    {
        return $this->calcType;
    }

    /**
     * @param string $calcType
     */
    public function setCalcType(string $calcType)
    {
        $this->calcType = $calcType;
    }

    /**
     * @return mixed
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param mixed $category
     * @return self
     */
    public function setCategory(IngredientCategory $category)
    {
        $this->category = $category;
        return $this;
    }

    /**
     * @return string
     */
    public function getFullTitle()
    {
        return $this->fullTitle;
    }

    /**
     * @param mixed $fullTitle
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
        return $this->title?:$this->getFullTitle();
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return float
     */
    public function getPrice() :float
    {
        return $this->price;
    }

    /**
     * @param float $price
     */
    public function setPrice(float $price)
    {
        $this->price = $price;
    }

    /**
     * @return float
     */
    public function getOutput(): float
    {
        return $this->output;
    }

    /**
     * @param float $output
     */
    public function setOutput(float $output)
    {
        $this->output = $output;
    }


    public function getHotel()
    {
        return $this->getCategory()->getHotel();
    }

}