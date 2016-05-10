<?php

namespace MBH\Bundle\WarehouseBundle\Document;

use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\BaseBundle\Service\Helper;


class RecordRepository extends DocumentRepository
{
    /**
     * @param mixed $criteria
     * @param int $offset
     * @param int $limit
     * @return Record[]
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function findByQueryCriteria($criteria, $offset = 0, $limit = 10)
    {
		$qb = $this->createQueryBuilder('q');
		
		if ($criteria->getRecordDateFrom() && $criteria->getRecordDateTo()) {
	        $qb->field('recordDate')->range($criteria->getRecordDateFrom(), $criteria->getRecordDateTo()->modify('+ 1 day'));
		} 
		elseif ($criteria->getRecordDateFrom()) {
			$qb->field('recordDate')->equals($criteria->getRecordDateFrom());
		} 
		elseif ($criteria->getRecordDateTo()) {
			$qb->field('recordDate')->equals($criteria->getRecordDateTo());
		}
		
		if ($criteria->getOperation()) {
	        $qb->field('operation')->equals($criteria->getOperation());
		}
		
		if ($criteria->getWareItem()) {
	        $qb->field('wareItem')->references($criteria->getWareItem());
		}
		
		if ($criteria->getHotel()) {
	        $qb->field('hotel')->references($criteria->getHotel());
		}
		
		if ($criteria->getSearch()) {
			$ids = $this->searchWareItem($criteria->getSearch()); // search wareItem collection
			
			$qb->field('wareItem.id')->in($ids);
		}
		
		$qb->sort($criteria->getSortBy(), $criteria->getSortDirection());
		
        $records = $qb
            ->skip($offset)
            ->limit($limit)
            ->sort('id', 'desc')
			->getQuery()
			->execute();
		
        return $records;
    }
	
    /**
     * @param mixed $criteria
     * @param int $offset
     * @param int $limit
     * @return Record[]
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function findInventoryByQueryCriteria($criteria, $offset = 0, $limit = 10)
    {
		$qb = $this->createQueryBuilder('q');
		
		if ($criteria->getWareItem()) {
	        $qb->field('wareItem')->references($criteria->getWareItem());
		}
		
		if ($criteria->getHotel()) {
	        $qb->field('hotel')->references($criteria->getHotel());
		}
		
		if ($criteria->getSearch()) {
			$ids = $this->searchWareItem($criteria->getSearch()); // search wareItem collection
			
			$qb->field('wareItem.id')->in($ids);
		}
		
		$qb->sort($criteria->getSortBy(), $criteria->getSortDirection());
		
        $records = $qb
            ->skip($offset)
            ->limit($limit)
            ->sort('id', 'desc')
			->getQuery()
			->execute();
		
        return $records;
    }
	
	/**
	 * Search ('%str%'-style) WareItem collection's `fullTitle` or `title`.
	 * 
	 * @param string $str
	 * @return array
	 */
	private function searchWareItem($str) {
		
		if (empty($str)) {
			return [];
		}
		
		$re = new \MongoRegex("/{$str}/ui");
		
		$qb = $this->getDocumentManager()
			->getRepository('MBHWarehouseBundle:WareItem')
			->createQueryBuilder('w');

		$ware = $qb
			->addOr($qb->expr()->field('fullTitle')->equals($re))
			->addOr($qb->expr()->field('title')->equals($re))
			->getQuery()
			->execute();

		return Helper::toIds($ware);
	}

}
