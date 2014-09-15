<?php

namespace MBH\Bundle\PackageBundle\Document;

use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Form\RoomType;

class PackageRepository extends DocumentRepository
{
    /**
     * @param $data
     * @return \MBH\Bundle\PackageBundle\Document\Package[]
     * @throws \Exception
     */
    public function fetch($data)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->getDocumentManager();
        $qb = $this->createQueryBuilder('s');

        //hotel
        if(isset($data['hotel']) && !empty($data['hotel'])) {
            if(!$data['hotel'] instanceof Hotel) {
                $data['hotel'] = $dm->getRepository('MBHHotelBundle:Hotel')->find($data['hotel']);

                if (!$data['hotel']) {
                    throw new \Exception('Hotel not found.');
                }
            }
            $roomTypesIds = [];
            foreach ($data['hotel']->getRoomTypes() as $roomType) {
                $roomTypesIds[] = $roomType->getId();
            }
            if (count($roomTypesIds)) {
                $qb->field('roomType.id')->in($roomTypesIds);
            }
        }

        //roomType
        if(isset($data['roomType']) && !empty($data['roomType'])) {
            if($data['roomType'] instanceof RoomType) {
                $data['roomType'] = $data['roomType']->getId();
            }
            $qb->field('roomType.id')->equals($data['roomType']);
        }

        //status
        if(isset($data['status']) && !empty($data['status'])) {
            $qb->field('status')->equals($data['status']);
        }

        //paid status
        if(isset($data['paid']) && in_array($data['paid'], ['paid', 'part', 'not_paid'])) {

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

        //get dates
        $dateType = 'begin';
        if(isset($data['dates']) && !empty($data['dates'])) {
            $dateType = $data['dates'];
        }

        //begin
        if(isset($data['begin']) && !empty($data['begin'])) {
            if(!$data['begin'] instanceof \DateTime) {
                $data['begin'] = \DateTime::createFromFormat('d.m.Y H:i:s', $data['begin'] . ' 00:00:00');
            }

            $qb->field($dateType)->gte($data['begin']);
        }

        //end
        if(isset($data['end']) && !empty($data['end'])) {
            if(!$data['end'] instanceof \DateTime) {
                $data['end'] = \DateTime::createFromFormat('d.m.Y H:i:s', $data['end'] . ' 00:00:00');
            }

            $qb->field($dateType)->lte($data['end']);
        }

        //query
        if(isset($data['query']) && !empty($data['query'])) {
            $query = trim($data['query']);
            $tourists = $dm->getRepository('MBHPackageBundle:Tourist')
                           ->createQueryBuilder('t')
                           ->field('fullName')->equals(new \MongoRegex('/.*' . $query . '.*/ui'))
                           ->getQuery()
                           ->execute()
            ;

            $touristsIds = [];
            foreach ($tourists as $tourist) {
                $touristsIds[] = $tourist->getId();
            }

            if (count($touristsIds)) {
                $qb->addOr($qb->expr()->field('tourists.id')->in($touristsIds));
                $qb->addOr($qb->expr()->field('mainTourist.id')->in($touristsIds));
            }

            $qb->addOr($qb->expr()->field('numberWithPrefix')->equals(new \MongoRegex('/.*' . $query . '.*/ui')));
        }

        //order
        $order = 'createdAt';
        $dir = 'desc';
        $cols = [1 => 'number', 2 => 'begin', 3 => 'roomType', 4 => 'createdAt', 5 => 'mainTourist', 6 => 'price'];
        if (isset($data['order']) && isset($cols[$data['order']])) {
            $order = $cols[$data['order']];
        }
        if (isset($data['dir']) && in_array($data['dir'], ['asc', 'desc'])) {
            $dir = $data['dir'];
        }
        $qb->sort($order, $dir);

        // paging
        if (isset($data['skip'])) {
            $qb->skip($data['skip']);
        }
        if (isset($data['limit'])) {
            $qb->limit($data['limit']);
        }

        //deleted
        if (isset($data['deleted']) && $data['deleted']) {
            $dm->getFilterCollection()->disable('softdeleteable');
        }
        $docs = $qb->getQuery()->execute();
        if (isset($data['deleted']) && $data['deleted']) {
            $dm->getFilterCollection()->enable('softdeleteable');
        }

        return $docs;
    }

}
