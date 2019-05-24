<?php

namespace MBH\Bundle\UserBundle\Document;


use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use MBH\Bundle\BaseBundle\Document\Base;
use MBH\Bundle\BaseBundle\Document\Traits\BlameableDocument;
use MBH\Bundle\BaseBundle\Document\Traits\HotelableDocument;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ODM\Document(collection="WorkShift", repositoryClass="\MBH\Bundle\UserBundle\Document\WorkShiftRepository")
 * @Gedmo\Loggable
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class WorkShift extends Base
{
    use TimestampableDocument;
    use SoftDeleteableDocument;
    use BlameableDocument;
    use HotelableDocument;

    const STATUS_OPEN = 'open';
    const STATUS_LOCKED = 'locked';
    const STATUS_CLOSED = 'closed';

    /**
     * @var string
     * @ODM\Id(strategy="INCREMENT")
     */
    protected $id;
    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string")
     * @Assert\Choice(callback="getAvailableStatuses")
     */
    protected $status;
    /**
     * @var \DateTime
     * @Gedmo\Versioned
     * @ODM\Field(type="date")
     */
    protected $begin;
    /**
     * @var \DateTime
     * @Gedmo\Versioned
     * @ODM\Field(type="date")
     */
    protected $end;
    /**
     * Количество гостей на начало
     * @var integer
     * @Gedmo\Versioned
     * @ODM\Field(type="integer")
     */
    protected $beginGuestTotal;
    /**
     * Количество гостей на конец
     * @var integer
     * @Gedmo\Versioned
     * @ODM\Field(type="integer")
     */
    protected $endGuestTotal;
    /**
     * Количество оформленных туристов на начало
     * @var integer
     * @Gedmo\Versioned
     * @ODM\Field(type="integer")
     */
    protected $beginTouristTotal;
    /**
     * Количетво заехавших туриство за смену
     * @var integer
     * @Gedmo\Versioned
     * @ODM\Field(type="integer")
     */
    protected $arrivalTouristTotal;
    /**
     * Количетво не заехавших туриство за смену
     * @var integer
     * @Gedmo\Versioned
     * @ODM\Field(type="integer")
     */
    protected $noArrivalTouristTotal;
    /**
     * Продлились
     * @var integer
     * @Gedmo\Versioned
     * @ODM\Field(type="integer")
     */
    protected $continuePackageTotal;
    /**
     * Выехавших туристов
     * @var integer
     * @Gedmo\Versioned
     * @ODM\Field(type="integer")
     */
    protected $departureTouristTotal;
    /**
     * Не выехавших туристов
     * @var integer
     * @Gedmo\Versioned
     * @ODM\Field(type="integer")
     */
    protected $noDepartureTouristTotal;

    /**
     * Созданные этим пользователем за его смену
     * @var integer
     * @Gedmo\Versioned
     * @ODM\Field(type="integer")
     */
    protected $cashIncomeTotal;

    /**
     * @var integer
     * @Gedmo\Versioned
     * @ODM\Field(type="integer")
     */
    protected $electronicCashIncomeTotal;

    /**
     * @var integer
     * @Gedmo\Versioned
     * @ODM\Field(type="integer")
     */
    protected $cashExpenseTotal;

    /**
     * @var User
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\UserBundle\Document\User")
     */
    protected $closedBy;
    /**
     * @var \DateTime
     * @ODM\Field(type="date")
     */
    protected $closedAt;

    /**
     * @return array
     */
    public static function getAvailableStatuses()
    {
        return [self::STATUS_OPEN, self::STATUS_LOCKED, self::STATUS_CLOSED];
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return WorkShift
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBegin()
    {
        return $this->begin;
    }

    /**
     * @param mixed $begin
     * @return WorkShift
     */
    public function setBegin($begin)
    {
        $this->begin = $begin;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * @param \DateTime $end
     * @return WorkShift
     */
    public function setEnd($end)
    {
        $this->end = $end;
        return $this;
    }

    /**
     * @return int
     */
    public function getPastHours()
    {
        return $this->begin->diff(new \DateTime())->h;
    }

    /**
     * @return int
     */
    public function getHours()
    {
        return $this->begin->diff($this->end)->h;
    }

    /**
     * Продолжительность
     * @return \DateInterval
     */
    public function getDuration()
    {
        $end = $this->end ? $this->end : new \DateTime();
        return $this->begin->diff($end);
    }

    /**
     * @return mixed
     */
    public function getBeginGuestTotal()
    {
        return $this->beginGuestTotal;
    }

    /**
     * @param mixed $beginGuestTotal
     * @return WorkShift
     */
    public function setBeginGuestTotal($beginGuestTotal)
    {
        $this->beginGuestTotal = $beginGuestTotal;

        return $this;
    }

    /**
     * @return int
     */
    public function getEndGuestTotal()
    {
        return $this->endGuestTotal;
    }

    /**
     * @param int $endGuestTotal
     * @return WorkShift
     */
    public function setEndGuestTotal($endGuestTotal)
    {
        $this->endGuestTotal = $endGuestTotal;

        return $this;
    }



    /**
     * @return int
     */
    public function getBeginTouristTotal()
    {
        return $this->beginTouristTotal;
    }

    /**
     * @param int $beginTouristTotal
     * @return WorkShift
     */
    public function setBeginTouristTotal($beginTouristTotal)
    {
        $this->beginTouristTotal = $beginTouristTotal;

        return $this;
    }

    /**
     * @return int
     */
    public function getArrivalTouristTotal()
    {
        return $this->arrivalTouristTotal;
    }

    /**
     * @param int $arrivalTouristTotal
     * @return WorkShift
     */
    public function setArrivalTouristTotal($arrivalTouristTotal)
    {
        $this->arrivalTouristTotal = $arrivalTouristTotal;

        return $this;
    }

    /**
     * @return int
     */
    public function getNoArrivalTouristTotal()
    {
        return $this->noArrivalTouristTotal;
    }

    /**
     * @param int $noArrivalTouristTotal
     * @return WorkShift
     */
    public function setNoArrivalTouristTotal($noArrivalTouristTotal)
    {
        $this->noArrivalTouristTotal = $noArrivalTouristTotal;

        return $this;
    }

    /**
     * @return int
     */
    public function getContinuePackageTotal()
    {
        return $this->continuePackageTotal;
    }

    /**
     * @param int $continuePackageTotal
     * @return WorkShift
     */
    public function setContinuePackageTotal($continuePackageTotal)
    {
        $this->continuePackageTotal = $continuePackageTotal;

        return $this;
    }


    /**
     * @return int
     */
    public function getDepartureTouristTotal()
    {
        return $this->departureTouristTotal;
    }

    /**
     * @param int $departureTouristTotal
     * @return WorkShift
     */
    public function setDepartureTouristTotal($departureTouristTotal)
    {
        $this->departureTouristTotal = $departureTouristTotal;

        return $this;
    }

    /**
     * @return int
     */
    public function getNoDepartureTouristTotal()
    {
        return $this->noDepartureTouristTotal;
    }

    /**
     * @param int $noDepartureTouristTotal
     * @return WorkShift
     */
    public function setNoDepartureTouristTotal($noDepartureTouristTotal)
    {
        $this->noDepartureTouristTotal = $noDepartureTouristTotal;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCashIncomeTotal()
    {
        return $this->cashIncomeTotal;
    }

    /**
     * @param mixed $cashIncomeTotal
     * @return WorkShift
     */
    public function setCashIncomeTotal($cashIncomeTotal)
    {
        $this->cashIncomeTotal = $cashIncomeTotal;

        return $this;
    }

    /**
     * @return integer
     */
    public function getElectronicCashIncomeTotal()
    {
        return $this->electronicCashIncomeTotal;
    }

    /**
     * @param integer $electronicCashIncomeTotal
     * @return WorkShift
     */
    public function setElectronicCashIncomeTotal($electronicCashIncomeTotal)
    {
        $this->electronicCashIncomeTotal = $electronicCashIncomeTotal;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCashExpenseTotal()
    {
        return $this->cashExpenseTotal;
    }

    /**
     * @param mixed $cashExpenseTotal
     * @return WorkShift
     */
    public function setCashExpenseTotal($cashExpenseTotal)
    {
        $this->cashExpenseTotal = $cashExpenseTotal;

        return $this;
    }

    /**
     * @return User
     */
    public function getClosedBy()
    {
        return $this->closedBy;
    }

    /**
     * @param User $closedBy
     * @return WorkShift
     */
    public function setClosedBy(User $closedBy)
    {
        $this->closedBy = $closedBy;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getClosedAt()
    {
        return $this->closedAt;
    }

    /**
     * @param \DateTime $closedAt
     * @return WorkShift
     */
    public function setClosedAt(\DateTime $closedAt = null)
    {
        $this->closedAt = $closedAt;

        return $this;
    }
}
