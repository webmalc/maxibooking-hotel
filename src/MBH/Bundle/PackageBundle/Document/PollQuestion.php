<?php

namespace MBH\Bundle\PackageBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\Document(repositoryClass="MBH\Bundle\PackageBundle\Document\PollQuestionRepository")
 */
class PollQuestion
{
    /**
     * @var string
     * @ODM\Id(strategy="NONE")
     */
    protected $id;

    /**
     * @var int
     * @ODM\Field(type="integer")
     */
    protected $sort = 0;

    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $text;

    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $category;

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return self
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param string $category
     * @return self
     */
    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param string $text
     * @return self
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * @return int
     */
    public function getSort()
    {
        return $this->sort;
    }

    /**
     * @param int $sort
     * @return self
     */
    public function setSort($sort)
    {
        $this->sort = $sort;

        return $this;
    }

    public function __toString()
    {
        return $this->getText();
    }
}
