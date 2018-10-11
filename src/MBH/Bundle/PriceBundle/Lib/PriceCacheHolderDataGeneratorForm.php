<?php
/**
 * Created by PhpStorm.
 * Date: 09.10.18
 */

namespace MBH\Bundle\PriceBundle\Lib;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Document\Base;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\HotelBundle\Document\RoomTypeCategory;
use MBH\Bundle\PriceBundle\Document\Tariff;

class PriceCacheHolderDataGeneratorForm extends PriceCacheKit
{
    /**
     * @var \DateTime|null
     */
    private $begin;

    /**
     * @var \DateTime|null
     */
    private $end;

    /**
     * @var Hotel|null
     */
    private $hotel;

    /**
     * @var string|null
     */
    private $singlePriceFake;

    /**
     * @var string|null
     */
    private $childPriceFake;

    /**
     * @var ArrayCollection
     */
    private $tariffs;

    /**
     * @var ArrayCollection
     */
    private $roomTypes;

    /**
     * @var array
     */
    private $weekdays = [];

    /**
     * @var string[]
     */
    private $holderTariffIds;

    /**
     * @var string[]
     */
    private $holderRoomTypeIds;

    /**
     * @var bool
     */
    private $saveForm = false;

    /**
     * @return bool
     */
    public function isSaveForm(): bool
    {
        return $this->saveForm;
    }

    /**
     * @param bool $saveForm
     */
    public function setSaveForm(bool $saveForm): void
    {
        $this->saveForm = $saveForm;
    }

    /**
     * @return \DateTime|null
     */
    public function getBegin(): ?\DateTime
    {
        return $this->begin;
    }

    /**
     * @param \DateTime|null $begin
     */
    public function setBegin(?\DateTime $begin): void
    {
        $this->begin = $begin;
    }

    /**
     * @return \DateTime|null
     */
    public function getEnd(): ?\DateTime
    {
        return $this->end;
    }

    /**
     * @param \DateTime|null $end
     */
    public function setEnd(?\DateTime $end): void
    {
        $this->end = $end;
    }

    /**
     * @return Hotel|null
     */
    public function getHotel(): ?Hotel
    {
        return $this->hotel;
    }

    /**
     * @param Hotel|null $hotel
     */
    public function setHotel(?Hotel $hotel): void
    {
        $this->hotel = $hotel;
    }

    /**
     * @return string|null
     */
    public function getSinglePriceFake(): ?string
    {
        return $this->singlePriceFake;
    }

    /**
     * @param string|null $singlePriceFake
     */
    public function setSinglePriceFake(?string $singlePriceFake): void
    {
        $this->singlePriceFake = $singlePriceFake;
    }

    /**
     * @return null|string
     */
    public function getChildPriceFake(): ?string
    {
        return $this->childPriceFake;
    }

    /**
     * @param null|string $childPriceFake
     */
    public function setChildPriceFake(?string $childPriceFake): void
    {
        $this->childPriceFake = $childPriceFake;
    }

    public function getTariffs(): ?ArrayCollection
    {
        return $this->tariffs;
    }

    /**
     * @return Tariff[]
     */
    public function getTariffsAsArray(): array
    {
        return $this->tariffs !== null ? $this->tariffs->toArray() : [];
    }

    /**
     * @param Tariff[] $tariffs
     */
    public function setTariffs(ArrayCollection $tariffs): void
    {
        $this->tariffs = $tariffs;
    }

    public function getRoomTypes(): ?ArrayCollection
    {
        return $this->roomTypes;
    }

    /**
     * @return RoomType[]
     */
    public function getRoomTypesAsArray(): array
    {
        return $this->roomTypes !== null ? $this->roomTypes->toArray() : [];
    }

    /**
     * @param RoomType[] $roomTypes
     */
    public function setRoomTypes(ArrayCollection $roomTypes): void
    {
        $this->roomTypes = $roomTypes;
    }

    /**
     * @return array
     */
    public function getWeekdays(): array
    {
        return $this->weekdays;
    }

    /**
     * @param array $weekdays
     */
    public function setWeekdays(array $weekdays): void
    {
        $this->weekdays = $weekdays;
    }


    public function __sleep()
    {
        $this->holderRoomTypeIds = $this->getIds($this->getRoomTypesAsArray());
        $this->holderTariffIds = $this->getIds($this->getTariffsAsArray());

        return [
            'begin',
            'end',
            'holderRoomTypeIds',
            'holderTariffIds',
            'price',
            'isPersonPrice',
            'singlePrice',
            'singlePriceFake',
            'childPrice',
            'childPriceFake',
            'weekdays',
            'saveForm',
            'additionalPrices',
            'additionalChildrenPrices',
            'additionalPricesRawFields',
            'additionalChildrenPricesRawFields',
        ];
    }

    /**
     * @param DocumentManager $dm
     * @param bool $useCategories
     * @throws \Doctrine\ODM\MongoDB\LockException
     * @throws \Doctrine\ODM\MongoDB\Mapping\MappingException
     */
    public function afterUnserialize(DocumentManager $dm, bool $useCategories): void
    {
        $roomTypes = [];
        $tariffs = [];

        foreach ($this->holderRoomTypeIds as $id) {
            $roomTypes[$id] = $dm->getRepository($useCategories ? RoomTypeCategory::class : RoomType::class)->find($id);
        }
        foreach ($this->holderTariffIds as $id) {
            $tariffs[$id] = $dm->getRepository(Tariff::class)->find($id);
        }

        $this->holderRoomTypeIds = [];
        $this->holderTariffIds = [];
        $this->roomTypes = new ArrayCollection($roomTypes);
        $this->tariffs = new ArrayCollection($tariffs);
    }

    /**
     * @param array $array
     * @return array
     */
    private function getIds(array $array): array
    {
        $ids = [];

        /** @var Base $a */
        foreach ($array as $a) {
            $ids[] = $a->getId();
        }

        return $ids;
    }
}