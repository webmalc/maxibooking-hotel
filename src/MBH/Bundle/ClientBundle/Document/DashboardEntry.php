<?php

namespace MBH\Bundle\ClientBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use MBH\Bundle\BaseBundle\Document\Base;
use MBH\Bundle\BaseBundle\Document\Traits\BlameableDocument;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique as MongoDBUnique;

/**
 * @ODM\Document(collection="DashboardEntry", repositoryClass="MBH\Bundle\ClientBundle\Document\DashboardEntryRepository")
 * @Gedmo\Loggable
 * @MongoDBUnique(fields={"text", "source"})
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class DashboardEntry extends Base
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
     * @ODM\Field(type="string")
     * @ODM\Index()
     * @Assert\NotNull()
     */
    private $text;
    
    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string")
     * @ODM\Index()
     * @Assert\NotNull()
     */
    private $type = 'info';

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string")
     * @ODM\Index()
     * @Assert\NotNull()
     */
    private $source;

    /**
     * @var \DateTime
     * @Gedmo\Versioned
     * @ODM\Date()
     * @Assert\Date()
     * @ODM\Index()
     */
    private $confirmedAt;

    /**
     * confirmedAt set
     *
     * @param \DateTime $confirmedAt
     * @return self
     */
    public function setConfirmedAt(\DateTime $confirmedAt = null): self
    {
        $this->confirmedAt = $confirmedAt;

        return $this;
    }

    /**
     * confirmedAt get
     *
     * @return \DateTime | null
     */
    public function getConfirmedAt()
    {
        return $this->confirmedAt;
    }

    /**
     * source set
     *
     * @param string $source
     * @return self
     */
    public function setSource(string $source): self
    {
        $this->source = $source;

        return $this;
    }

    /**
     * source get
     *
     * @return string
     */
    public function getSource(): string
    {
        return $this->source;
    }

    /**
     * type set
     *
     * @param string $type
     * @return self
     */
    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * type get
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * text set
     *
     * @param string $text
     * @return self
     */
    public function setText(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    /**
     * text get
     *
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }
}
