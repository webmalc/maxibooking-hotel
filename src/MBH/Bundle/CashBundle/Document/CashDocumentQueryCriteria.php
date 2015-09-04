<?php
/**
 * Created by PhpStorm.
 * User: mb
 * Date: 26.05.15
 * Time: 17:17
 */

namespace MBH\Bundle\CashBundle\Document;


use MBH\Bundle\BaseBundle\Document\AbstractQueryCriteria;

/**
 * Class CashDocumentQueryCriteria
 * @package MBH\Bundle\CashBundle\Document
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
class CashDocumentQueryCriteria extends AbstractQueryCriteria
{
    /**
     * @var bool
     */
    public $isPaid;

    /**
     * with deleted
     * @var bool
     */
    public $deleted;

    /**
     * @var bool
     */
    public $isConfirmed;

    /**
     * @var \DateTime|null
     */
    public $begin;

    /**
     * @var \DateTime|null
     */
    public $end;

    /**
     * Field Name
     * @var string
     */
    public $filterByRange;

    /**
     * @var string
     */
    public $orderIds;

    /**
     * @var string[]
     */
    public $methods = [];

    /**
     * @var int
     */
    public $skip;

    /**
     * @var int
     */
    public $limit;

    /**
     * @var array
     */
    public $sortDirection = [];

    /**
     * @var array
     */
    public $sortBy = [];

    /**
     * @var string
     */
    public $search;

    /**
     * @var string
     */
    public $createdBy;
}