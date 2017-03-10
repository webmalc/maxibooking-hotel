<?php

namespace MBH\Bundle\HotelBundle\Document;

use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique as MongoDBUnique;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use MBH\Bundle\HotelBundle\DataFixtures\MongoDB\RoomViewTypeData;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ODM\Document(collection="RoomViewTypes")
 * @MongoDBUnique(fields={"openTravelCode", "title"})
 * Class RoomViewType
 * @package MBH\Bundle\HotelBundle\Document
 */
class RoomViewType
{
    /**
     * @var string
     * @ODM\Id
     */
    protected $id;

    /**
     * @var int
     * @ODM\Field(type="int")
     * @Assert\Choice(callback="getOpenTravelCodes")
     */
    protected $openTravelCode;

    /**
     * @var string
     * @ODM\Field(type="string")
     * @Assert\Choice(callback="getRoomViewTitles")
     */
    protected $codeName;

    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $mbhTranslationId;

    public function __construct($transId, $code = null, $codeName = null)
    {
        $this->mbhTranslationId = $transId;
        $this->openTravelCode = $code;
        $this->codeName = $codeName;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return RoomViewType
     */
    public function setId(string $id): RoomViewType
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getOpenTravelCode(): ?int
    {
        return $this->openTravelCode;
    }

    /**
     * @param int $openTravelCode
     * @return RoomViewType
     */
    public function setOpenTravelCode(int $openTravelCode): RoomViewType
    {
        $this->openTravelCode = $openTravelCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getCodeName(): ?string
    {
        return $this->codeName;
    }

    /**
     * @param string $codeName
     * @return RoomViewType
     */
    public function setCodeName(string $codeName): RoomViewType
    {
        $this->codeName = $codeName;

        return $this;
    }

    public function __toString()
    {
        return $this->mbhTranslationId;
    }

    public static function getOpenTravelCodes()
    {
        return RoomViewTypeData::getOpentravelCodes();
    }

    public static function getRoomViewTitles()
    {
        return RoomViewTypeData::getOpenTravelCodeNames();
    }

    /**
     * @return string
     */
    public function getMbhTranslationId(): string
    {
        return $this->mbhTranslationId;
    }

    /**
     * @param string $mbhTranslationId
     * @return RoomViewType
     */
    public function setMbhTranslationId(string $mbhTranslationId): RoomViewType
    {
        $this->mbhTranslationId = $mbhTranslationId;

        return $this;
    }
}