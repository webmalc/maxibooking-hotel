<?php

namespace MBH\Bundle\CashBundle\Document;

use MBH\Bundle\BaseBundle\Document\Base;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use MBH\Bundle\BaseBundle\Document\Traits\BlameableDocument;

/**
 * @ODM\Document()
 * @Gedmo\Loggable()
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class CashDocumentArticle extends Base
{
    use TimestampableDocument;
    use SoftDeleteableDocument;
    use BlameableDocument;

    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $code;

    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $title;

    /**
     * @var self
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\CashBundle\Document\CashDocumentArticle")
     */
    protected $parent;

    /**
     * @var self[]
     * @ODM\ReferenceMany(targetDocument="MBH\Bundle\CashBundle\Document\CashDocumentArticle", mappedBy="parent")
     */
    protected $children;
    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = $code;
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

    /**
     * @return CashDocumentArticle
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param CashDocumentArticle $parent
     */
    public function setParent(self $parent = null)
    {
        $this->parent = $parent;
    }

    /**
     * @return CashDocumentArticle[]
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param CashDocumentArticle[] $children
     */
    public function setChildren($children)
    {
        $this->children = $children;
    }
}