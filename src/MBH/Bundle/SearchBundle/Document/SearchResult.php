<?php


namespace MBH\Bundle\SearchBundle\Document;


use Doctrine\ODM\MongoDB\Mapping\Annotations\Document;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use MBH\Bundle\BaseBundle\Document\Base;
use MBH\Bundle\HotelBundle\Document\Room;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PackageBundle\Document\PackagePrice;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchException;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class SearchResult
 * @package MBH\Bundle\SearchBundle\Document
 * @Document(collection="SearchResults",repositoryClass="SearchResultRepository")
 */
class SearchResult extends Base implements \JsonSerializable
{

    use TimestampableDocument;

    /**
     * @var string
     * @Assert\Choice({"ok", "error"})
     * @ODM\Field(type="string")
     */
    private $status = 'ok';

    /**
     * @var string
     * @Assert\Type(type="string")
     * @ODM\Field(type="string")
     */
    private $error;

    /**
     * @var \DateTime
     * @Assert\Date()
     * @ODM\Field(type="date")
     */
    protected $begin;

    /**
     * @var \DateTime
     * @Assert\Date()
     * @ODM\Field(type="date")
     */
    protected $end;

    /**
     * @var int
     * @Assert\Type(type="integer")
     * @ODM\Field(type="int")
     */
    protected $adults;

    /**
     * @var int
     * @Assert\Type(type="integer")
     * @ODM\Field(type="int")
     */
    protected $children;

    /**
     * @var int
     * @Assert\Type(type="integer")
     * @ODM\Field(type="int")
     */
    protected $infants;

    /**
     * @var RoomType
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\HotelBundle\Document\RoomType")
     */
    protected $roomType;

    /**
     * @var Room
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\HotelBundle\Document\Room", cascade={"persist"})
     */
    protected $virtualRoom;

    /**
     * @var Tariff
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\PriceBundle\Document\Tariff")
     */
    protected $tariff;

    /**
     * mixed array of prices
     * @Assert\Collection()
     * @var []
     * @ODM\Field(type="hash")
     */
    protected $prices = [];

    /**
     * @var int
     * @Assert\Type(type="integer")
     * @Assert\Range(min=0)
     * @ODM\Field(type="integer")
     */
    protected $roomsCount = 0;

    /**
     * TODO: Тут прям комнаты ?
     * @var array
     */
    protected $rooms = [];

    /**
     * TODO: Переделать PackagePrices (добавить как миинмум детей и взрослых в поля, а не в массив)
     * @var PackagePrice[]
     */
    protected $packagePrices = [];

    /**
     * @var bool
     */
    protected $useCategories = false;

    /**
     * @var bool
     */
    protected $forceBooking = false;

    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $queryId;

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return SearchResult
     */
    public function setStatus(string $status): SearchResult
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return string
     * @Assert\Type(type="string")
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @param string $error
     * @return SearchResult
     */
    public function setError(string $error): SearchResult
    {
        $this->error = $error;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getBegin(): \DateTime
    {
        return $this->begin;
    }

    public function setBegin(\DateTime $begin)
    {
        $this->begin = $begin;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getEnd(): \DateTime
    {
        return $this->end;
    }


    public function setEnd(\DateTime $end)
    {
        $this->end = $end;

        return $this;
    }

    /**
     * @return RoomType
     */
    public function getRoomType()
    {
        return $this->roomType;
    }


    public function setRoomType(RoomType $roomType)
    {
        $this->roomType = $roomType;

        return $this;
    }


    public function getTariff()
    {
        return $this->tariff;
    }


    public function setTariff(Tariff $tariff)
    {
        $this->tariff = $tariff;

        return $this;
    }

    /**
     * @return int
     */
    public function getRoomsCount(): int
    {
        return $this->roomsCount;
    }


    public function setRoomsCount(int $roomsCount)
    {
        $this->roomsCount = $roomsCount;

        return $this;
    }


    public function addPrice($price, $adults = null, $children = null)
    {
        if ($adults !== null && $children !== null) {
            if ($this->getAdults() !== 0 || $this->getChildren() !== 0) {
                if(($adults !== $this->getAdults()) || ($children !== $this->getChildren())) {
                    return $this;
                }
            }
        }

        $key = $adults . '_' . $children;

        if($price === null) {

            if(isset($this->prices[$key])) {
                unset($this->prices[$key]);
            }
            return $this;
        }

        if (!isset($this->prices[$key])) {
            $this->prices[$key] = 0;
        }
        $this->prices[$key] += (float) $price;

        return $this;
    }

    /**
     * @return int
     */
    public function getAdults()
    {
        return $this->adults;
    }


    public function setAdults($adults)
    {
        $this->adults = $adults;

        return $this;
    }

    /**
     * @return int
     */
    public function getChildren()
    {
        return $this->children;
    }


    public function setChildren($children)
    {
        $this->children = $children;

        return $this;
    }

    /**
     * @return int
     */
    public function getInfants(): int
    {
        return $this->infants;
    }

    /**
     * @param int $infants
     * @return SearchResult
     */
    public function setInfants(int $infants): SearchResult
    {
        $this->infants = $infants;

        return $this;
    }

    public function getPrices()
    {
        return $this->prices;
    }


    public function setPrices(array $prices)
    {
        $this->prices = $prices;

        return $this;
    }


    public function getRooms()
    {
        return $this->rooms;
    }


    public function setRooms($rooms)
    {
        $this->rooms = $rooms;

        return $this;
    }


    public function isUseCategories(): bool
    {
        return $this->useCategories;
    }


    public function setUseCategories($useCategories)
    {
        $this->useCategories = $useCategories;

        return $this;
    }


    public function getPackagePrices($adults, $children)
    {
        $key = $adults . '_' . $children;
        if (isset($this->packagePrices[$key])) {
            return $this->packagePrices[$key];
        }

        return null;
    }

    public function getAllPackagesPrices(): array
    {
        return $this->packagePrices;
    }

    public function setPackagePrices(array $packagePrices, $adults, $children)
    {
        $this->packagePrices[$adults . '_' . $children] = $packagePrices;

        return $this;
    }


    public function getForceBooking(): bool
    {
        return $this->forceBooking;
    }


    public function setForceBooking(bool $forceBooking)
    {
        $this->forceBooking = $forceBooking;

        return $this;
    }


    public function getVirtualRoom()
    {
        return $this->virtualRoom;
    }


    public function setVirtualRoom(Room $virtualRoom): SearchResult
    {
        $this->virtualRoom = $virtualRoom;

        return $this;
    }


    public function getQueryId(): ?string
    {
        return $this->queryId;
    }


    public function setQueryId(string $queryId = null): SearchResult
    {
        if (null !== $queryId) {
            $this->queryId = $queryId;
        }

        return $this;
    }

    public static function createErrorResult(SearchException $exception): SearchResult
    {
        $result = new static();
        $result
            ->setStatus('error')
            ->setError($exception->getMessage())
        ;

        return $result;
    }

    public function jsonSerialize()
    {
        return [
            'id' => static::getId(),
            'prices' => $this->getPrices(),
            'rooms' => $this->getRooms(),
            'roomType' => $this->getRoomType()->getId(),
        ];
    }


}