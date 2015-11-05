<?php

namespace MBH\Bundle\UserBundle\Document;


use MBH\Bundle\BaseBundle\Document\Base;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Blameable\Traits\BlameableDocument;

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

    const STATUS_OPEN = 'open';
    const STATUS_LOCKED = 'locked';
    const STATUS_CLOSED = 'closed';

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String()
     */
    protected $status;

    /**
     * @var \DateTime
     * @Gedmo\Versioned
     * @ODM\Date()
     */
    protected $begin;

    /**
     * @var \DateTime
     * @Gedmo\Versioned
     * @ODM\Date()
     */
    protected $end;

    /**
     * @var integer
     * @Gedmo\Versioned
     * @ODM\Integer()
     */
    protected $beginGuestTotal;

    /**
     * @var integer
     * @Gedmo\Versioned
     * @ODM\Integer()
     */
    protected $beginTouristTotal;

    /**
     * @var integer
     * @Gedmo\Versioned
     * @ODM\Integer()
     */
    protected $arrivalTouristTotal;
    /**
     * @var integer
     * @Gedmo\Versioned
     * @ODM\Integer()
     */
    protected $noArrivalTouristTotal;

    /**
     * @var integer
     * @Gedmo\Versioned
     * @ODM\Integer()
     */
    protected $continuePackageTotal;

    /**
     * @var integer
     * @Gedmo\Versioned
     * @ODM\Integer()
     */
    protected $departureTouristTotal;
    /**
     * @var integer
     * @Gedmo\Versioned
     * @ODM\Integer()
     */
    protected $noDepartureTouristTotal;

    /**
     * Созданные этим пользователем за его смену
     * @var integer
     * @Gedmo\Versioned
     * @ODM\Integer()
     */
    protected $cashIncomeTotal;

    /**
     * @var integer
     * @Gedmo\Versioned
     * @ODM\Integer()
     */
    protected $electronicCashIncomeTotal;

    /**
     * @var integer
     * @Gedmo\Versioned
     * @ODM\Integer()
     */
    protected $cashExpenseTotal;

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
     * @return mixed
     */
    public function getElectronicCashIncomeTotal()
    {
        return $this->electronicCashIncomeTotal;
    }

    /**
     * @param mixed $electronicCashIncomeTotal
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


}