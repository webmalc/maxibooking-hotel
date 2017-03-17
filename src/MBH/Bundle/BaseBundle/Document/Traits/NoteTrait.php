<?php
/**
 * Created by PhpStorm.
 * User: webmalc
 * Date: 11/15/16
 * Time: 12:34 PM
 */

namespace MBH\Bundle\BaseBundle\Document\Traits;


use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

trait NoteTrait
{
    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string", name="note")
     */
    public $note;

    /**
     * Set note
     *
     * @param string $note
     * @return self
     */
    public function setNote(string $note = null)
    {
        $this->note = $note;

        return $this;
    }

    /**
     * Get note
     *
     * @return string $note
     */
    public function getNote(): string
    {
        return $this->note ?? "";
    }
}