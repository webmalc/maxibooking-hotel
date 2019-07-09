<?php


namespace MBH\Bundle\SearchBundle\Document;


use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use MBH\Bundle\BaseBundle\Document\Base;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use MBH\Bundle\BaseBundle\Document\Traits\BlameableDocument;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * Class SearchConfig
 * @package MBH\Bundle\SearchBundle\Document
 * @ODM\Document(collection="SearchConfig", repositoryClass="SearchConfigRepository")
 */
class SearchConfig extends Base
{

    use TimestampableDocument;

    use SoftDeleteableDocument;

    use BlameableDocument;

    /**
     * @var int
     * @ODM\Field(type="int")
     * @Assert\NotNull()
     * @Assert\Range(
     *     min=0,
     *     max=14
     * )
     *
     */
    private $positivePackageLengthDelta = 3;

    /**
     * @var int
     * @ODM\Field(type="int")
     * @Assert\NotNull()
     * @Assert\Range(
     *     min=0,
     *     max=21,
     *     minMessage = "You must be at least {{ limit }}days more to enter",
     *     maxMessage = "You cannot be more than {{ limit }}days to enter"
     * )
     *
     */
    private $negativePackageLengthDelta = 21;

    /**
     * @var int|null
     * @ODM\Field(type="int")
     * @Assert\NotNull()
     * @Assert\LessThanOrEqual(28)
     *
     */
    private $maxAdditionalPackageLength = 28;

    /**
     * @var int
     * @ODM\Field(type="int")
     * @Assert\NotNull()
     * @Assert\GreaterThanOrEqual(1)
     */
    private $minAdditionalPackageLength = 7;


    /**
     * @var int
     * @ODM\Field(type="int")
     * @Assert\NotNull()
     * @Assert\GreaterThanOrEqual(1)
     */
    private $roomTypeResultsShowAmount = 1;

    /**
     * @var bool
     * @ODM\Field(type="boolean")
     * @Assert\NotNull()
     */
    private $mustShowNecessarilyDate = true;

    /**
     * @var int
     * @ODM\Field(type="int")
     * @Assert\NotNull()
     * @Assert\Range(
     *     min=0,
     *     max=14
     * )
     */
    private $positiveMaxAdditionalSearchDaysAmount = 14;

    /**
     * @var int
     * @ODM\Field(type="int")
     * @Assert\NotNull()
     * @Assert\Range(
     *     min=0,
     *     max=14
     * )
     */
    private $negativeMaxAdditionalSearchDaysAmount = 14;

    /**
     *
     * @var bool
     * @ODM\Field(type="boolean")
     *
     */
    private $isUseCacheDefault = false;

    /**
     * @var int
     */
    private $asyncSearchConsumersAmount = 5;

    /**
     * @var int
     */
    private $cacheInvalidateConsumersAmount = 5;

    /**
     * @var int
     */
    private $warmUpCacheConsumersAmount = 5;

    /**
     * @return int
     */
    public function getPositivePackageLengthDelta(): int
    {
        return $this->positivePackageLengthDelta;
    }

    /**
     * @param int $positivePackageLengthDelta
     * @return SearchConfig
     */
    public function setPositivePackageLengthDelta(int $positivePackageLengthDelta): SearchConfig
    {
        $this->positivePackageLengthDelta = $positivePackageLengthDelta;

        return $this;
    }

    /**
     * @return int
     */
    public function getNegativePackageLengthDelta(): int
    {
        return $this->negativePackageLengthDelta;
    }

    /**
     * @param int $negativePackageLengthDelta
     * @return SearchConfig
     */
    public function setNegativePackageLengthDelta(int $negativePackageLengthDelta): SearchConfig
    {
        $this->negativePackageLengthDelta = $negativePackageLengthDelta;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getMaxAdditionalPackageLength(): ?int
    {
        return $this->maxAdditionalPackageLength;
    }

    /**
     * @param int|null $maxAdditionalPackageLength
     * @return SearchConfig
     */
    public function setMaxAdditionalPackageLength(?int $maxAdditionalPackageLength): SearchConfig
    {
        $this->maxAdditionalPackageLength = $maxAdditionalPackageLength;

        return $this;
    }

    /**
     * @return int
     */
    public function getMinAdditionalPackageLength(): int
    {
        return $this->minAdditionalPackageLength;
    }

    /**
     * @param int $minAdditionalPackageLength
     * @return SearchConfig
     */
    public function setMinAdditionalPackageLength(int $minAdditionalPackageLength): SearchConfig
    {
        $this->minAdditionalPackageLength = $minAdditionalPackageLength;

        return $this;
    }

    /**
     * @return int
     */
    public function getRoomTypeResultsShowAmount(): int
    {
        return $this->roomTypeResultsShowAmount;
    }

    /**
     * @param int $roomTypeResultsShowAmount
     * @return SearchConfig
     */
    public function setRoomTypeResultsShowAmount(int $roomTypeResultsShowAmount): SearchConfig
    {
        $this->roomTypeResultsShowAmount = $roomTypeResultsShowAmount;

        return $this;
    }

    /**
     * @return bool
     */
    public function isMustShowNecessarilyDate(): bool
    {
        return $this->mustShowNecessarilyDate;
    }

    /**
     * @param bool $mustShowNecessarilyDate
     * @return SearchConfig
     */
    public function setMustShowNecessarilyDate(bool $mustShowNecessarilyDate): SearchConfig
    {
        $this->mustShowNecessarilyDate = $mustShowNecessarilyDate;

        return $this;
    }

    /**
     * @return int
     */
    public function getPositiveMaxAdditionalSearchDaysAmount(): int
    {
        return $this->positiveMaxAdditionalSearchDaysAmount;
    }

    /**
     * @param int $positiveMaxAdditionalSearchDaysAmount
     * @return SearchConfig
     */
    public function setPositiveMaxAdditionalSearchDaysAmount(int $positiveMaxAdditionalSearchDaysAmount): SearchConfig
    {
        $this->positiveMaxAdditionalSearchDaysAmount = $positiveMaxAdditionalSearchDaysAmount;

        return $this;
    }

    /**
     * @return int
     */
    public function getNegativeMaxAdditionalSearchDaysAmount(): int
    {
        return $this->negativeMaxAdditionalSearchDaysAmount;
    }

    /**
     * @param int $negativeMaxAdditionalSearchDaysAmount
     * @return SearchConfig
     */
    public function setNegativeMaxAdditionalSearchDaysAmount(int $negativeMaxAdditionalSearchDaysAmount): SearchConfig
    {
        $this->negativeMaxAdditionalSearchDaysAmount = $negativeMaxAdditionalSearchDaysAmount;

        return $this;
    }

    /**
     * @return bool
     */
    public function isUseCacheDefault(): bool
    {
        return $this->isUseCacheDefault;
    }

    /**
     * @param bool $isUseCacheDefault
     * @return SearchConfig
     */
    public function setIsUseCacheDefault(bool $isUseCacheDefault): SearchConfig
    {
        $this->isUseCacheDefault = $isUseCacheDefault;

        return $this;
    }




}