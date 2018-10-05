<?php

namespace MBH\Bundle\ApiBundle\Lib;

use MBH\Bundle\BaseBundle\Lib\QueryBuilder;

class RoomTypesRequestParams extends RequestParams
{
    use LimitedTrait;
    use HotelIdsParamTrait;

    public function fillQueryBuilder(QueryBuilder $builder)
    {
        parent::fillQueryBuilder($builder);
        $builder = $this
            ->addLimitedCondition($builder)
            ->addHotelIdsCondition($builder);

        return $builder;
    }
}