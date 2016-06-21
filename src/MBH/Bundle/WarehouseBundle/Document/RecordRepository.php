<?php

namespace MBH\Bundle\WarehouseBundle\Document;

use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\BaseBundle\Service\Helper;


class RecordRepository extends DocumentRepository
{
	/**
	 * List of wareItems.id(s)
	 * @var array
	 */
	private $wareItems = [];


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
	 * Get aggregated (groupped) results from wareRecords: quantity.
	 * Prepare simple array for future use of the following structure:
	 * [wareItem.id => quantity]
	 * Filter results by $criteria conditions. But do not sort!
	 * 
     * @param mixed $criteria
     * @param int $offset
     * @param int $limit
     * @return array
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function fetchSummary($criteria)
    {
		$qb = $this->createQueryBuilder('q');
		
		// a set of conditions (if any)
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
		
		$qb->group(
			['wareItem' => 1, ],
			['qtty' => 0, ]
		);
		
		$qb->reduce(
            "function (doc, v) {
				if (obj.operation === 'in') {
					v.qtty += doc.qtty;
				} else if (obj.operation === 'out') {
					v.qtty -= doc.qtty;
				}
        }");
		
        $res = $qb
			->getQuery()
			->execute();
		
		$summary = [];
		
		foreach (iterator_to_array($res) as $v) {
			$wid = $v['wareItem']['$id']->{'$id'};
			$this->wareItems[] = $wid; // collection of wareItem.id(s)
			
			$summary[$wid] = $v['qtty'];
		}
		
        return $summary;
    }
	
	/**
	 * Get wareItems details, selecting entries by a list of ids.
	 * Add qtty column to results. Allow to sort by it.
	 * Results sorting is done here.
	 * 
	 * @return array
	 */
	public function getItemsByIds($criteria, $summary, $offset = 0, $limit = 10) {
		
		$qb = $this->getDocumentManager()
			->getRepository('MBHWarehouseBundle:WareItem')->createQueryBuilder('i')
			->field('id')->in($this->wareItems)		
            ->skip($offset)
            ->limit($limit)
			//->sort($criteria->getSortBy(), $criteria->getSortDirection())
		;
		
        $res = $qb->getQuery()->execute();
		
		// the following is going to serve for the purpose of sorting results by 'qtty' table column
		
		$items = [];
		
		foreach (iterator_to_array($res) as $v) {
			$v1 = $v;
			$v1->qtty = $summary[$v->getId()];  // add new entity to the results
			$items[] = $v1;
		}
		
		// use usort() for sortng array by qtty (asc or desc etc.)
		if ($criteria->getSortBy() == 'qtty') {
			if ($criteria->getSortDirection() > 0) { // asc
				usort($items, function ($a, $b) {
					if ($a->qtty == $b->qtty) {
						return 0;
					}	
					
					return ($a->qtty < $b->qtty) ? -1 : 1;
				});
			} else { // desc order
				usort($items, function ($a, $b) {
					if ($a->qtty == $b->qtty) {
						return 0;
					}	
					
					return ($a->qtty > $b->qtty) ? -1 : 1;				
				});
			}
		} 
		
		return $items;
	}

	/**
	 * Search (in LIKE '%str%'-style) WareItem collection's `fullTitle` or `title`.
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
