<?php

namespace MBH\Bundle\PackageBundle\Document;

use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\HotelBundle\Document\Hotel;

/**
 * Class PollQuestionRepository
 */
class PollQuestionRepository extends DocumentRepository
{
    /**
     * @param Hotel $hotel
     * @return OrderPollQuestion[]
     */
    public function findByHotel(Hotel $hotel)
    {
        $roomTypes = $hotel->getRoomTypes();
        $roomTypeIDs = [];
        foreach($roomTypes as $roomType) {
            $roomTypeIDs[] = $roomType->getId();
        }

        if(!$roomTypeIDs) {
            return [];
        }

        $packageRepository = $this->dm->getRepository('MBHPackageBundle:Package');
        $packages = $packageRepository->createQueryBuilder()->hydrate(false)
            ->select('order')->field('roomType.id')->in($roomTypeIDs)->getQuery()->toArray();
        $ids = array_column(array_column($packages, 'order'), '$id');
        if(!$ids) {
            return [];
        }

        $orderRepository = $this->dm->getRepository('MBHPackageBundle:Order');
        /** @var Order[] $orders */
        $orders = $orderRepository->createQueryBuilder()->field('id')->in($ids)->getQuery()->execute();
        $result = [];
        foreach($orders as $order) {
            $result = array_merge($result, iterator_to_array($order->getPollQuestions()));
        }

        return $result;
    }
}
