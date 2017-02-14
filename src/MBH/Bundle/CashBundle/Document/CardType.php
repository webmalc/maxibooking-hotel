<?php

namespace MBH\Bundle\CashBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\Document(collection="CardType")
 * Class CardType
 * @package MBH\Bundle\CashBundle\Document
 */
class CardType
{
    /**
     * @var string
     * @ODM\Id
     */
    protected $id;

    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $cardCode;

    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $cardCategory;

    /**
     * @return string
     */
    public function getCardCode(): string
    {
        return $this->cardCode;
    }

    /**
     * @param string $cardCode
     * @return CardType
     */
    public function setCardCode(string $cardCode): CardType
    {
        $this->cardCode = $cardCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getCardCategory(): string
    {
        return $this->cardCategory;
    }

    /**
     * @param string $cardCategory
     * @return CardType
     */
    public function setCardCategory(string $cardCategory): CardType
    {
        $this->cardCategory = $cardCategory;

        return $this;
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
     * @return CardType
     */
    public function setId(string $id): CardType
    {
        $this->id = $id;
        return $this;
    }
}