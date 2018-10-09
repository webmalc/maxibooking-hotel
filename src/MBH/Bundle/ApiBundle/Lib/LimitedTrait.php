<?php

namespace MBH\Bundle\ApiBundle\Lib;

use MBH\Bundle\BaseBundle\Lib\QueryBuilder;
use Symfony\Component\Validator\Constraints as Assert;
use MBH\Bundle\ApiBundle\Service\ApiRequestManager;

trait LimitedTrait
{
    /**
     * @var int
     * @Assert\GreaterThanOrEqual(value="1")
     * @Assert\LessThanOrEqual(value="50")
     */
    private $limit = ApiRequestManager::DEFAULT_LIMIT;
    /**
     * @var int
     * @Assert\GreaterThanOrEqual(value="0")
     * @Assert\LessThanOrEqual(value="50")
     */
    private $skip = ApiRequestManager::DEFAULT_SKIP;

    /**
     * @return int
     */
    public function getLimit(): ?int
    {
        return $this->limit;
    }

    /**
     * @param int $limit
     * @return static
     */
    public function setLimit(int $limit): self
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * @return int
     */
    public function getSkip(): ?int
    {
        return $this->skip;
    }

    /**
     * @param int $skip
     * @return static
     */
    public function setSkip(int $skip): self
    {
        $this->skip = $skip;

        return $this;
    }

    /**
     * @param QueryBuilder $builder
     * @return self
     */
    public function addLimitedCondition(QueryBuilder $builder)
    {
        $builder->limit($this->getLimit());
        $builder->skip($this->getSkip());

        return $this;
    }
}