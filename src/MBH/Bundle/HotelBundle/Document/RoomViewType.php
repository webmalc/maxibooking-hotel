<?php

namespace MBH\Bundle\HotelBundle\Document;

use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique as MongoDBUnique;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

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
     * @Gedmo\Translatable
     */
    protected $title;

    /**
     * @Gedmo\Locale
     */
    protected $locale;

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
        return $this->title;
    }

    public static function getOpenTravelCodes()
    {
        return array_keys(self::getRoomViewTypes());
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return RoomViewType
     */
    public function setTitle(string $title): RoomViewType
    {
        $this->title = $title;

        return $this;

    }

    /**
     * @return mixed
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param mixed $locale
     * @return RoomViewType
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }


    public static function getRoomViewTitles()
    {
        return array_values(self::getRoomViewTypes());
    }

    public static  function getRoomViewTypes()
    {
        return [
            1 => 'Airport view',
            2 => 'Bay view',
            3 => 'City view',
            4 => 'Courtyard view',
            5 => 'Golf view',
            6 => 'Harbor view',
            7 => 'Intercoastal view',
            8 => 'Lake view',
            9 => 'Marina view',
            10 => 'Mountain view',
            11 => 'Ocean view',
            12 => 'Pool view',
            13 => 'River view',
            14 =>'Water view',
            15 => 'Beach view',
            16 => 'Garden view',
            17 => 'Park view',
            18 => 'Forest view',
            19 => 'Rain forest view',
            20 => 'Various views',
            21 => 'Limited view',
            22 => 'Slope view',
            23 => 'Strip view',
            24 => 'Countryside view',
            25 => 'Sea view'
        ];
    }
}