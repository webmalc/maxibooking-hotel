<?php
/**
 * Created by PhpStorm.
 * Date: 15.10.18
 */

namespace MBH\Bundle\PriceBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * Class TariffCombinationHolder
 * @ODM\Document(collection="TariffCombinationHolder")
 */
class TariffCombinationHolder
{

    /**
     * @var string
     * ODM\Field(type="string")
     * @ODM\Id
     */
    private $id;

    /**
     * @var string
     * @ODM\Field(type="string")
     */
    private $parentId;

    /**
     * @var string
     * @ODM\Field(type="string")
     */
    private $combinationTariffId;

    /**
     * @var int
     * @ODM\Field(type="integer")
     */
    private $position;

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

//    /**
//     * @param string $id
//     */
//    public function setId(string $id): void
//    {
//        $this->id = $id;
//    }

    /**
     * @return string
     */
    public function getParentId(): ?string
    {
        return $this->parentId;
    }

    /**
     * @param string $parentId
     */
    public function setParentId(string $parentId): void
    {
        $this->parentId = $parentId;
    }

    /**
     * @return string
     */
    public function getCombinationTariffId(): ?string
    {
        return $this->combinationTariffId;
    }

    /**
     * @param string $combinationTariffId
     */
    public function setCombinationTariffId(string $combinationTariffId): void
    {
        $this->combinationTariffId = $combinationTariffId;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param int $position
     */
    public function setPosition($position): void
    {
        $this->position = $position;
    }

}