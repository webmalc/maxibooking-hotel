<?php

namespace MBH\Bundle\PackageBundle\Document\Partials;

trait InnTrait {

    /**
     * @ODM\Field(type="string") 
     * @Assert\Length(min=7,max=12)
     * @Assert\Type(type="digit", message="document.inn_trait.inn.value_must_by_digit")
     */
    protected $inn;

    /**
     * @return mixed
     */
    public function getInn()
    {
        return $this->inn;
    }

    /**
     * @param mixed $inn
     */
    public function setInn($inn)
    {
        $this->inn = $inn;
    }
}