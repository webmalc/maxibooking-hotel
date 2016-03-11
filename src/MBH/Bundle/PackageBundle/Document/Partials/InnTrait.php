<?php

namespace MBH\Bundle\PackageBundle\Document\Partials;

trait InnTrait {

    /**
     * @ODM\String
     * @Assert\Length(min=8,max=12)
     * @Assert\Type(type="digit", message="Значение должно быть числом")
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