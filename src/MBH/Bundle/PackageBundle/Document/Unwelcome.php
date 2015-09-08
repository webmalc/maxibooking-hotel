<?php

namespace MBH\Bundle\PackageBundle\Document;

/**
 * Class Unwelcome
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
class Unwelcome implements \JsonSerializable
{
    /**
     * @var bool
     */
    protected $isAggressor;

    /**
     * @var string
     */
    protected $comment;

    /**
     * @var bool
     */
    protected $isMy;

    /**
     * @var \DateTime|null
     */
    protected $createdAt;

    /**
     * @return boolean
     */
    public function getIsAggressor()
    {
        return $this->isAggressor;
    }

    /**
     * @param boolean $aggressor
     * @return self
     */
    public function setIsAggressor($aggressor)
    {
        $this->isAggressor = $aggressor;
        return $this;
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param string $comment
     * @return self
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isIsMy()
    {
        return $this->isMy;
    }

    /**
     * @param boolean $isMy
     * @return self
     */
    public function setIsMy($isMy)
    {
        $this->isMy = $isMy;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime|null $createdAt
     * @return self
     */
    public function setCreatedAt(\DateTime $createdAt = null)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function jsonSerialize()
    {
        return [
            'comment' => $this->getComment(),
            'isAggressor' => $this->getIsAggressor(),
        ];
    }
}