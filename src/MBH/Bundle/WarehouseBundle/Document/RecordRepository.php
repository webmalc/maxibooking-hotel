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
		$queryBuilder = $this->createQueryBuilder('q');		
		
		if ($criteria->getRecordDateFrom() && $criteria->getRecordDateTo()) {
	        $queryBuilder->field('recordDate')->range($criteria->getRecordDateFrom(), $criteria->getRecordDateTo()->modify('+ 1 day'));
		} 
		elseif ($criteria->getRecordDateFrom()) {
			$queryBuilder->field('recordDate')->equals($criteria->getRecordDateFrom());
		} 
		elseif ($criteria->getRecordDateTo()) {
			$queryBuilder->field('recordDate')->equals($criteria->getRecordDateTo());
		}
		
		if ($criteria->getOperation()) {
	        $queryBuilder->field('operation')->equals($criteria->getOperation());
		}
		
		if ($criteria->getWareItem()) {
	        $queryBuilder->field('wareItem')->references($criteria->getWareItem());
		}
		
		if ($criteria->getHotel()) {
	        $queryBuilder->field('hotel')->references($criteria->getHotel());
		}
		
		if ($criteria->getSearch()) {
			$ids = $this->searchWareItem($criteria->getSearch()); // search wareItem collection
			
			$queryBuilder->field('wareItem.id')->in($ids);
		}
		
        $records = $queryBuilder
            ->skip($offset)
            ->limit($limit)
            ->sort('recordDate', 'desc')
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
