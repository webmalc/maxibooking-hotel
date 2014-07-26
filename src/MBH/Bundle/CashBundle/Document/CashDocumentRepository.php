<?php

namespace MBH\Bundle\CashBundle\Document;

use Doctrine\ODM\MongoDB\DocumentRepository;

class CashDocumentRepository extends DocumentRepository
{

    /**
     * @param string $type in || out
     * @return int
     * @throws Exception
     */
    public function total($type, $search, \DateTime $begin = null, \DateTime $end = null)
    {
        if (!in_array($type, ['in', 'out'])) {
            throw new Exception('Invalid type');
        }

        $qb = $this->createQueryBuilder('CashDocument');
        
        if (!empty($search)) {
            $qb->addOr($qb->expr()->field('total')->equals((int) $search));
            $qb->addOr($qb->expr()->field('prefix')->equals(new \MongoRegex('/.*' . $search . '.*/ui')));
        }
        if ($type == 'in') {
            $qb->field('operation')->notEqual('out');
        } else {
            $qb->field('operation')->equals('out');
        }
        
        if (!empty($begin)) {
            $qb->field('createdAt')->gte($begin);
        }
        
        if (!empty($end)) {
            $qb->field('createdAt')->lte($end);
        }

        $qb->map('function() { emit(1, this.total); }')
           ->reduce('function(k, vals) {
                    var sum = 0;
                    for (var i in vals) {
                        sum += vals[i];
                    }
                    return sum;
            }');

        $result = $qb->getQuery()->execute();
        
        return (isset($result[0]['value'])) ? $result[0]['value'] : 0;
    }

}
