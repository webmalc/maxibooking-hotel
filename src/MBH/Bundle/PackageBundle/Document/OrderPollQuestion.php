<?php

namespace MBH\Bundle\PackageBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ODM\EmbeddedDocument
 */
class OrderPollQuestion
{
    /**
     * @var PollQuestion
     * @ODM\ReferenceOne(targetDocument="PollQuestion")
     */
    protected $question;

    /**
     * @var mixed
     * @ODM\Field(type="string")
     * @Assert\NotNull()
     */
    public $code;

    /**
     * @var mixed
     * @ODM\Field(type="string")
     * @Assert\NotNull()
     */
    public $value;

    /**
     * @var boolean
     * @ODM\Field(type="boolean")
     * @Assert\NotNull()
     */
    public $isQuestion = true;

    /**
     * @return PollQuestion
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * @param PollQuestion $question
     * @return self
     */
    public function setQuestion(PollQuestion $question = null)
    {
        $this->question = $question;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param mixed $code
     * @return self
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     * @return self
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getIsQuestion()
    {
        return $this->isQuestion;
    }

    /**
     * @param boolean $isQuestion
     * @return self
     */
    public function setIsQuestion($isQuestion)
    {
        $this->isQuestion = $isQuestion;

        return $this;
    }


}
