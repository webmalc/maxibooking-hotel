<?php
/**
 * Created by Zavalyuk Alexandr (Zalex).
 * email: zalex@zalex.com.ua
 * Date: 12.07.16
 * Time: 17:11
 */

namespace MBH\Bundle\RestaurantBundle\Document;


use Doctrine\ODM\MongoDB\DocumentRepository;
use Doctrine\ODM\MongoDB\Query\Builder;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\HotelBundle\Document\Hotel;

abstract class AbstractRepository extends DocumentRepository
{
    /**
     * @param Helper $helper
     * @param Hotel $hotel
     * @return Builder
     */
    public function qbFindByHotelByCategoryId(Helper $helper, Hotel $hotel)
    {
        $dm = $this->getDocumentManager()->getRepository($this->getOwnCategoryName());
        $categories = $dm->findBy([
            'hotel.id' => $hotel->getId()
        ]);

        return $this->createQueryBuilder()
            ->field('category.id')->in($helper->toIds($categories));
    }

    public function findByHotelByCategoryId(Helper $helper, Hotel $hotel)
    {
        $qb = $this->qbFindByHotelByCategoryId($helper, $hotel);
        return $qb->getQuery()->execute();
    }

    abstract protected function getOwnCategoryName();

}