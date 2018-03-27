<?php

namespace MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\HotelBundle\Document\RoomTypeCategory;
use MBH\Bundle\HotelBundle\Document\RoomTypeImage;
use MBH\Bundle\PackageBundle\Document\SearchQuery;
use MBH\Bundle\PackageBundle\Lib\SearchResult;
use MBH\Bundle\PriceBundle\Document\Promotion;
use MBH\Bundle\PriceBundle\Document\Special;

/**
 * Class OnlineResultInstance
 * @package MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper
 */
class OnlineResultInstance
{
    const MIN_THRESHOLD = 3;

    const MAX_THRESHOLD = 7;
    /** @var  int */
    protected $minThreshold;
    /** @var  int */
    protected $maxThreshold;
    /** @var  DocumentManager */
    private $dm;
    /** @var  RoomType */
    protected $roomType;
    /** @var  ArrayCollection */
    protected $results;
    /** @var  bool */
    protected $additional;
    /** @var  SearchQuery */
    protected $query;
    /** @var  array */
    protected $forceRoomType;
    /** @var  string */
    protected $type;
    /** @var Special */
    protected $special;
    /** @var string */
    protected $queryId;


    /**
     * OnlineResultInstance constructor.
     * @param array|null $thresholds
     */
    public function __construct(array $thresholds = null)
    {
        $this->results = new ArrayCollection();
        if (!$thresholds) {
            $this->minThreshold = self::MIN_THRESHOLD;
            $this->maxThreshold = self::MAX_THRESHOLD;
        } else {
            $this->minThreshold = $thresholds['min_threshold'];
            $this->maxThreshold = $thresholds['max_threshold'];
        }
    }


    /**
     * @return RoomType|RoomTypeCategory
     */
    public function getRoomType()
    {
        return $this->roomType;
    }

    /**
     * @param RoomType|RoomTypeCategory $roomType
     */
    public function setRoomType($roomType)
    {
        $this->roomType = $roomType;
    }

    /**
     * @return ArrayCollection
     */
    public function getResults(): ArrayCollection
    {
        return $this->results;
    }

    /**
     * @param SearchResult $result
     */
    public function addResult(SearchResult $result)
    {
        $this->results->add($result);
    }

    /**
     * @return bool
     */
    public function isAdditional(): bool
    {
        return $this->additional;
    }

    /**
     * @param bool $additional
     */
    public function setAdditional(bool $additional)
    {
        $this->additional = $additional;
    }

    /**
     * @return SearchQuery
     */
    public function getQuery(): SearchQuery
    {
        return $this->query;
    }

    /**
     * @param SearchQuery $query
     */
    public function setQuery(SearchQuery $query)
    {
        $this->query = $query;
    }

    /**
     * @return array
     */
    public function getForceRoomType(): array
    {
        return $this->forceRoomType;
    }

    /**
     * @param array $forceRoomType
     */
    public function setForceRoomType(array $forceRoomType)
    {
        $this->forceRoomType = $forceRoomType;
    }

    /**
     * @return RoomTypeImage
     */
    public function getMainImage(): ?RoomTypeImage
    {
        return $this->getImages()['mainimage'];
    }

    /**
     * @return string
     */
    public function getLeftRoomKey(): string
    {
        return $this->roomType->getId().
            $this->getFirstResult()->getTariff()->getId().
            $this->getQuery()->begin->format('dmY').
            $this->getQuery()->end->format('dmY');
    }


    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type)
    {
        $this->type = $type;
    }


    /**
     * @return array
     */
    public function getImages()
    {
        $images = [];
        $mainImage = null;

        if ($this->roomType instanceof RoomTypeCategory) {
            /** @var RoomTypeCategory $roomTypeCategory */
            $mainImage = $this->roomType->getMainImage();
            $roomTypes = $this->roomType->getTypes();
            foreach ($roomTypes as $roomType) {
                $images = array_merge($roomType->getImages()->toArray());
            }
        } elseif ($this->roomType instanceof RoomType) {
            $mainImage = $this->roomType->getMainImage();
            $images = $this->roomType->getImages()->toArray();
        }

        return [
            'images' => $images,
            'mainimage' => $mainImage,
        ];
    }

    public function getDates()
    {
        $firstResult = $this->results->first();

        return [
            'begin' => $firstResult->getBegin(),
            'end' => $firstResult->getEnd(),
        ];
    }

    public function isCategory()
    {
        return ($this->getRoomType() instanceof RoomTypeCategory);
    }

    public function getFirstResult()
    {
        return $this->getResults()->first();
    }

    public function getRemain()
    {
        return $this->getFirstResult()->getRoomsCount();
    }

    /**
     * @return Special
     */
    public function getSpecial(): Special
    {
        return $this->special;
    }

    /**
     * @param Special $special
     */
    public function setSpecial(Special $special)
    {
        $this->special = $special;
    }

    public function isSameVirtualRoomInSpec()
    {
        $result = false;
        if (null !== $this->results && null !== $this->special) {
            $specialVirtualRoom = $this->special->getVirtualRoom();
            /** @var SearchResult $result */
            $searchResult = $this->results->first();
            $virtualRoom = $searchResult->getVirtualRoom();
            if ($specialVirtualRoom && $virtualRoom && $specialVirtualRoom->getId() === $virtualRoom->getId()) {
                $result = true;
            }
        }

        return $result;
    }

    public function getPrices(): array
    {
        $searches = $this->results->toArray();
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
            /** @var Promotion $promotion */
            /** @var SearchResult $search */
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


    public function getLeftSale(): ?int
    {
        return null;
//        $actualRoomsCount = $this->getRemain();
//        $leftRoomKey = $this->getLeftRoomKey();
//        if (!$actualRoomsCount || !$leftRoomKey) {
//            return 0;
//        }
//
//        $maxOutput = min($this->maxThreshold, $actualRoomsCount);
//        if ($maxOutput < $this->maxThreshold) {
//            return $maxOutput;
//        }
//
//        $leftRoom = $this->dm->getRepository('MBHOnlineBookingBundle:LeftRoom')->findOneBy(
//            [
//                'key' => $leftRoomKey,
//            ]
//        );
//
//        $now = new \DateTime("now");
//        $start = rand($this->minThreshold, $this->maxThreshold);
//        if (!$leftRoom) {
//            $leftRoom = new LeftRoom();
//            $leftRoom
//                ->setKey($leftRoomKey)
//                ->setDate($now)
//                ->setCount($start);
//        }
//
//        $interval = date_diff($now, $leftRoom->getDate(), true);
//
//        if (1 <= $interval->d){
//            if ($this->minThreshold <= $leftRoom->getCount()) {
//                $leftRoom->setCount($leftRoom->getCount() - 1);
//            } else {
//                $leftRoom->setCount($start);
//            }
//        }
//        $this->dm->persist($leftRoom);
//        $this->dm->flush($leftRoom);
//
//        return $leftRoom->getCount();
    }

    /**
     * @return string
     */
    public function getQueryId(): ?string
    {
        return $this->queryId;
    }

    /**
     * @param string $queryId
     * @return OnlineResultInstance
     */
    public function setQueryId(string $queryId): OnlineResultInstance
    {
        $this->queryId = $queryId;

        return $this;
    }



}