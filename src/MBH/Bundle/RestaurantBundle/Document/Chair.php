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
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ODM\Document(collection="Chair")
 * @Gedmo\Loggable()
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class Chair extends Base
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
     * @var boolean
     * @Gedmo\Versioned
     * @ODM\Boolean()
     * @Assert\NotNull()
     * @Assert\Type(type="boolean")
     */
    protected $type;

    /**
     *@ODM\ReferenceOne(targetDocument="MBH\Bundle\RestaurantBundle\Document\Table", inversedBy="chairs")
     *@Assert\NotNull()
     */
    protected $table;

    /**
     * @return mixed
     */
    public function getTable()
    {
        return $this->table;
    }


    public function setTable(Table $table)
    {
        $table->getChairs()->add($this);
        $this->table = $table;
    }


    /**
     * @return boolean
     */
    public function isType(): bool
    {
        return $this->type;
    }

    /**
     * @param boolean $type
     */
    public function setType(bool $type)
    {
        $this->type = $type;
    }





}