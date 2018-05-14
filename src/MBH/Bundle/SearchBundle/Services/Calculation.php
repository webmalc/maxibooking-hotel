<?php


namespace MBH\Bundle\SearchBundle\Services;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\HotelBundle\Service\RoomTypeManager;
use MBH\Bundle\PriceBundle\Document\PriceCache;
use MBH\Bundle\PriceBundle\Document\Promotion;
use MBH\Bundle\PriceBundle\Document\Special;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\SearchBundle\Lib\Exceptions\CalculationException;

class Calculation
{

    /** @var RoomTypeManager */
    private $roomTypeManager;

    /** @var DocumentManager */
    private $dm;

    /** @var bool */
    private $isUseCategory;


    /**
     * Calculation constructor.
     * @param RoomTypeManager $roomTypeManager
     */
    public function __construct(RoomTypeManager $roomTypeManager, DocumentManager $dm)
    {
        $this->roomTypeManager = $roomTypeManager;
        $this->dm = $dm;
        $this->isUseCategory = $roomTypeManager->useCategories;
    }


    public function calcPrices(
        RoomType $roomType,
        Tariff $tariff,
        \DateTime $begin,
        \DateTime $end,
        $adults = 0,
        $children = 0,
        Promotion $promotion = null,
        Special $special = null,
        $isStrictDuration = true
    ): array
    {
        $roomTypeId = $this->getRoomTypeId($roomType);
        $tariffId = $this->getTariffId($tariff);

        $caches = $this->getPriceCaches($begin, $end, $roomType, $tariff);
        $duration = (int)$end->diff($begin)->format('%a') + 1;
        $this->checkCaches($caches, $duration, $isStrictDuration);

        $combinations = $this->getCombinations($roomType, $adults, $children);

        $a = 'b';




    }

    private function getRoomTypeId(RoomType $roomType): string
    {
        if ($this->isUseCategory) {
            if (null === $roomType->getCategory()) {
                throw new CalculationException('Categories in use, but RoomType hasn\'t category');
            }

            return $roomType->getCategory()->getId();
        }

        return $roomType->getId();
    }

    private function getTariffId(Tariff $tariff): string
    {
        if ($tariff->getParent() && $tariff->getChildOptions()->isInheritPrices()) {
            return $tariff->getParent()->getId();
        }

        return $tariff->getId();
    }

    private function getPriceCaches(\DateTime $begin, \DateTime $end, RoomType $roomType, Tariff $tariff): array
    {

        $caches = $this->dm
            ->getRepository(PriceCache::class)
            ->fetchRaw($begin, $end, $roomType->getId(), $tariff->getId(), $this->roomTypeManager->useCategories);

        return $caches;
    }

    private function checkCaches(array $priceCaches, int $duration, bool $isStrictDuration)
    {
        if ($isStrictDuration && $duration !== \count($priceCaches)) {
            throw new CalculationException('Duration not equal priceCaches count');
        }

    }

    private function getCombinations(RoomType $roomType, int $adults, int $children): array

    {
        if ($adults === 0 && $children === 0) {
            $combinations = $roomType->getAdultsChildrenCombinations($adults, $children);
        } else {
            $combinations = [0 => ['adults' => $adults, 'children' => $children]];

        }

        return $combinations;
    }
}