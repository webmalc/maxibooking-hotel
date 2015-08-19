<?php

namespace MBH\Bundle\BaseBundle\Filter;


use Doctrine\ODM\MongoDB\Mapping\ClassMetaData;
use Doctrine\ODM\MongoDB\Query\Filter\BsonFilter;
use MBH\Bundle\BaseBundle\Document\Traits\HotelableDocument;
use MBH\Bundle\BaseBundle\Service\HotelSelector;

class HotelableFilter extends BsonFilter
{
    protected $hotelSelector;

    public function setHotelSelector(HotelSelector $hotelSelector)
    {
        $this->hotelSelector = $hotelSelector;
    }

    public function addFilterCriteria(ClassMetadata $class)
    {
        if ($this->hotelSelector && $this->hotelSelector->getSelected()) {
            $traits = $class->getReflectionClass()->getTraits();
            foreach($traits as $trait) {
                if ($trait->getName() == HotelableDocument::class) {
                    return ['hotel.id' => $this->hotelSelector->getSelected()->getId()];
                };
            }
        }
        return [];
    }
}