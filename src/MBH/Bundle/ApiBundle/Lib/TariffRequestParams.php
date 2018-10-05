<?php

namespace MBH\Bundle\ApiBundle\Lib;

use MBH\Bundle\BaseBundle\Lib\QueryBuilder;

class TariffRequestParams extends RequestParams
{
    use HotelIdsParamTrait;

    private $isOnline = true;

    /**
     * @return bool
     */
    public function isOnline(): ?bool
    {
        return $this->isOnline;
    }

    /**
     * @param bool $isOnline
     * @return TariffRequestParams
     */
    public function setIsOnline(bool $isOnline): TariffRequestParams
    {
        $this->isOnline = $isOnline;

        return $this;
    }

    public function fillQueryBuilder(QueryBuilder $builder)
    {
        parent::fillQueryBuilder($builder);
        $builder->field('isOnline')->equals($this->isOnline());

        return $builder;
    }
}