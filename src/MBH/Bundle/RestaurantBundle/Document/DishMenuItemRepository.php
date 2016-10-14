<?php
/**
 * Created by Zavalyuk Alexandr (Zalex).
 * email: zalex@zalex.com.ua
 * Date: 12.07.16
 * Time: 17:09
 */

namespace MBH\Bundle\RestaurantBundle\Document;


class DishMenuItemRepository extends AbstractRepository
{
    /**
     * @return mixed
     */
    protected function getOwnCategoryName()
    {
        return 'MBHRestaurantBundle:DishMenuCategory';
    }

}