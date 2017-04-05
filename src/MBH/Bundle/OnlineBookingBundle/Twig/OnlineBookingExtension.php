<?php

namespace MBH\Bundle\OnlineBookingBundle\Twig;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\OnlineBookingBundle\Document\LeftRoom;
use MBH\Bundle\PackageBundle\Lib\SearchResult;

class OnlineBookingExtension extends \Twig_Extension
{
    /** @var DocumentManager */
    private $dm;

    const MIN_THRESHOLD = 3;

    const MAX_THRESHOLD = 7;

    /** @var  int */
    protected $minThreshold;

    /** @var  int */
    protected $maxThreshold;

    /**
     * OnlineBookingExtension constructor.
     * @param DocumentManager $dm
     */
    public function __construct(DocumentManager $dm, array $thresholds = null)
    {
        $this->dm = $dm;
        if (!$thresholds) {
            $this->minThreshold = self::MIN_THRESHOLD;
            $this->maxThreshold = self::MAX_THRESHOLD;
        } else {
            $this->minThreshold = $thresholds['min_threshold'];
            $this->maxThreshold = $thresholds['max_threshold'];
        }
    }



    public function getFunctions()
    {
        return [
            'left_sale' => new \Twig_SimpleFunction('left_sale', [$this, 'leftSale'], ['is_safe' => ['html']]),
            'count_prices'=> new \Twig_SimpleFunction('count_prices', [$this, 'countPrices'], ['is_safe' => ['html']])
        ];
    }

    public function countPrices(array $searchResults): array
    {
        $searches = $searchResults;
        usort(
            $searches,
            function ($cmpA, $cmpB) {
                /** @var $cmpA SearchResult */
                return $cmpA->getPrice($cmpA->getAdults(), $cmpA->getChildren()) <=> $cmpB->getPrice( $cmpB->getAdults(), $cmpB->getChildren());
            }
        );

        $oldPrice = $newPrice = $discount = null;
        $firstSearch = reset($searches);
        $newPrice = $firstSearch->getPrice($firstSearch->getAdults(), $firstSearch->getChildren());
        foreach ($searches as $search) {
            $promotion = $search->getTariff()->getDefaultPromotion()??false;
            if ($promotion && $promotion->getDiscount()??false) {
                $discount = $promotion->getDiscount();
                $currentSearchPrice = $search->getPrice($search->getAdults(), $search->getChildren());
                $oldPrice = $currentSearchPrice * 100 /(100 - $discount);
                break;
            }
        }

        return [
            'old' => $oldPrice,
            'new' => $newPrice,
        ];
    }

    public function leftSale(int $actualRoomsCount = null, string $leftRoomKey = null)
    {
        if (!$actualRoomsCount || !$leftRoomKey) {
            return 0;
        }

        $maxOutput = min($this->maxThreshold, $actualRoomsCount);
        if ($maxOutput < $this->maxThreshold) {
            return $maxOutput;
        }

        $leftRoom = $this->dm->getRepository('MBHOnlineBookingBundle:LeftRoom')->findOneBy(
            [
                'key' => $leftRoomKey,
            ]
        );

        $now = new \DateTime("now");
        $start = rand($this->minThreshold, $this->maxThreshold);
        if (!$leftRoom) {
            $leftRoom = new LeftRoom();
            $leftRoom
                ->setKey($leftRoomKey)
                ->setDate($now)
                ->setCount($start);
        }

        $interval = date_diff($now, $leftRoom->getDate(), true);

        if (1 <= $interval->d){
            if ($this->minThreshold <= $leftRoom->getCount()) {
                $leftRoom->setCount($leftRoom->getCount() - 1);
            } else {
                $leftRoom->setCount($start);
            }
        }
        $this->dm->persist($leftRoom);
        $this->dm->flush();

        return $leftRoom->getCount();
    }


}