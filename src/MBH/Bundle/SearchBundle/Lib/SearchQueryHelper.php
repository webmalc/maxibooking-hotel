<?php


namespace MBH\Bundle\SearchBundle\Lib;


use Symfony\Component\Validator\Constraints as Assert;

class SearchQueryHelper
{
    /** @var \DateTime
     * @Assert\Date()
     * @Assert\NotNull()
     */
    private $begin;

    /** @var \DateTime
     * @Assert\Date()
     * @Assert\NotNull()
     */
    private $end;

    /**
     * @var string
     * @Assert\Type(type="string")
     * @Assert\NotNull()
     */
    private $tariffId;

    /**
     * @var string
     * @Assert\Type(type="string")
     * @Assert\NotNull()
     */
    private $roomTypeId;

    /**
     * @return \DateTime
     */
    public function getBegin(): \DateTime
    {
        return $this->begin;
    }

    /**
     * @param \DateTime $begin
     * @return SearchQueryHelper
     */
    public function setBegin(\DateTime $begin): SearchQueryHelper
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

    /**
     * @param \DateTime $end
     * @return SearchQueryHelper
     */
    public function setEnd(\DateTime $end): SearchQueryHelper
    {
        $this->end = $end;

        return $this;
    }

    /**
     * @return string
     */
    public function getTariffId(): string
    {
        return $this->tariffId;
    }

    /**
     * @param string $tariffId
     * @return SearchQueryHelper
     */
    public function setTariffId(string $tariffId): SearchQueryHelper
    {
        $this->tariffId = $tariffId;

        return $this;
    }

    /**
     * @return string
     */
    public function getRoomTypeId(): string
    {
        return $this->roomTypeId;
    }

    /**
     * @param string $roomTypeId
     * @return SearchQueryHelper
     */
    public function setRoomTypeId(string $roomTypeId): SearchQueryHelper
    {
        $this->roomTypeId = $roomTypeId;

        return $this;
    }

    public static function createInstance(array $dates, array $tariffRoomType)
    {
        $helper = new static();
        $helper
            ->setBegin($dates['begin'])
            ->setEnd($dates['end'])
            ->setTariffId($tariffRoomType['tariff'])
            ->setRoomTypeId($tariffRoomType['roomType']);

        return $helper;
    }


}