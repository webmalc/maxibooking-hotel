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

 */
class CashDocumentQueryCriteria extends AbstractQueryCriteria
{
    const TYPE_BY_ORDER = 'order';
    const TYPE_BY_OTHER = 'other';

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
     * @var array
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

    /**
     * @var CashDocumentArticle
     */
    public $article;

    /**
     * @var string|null
     */
    public $type;

    /**
     * @return array
     */
    public static function getTypeList()
    {
        return [self::TYPE_BY_ORDER, self::TYPE_BY_OTHER];
    }
}