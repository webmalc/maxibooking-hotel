<?php
/**
 * Created by PhpStorm.
 * User: zalex
 * Date: 01.07.16
 * Time: 14:02
 */

namespace MBH\Bundle\RestaurantBundle\Document;


use Doctrine\ODM\MongoDB\DocumentRepository;
use Doctrine\ODM\MongoDB\Query\Builder;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\HotelBundle\Document\Hotel;

class IngredientRepository extends DocumentRepository
{
    public function findIsEnabled()
    {
        return $this->createQueryBuilder()
            ->field('isEnabled')->equals('true')
            ->getQuery()
            ->execute();
    }


    /**
     * @param Helper $helper
     * @param Hotel $hotel
     * @return Builder
     */
    public function qbFindByHotelByCategoryId(Helper $helper, Hotel $hotel)
    {
        $dm = $this->getDocumentManager()->getRepository('MBHRestaurantBundle:IngredientCategory');
        $categories = $dm->findBy([
            'hotel.id' => $hotel->getId()
        ]);

        return $this->createQueryBuilder()
            ->field('category.id')->in($helper->toIds($categories));
    }
}