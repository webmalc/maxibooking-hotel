<?php

namespace MBH\Bundle\ApiBundle\Lib;

use MBH\Bundle\BaseBundle\Lib\QueryBuilder;

trait HotelIdsParamTrait
{
    /** @var array */
    private $hotelIds;

    /**
     * @return array
     */
    public function getHotelIds(): ?array
    {
        return $this->hotelIds;
    }

    /**
     * @param array $hotelIds
     * @return self
     */
    public function setHotelIds(array $hotelIds): self
    {
        $this->hotelIds = $hotelIds;

        return $this;
    }

    /**
     * @param QueryBuilder $builder
     * @return QueryBuilder
     */
    public function addHotelIdsCondition(QueryBuilder $builder)
    {
        $builder->inIfNotEmpty('hotel.id', $this->getHotelIds());

        return $builder;
    }
}