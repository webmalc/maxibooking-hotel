<?php
/**
 * Date: 15.03.19
 */

namespace MBH\Bundle\OnlineBundle\Document\SettingsOnlineForm;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use MBH\Bundle\BaseBundle\Document\Base;

/**
 * @Gedmo\Loggable
 * @ODM\EmbeddedDocument
 */
class FieldsName extends Base
{
    public const MAP_FIELDS = [
        'hotel'        => 'hotel',
        'begin'        => 'check_in_date',
        'end'          => 'check_out_date',
        'nights'       => 'nights_amount',
        'roomType'     => 'room_type',
        'adults'       => 'adults',
        'children'     => 'children',
        'childrenAges' => 'children_ages',
        'guest'        => 'guest',
        'btnFind'      => 'btn_find_room',
    ];

    /**
     * @var string|null
     * @Gedmo\Versioned
     * @ODM\Field(type="string")
     * @Gedmo\Translatable
     */
    private $hotel;

    /**
     * @var string|null
     * @Gedmo\Versioned
     * @ODM\Field(type="string")
     * @Gedmo\Translatable
     */
    private $begin;

    /**
     * @var string|null
     * @Gedmo\Versioned
     * @ODM\Field(type="string")
     * @Gedmo\Translatable
     */
    private $end;

    /**
     * @var string|null
     * @Gedmo\Versioned
     * @ODM\Field(type="string")
     * @Gedmo\Translatable
     */
    private $nights;

    /**
     * @var string|null
     * @Gedmo\Versioned
     * @ODM\Field(type="string")
     * @Gedmo\Translatable
     */
    private $roomType;

    /**
     * @var string|null
     * @Gedmo\Versioned
     * @ODM\Field(type="string")
     * @Gedmo\Translatable
     */
    private $adults;

    /**
     * @var string|null
     * @Gedmo\Versioned
     * @ODM\Field(type="string")
     * @Gedmo\Translatable
     */
    private $children;

    /**
     * @var string|null
     * @Gedmo\Versioned
     * @ODM\Field(type="string")
     * @Gedmo\Translatable
     */
    private $childrenAges;

    /**
     * @var string|null
     * @Gedmo\Versioned
     * @ODM\Field(type="string")
     * @Gedmo\Translatable
     */
    private $guest;

    /**
     * @var string|null
     * @Gedmo\Versioned
     * @ODM\Field(type="string")
     * @Gedmo\Translatable
     */
    private $btnFind;

    /**
     * @return string|null
     */
    public function getHotel(): ?string
    {
        return $this->hotel;
    }

    /**
     * @param string|null $hotel
     */
    public function setHotel(?string $hotel): self
    {
        $this->hotel = $hotel;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getBegin(): ?string
    {
        return $this->begin;
    }

    /**
     * @param string|null $begin
     */
    public function setBegin(?string $begin): self
    {
        $this->begin = $begin;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getEnd(): ?string
    {
        return $this->end;
    }

    /**
     * @param string|null $end
     */
    public function setEnd(?string $end): self
    {
        $this->end = $end;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getNights(): ?string
    {
        return $this->nights;
    }

    /**
     * @param string|null $nights
     */
    public function setNights(?string $nights): self
    {
        $this->nights = $nights;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getRoomType(): ?string
    {
        return $this->roomType;
    }

    /**
     * @param string|null $roomType
     */
    public function setRoomType(?string $roomType): self
    {
        $this->roomType = $roomType;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getAdults(): ?string
    {
        return $this->adults;
    }

    /**
     * @param string|null $adults
     */
    public function setAdults(?string $adults): self
    {
        $this->adults = $adults;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getChildren(): ?string
    {
        return $this->children;
    }

    /**
     * @param string|null $children
     */
    public function setChildren(?string $children): self
    {
        $this->children = $children;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getChildrenAges(): ?string
    {
        return $this->childrenAges;
    }

    /**
     * @param string|null $childrenAges
     */
    public function setChildrenAges(?string $childrenAges): self
    {
        $this->childrenAges = $childrenAges;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getGuest(): ?string
    {
        return $this->guest;
    }

    /**
     * @param string|null $guest
     */
    public function setGuest(?string $guest): self
    {
        $this->guest = $guest;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getBtnFind(): ?string
    {
        return $this->btnFind;
    }

    /**
     * @param string|null $btnFind
     */
    public function setBtnFind(?string $btnFind): self
    {
        $this->btnFind = $btnFind;

        return $this;
    }

    public function getNameNotEmptyProperties(): array
    {
        $results = [];

        foreach (array_keys(self::MAP_FIELDS) as $property) {
            if ($this->$property !== null) {
                $results[] = $property;
            }
        }

        return $results;
    }

}