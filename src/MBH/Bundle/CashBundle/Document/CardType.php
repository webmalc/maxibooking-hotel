<?php

namespace MBH\Bundle\CashBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique as MongoDBUnique;

/**
 * @ODM\Document(collection="CardType")
 * @MongoDBUnique(fields={"cardCode", "cardCategory"}, message="validator.document.card_type.unique_constraint")
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
     * @ODM\Index()
     * @Assert\Choice(callback="getCardCodes")
     */
    protected $cardCode;

    /**
     * @var string
     * @ODM\Field(type="string")
     * @Assert\Choice(callback="getCardCategories")
     *
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

    public function __toString()
    {
        return $this->cardCode . " ({$this->cardCategory})";
    }

    public static function getCardCategories()
    {
        return [
            'CREDIT',
            'DEBIT'
        ];
    }

    public static function getCardCodes()
    {
        return [
            'VISA',
            'AMEX',
            'DINERS',
            'JCB',
            'JAL',
            'DELTA',
            'VISA_ELECTRON',
            'LASER',
            'CARTA_SI',
            'MASTERCARD',
            'DISCOVER',
            'CARTE_BLANCHE',
            'ENROUTE',
            'MAESTRO_UK',
            'SOLO',
            'DANKORT',
            'CARTE_BLEU',
            'MAESTRO_INTERNATIONAL',
        ];
    }
}