<?php

namespace MBH\Bundle\BaseBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;


/**
 * @ODM\MappedSuperclass
 */
class Base 
{
    /**
     * @var string
     * @ODM\Id
     */
    protected $id;

    /**
     * Get id
     *
     * @return id $id
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * @return string
     */
    public function __toString()
    {
        if (method_exists($this, 'getName')) {
            return $this->getName();
        }
        
        return $this->getId();
    }

    public function __clone()
    {
        $this->id = null;
    }
    
}
