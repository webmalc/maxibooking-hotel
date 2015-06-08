<?php

namespace MBH\Bundle\PackageBundle\Document;

use Doctrine\ODM\MongoDB\DocumentRepository;

class OrderRepository extends DocumentRepository
{
    /**
     * @param $data
     * @return \MBH\Bundle\PackageBundle\Document\Order[]
     * @throws \Exception
     */
    public function fetch($data)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $qb = $this->createQueryBuilder('s');

        //confirmed
        if (isset($data['confirmed'])) {

            if (!empty($data['confirmed'])) {
                $qb->field('confirmed')->equals(true);
            } else {
                $qb->addOr($qb->expr()->field('confirmed')->exists(false));
                $qb->addOr($qb->expr()->field('confirmed')->equals(false));
            }
        }

        //paid status
        if (isset($data['paid']) && in_array($data['paid'], ['paid', 'part', 'not_paid'])) {
            switch ($data['paid']) {
                case 'paid':
                    $qb->field('isPaid')->equals(true);
                    break;
                case 'part':
                    $qb->field('isPaid')->equals(false)
                        ->field('paid')->gt(0)
                    ;
                    break;
                case 'not_paid':
                    $qb->field('isPaid')->equals(false)
                        ->field('paid')->equals(0)
                    ;
                    break;
                default:
                    break;
            }
        }

        //status
        if(isset($data['status']) && !empty($data['status'])) {
            $qb->field('status')->equals($data['status']);
        }

        if (isset($data['count']) && $data['count']) {
            $docs = $qb->getQuery()->count();
        } else {
            $docs = $qb->getQuery()->execute();
            if(isset($data['asIdsArray']) && !empty($data['asIdsArray'])) {


                $ids = [];
                foreach ($docs as $doc) {
                    $ids[] = $doc->getId();
                }
                return $ids;
            }
        }



        return $docs;
    }

}
