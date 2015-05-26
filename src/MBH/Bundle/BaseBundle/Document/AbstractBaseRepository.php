<?php

namespace MBH\Bundle\BaseBundle\Document;

use Doctrine\ODM\MongoDB\DocumentRepository;
use Doctrine\MongoDB\Query\Builder;
use MBH\Bundle\BaseBundle\Lib\BaseRepositoryInterface;

abstract class AbstractBaseRepository extends DocumentRepository implements BaseRepositoryInterface
{
    /**
     * @param Builder $qb
     * @param string $field
     * @param string $value
     * @return $this
     */
    public function equals(Builder $qb, $field, $value)
    {
        $qb->field($field)->equals($value);

        return $this;
    }

    /**
     * @param Builder $qb
     * @param string $field
     * @param string $value
     * @return $this
     */
    public function equalsNotEmpty(Builder $qb, $field, $value)
    {
        if (!empty($value)) {
            $this->equals($qb, $field, $value);
        }

        return $this;
    }

    /**
     * @param Builder $qb
     * @param string $field
     * @param string $value
     * @return $this
     */
    public function in(Builder $qb, $field, $value)
    {
        is_array($value) ? $value : $value = [$value];
        $qb->field($field)->in($value);

        return $this;
    }

    /**
     * @param Builder $qb
     * @param string $field
     * @param string $value
     * @return $this
     */
    public function inNotEmpty(Builder $qb, $field, $value)
    {
        if (!empty($value)) {
            $this->in($qb, $field, $value);
        }

        return $this;
    }

    /**
     * @param Builder $qb
     * @param string $field
     * @param string $value
     * @return $this
     */
    public function notIn(Builder $qb, $field, $value)
    {
        is_array($value) ? $value : $value = [$value];
        $qb->field($field)->notIn($value);

        return $this;
    }

    /**
     * @param Builder $qb
     * @param string $field
     * @param string $value
     * @return $this
     */
    public function NotInNotEmpty(Builder $qb, $field, $value)
    {
        if (!empty($value)) {
            $this->notIn($qb, $field, $value);
        }

        return $this;
    }
}
