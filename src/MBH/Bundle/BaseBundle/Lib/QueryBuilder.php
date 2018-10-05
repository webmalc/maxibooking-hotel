<?php

namespace MBH\Bundle\BaseBundle\Lib;

use Doctrine\ODM\MongoDB\Query\Builder;

class QueryBuilder extends Builder
{
    /**
     * @param string $field
     * @param $value
     * @return $this
     */
    public function equalsIfNotEmpty($field, $value)
    {
        if (!empty($value)) {
            $this->field($field)->equals($value);
        }

        return $this;
    }

    /**
     * @param string $field
     * @param $value
     * @return $this
     */
    public function notEqualsIfNotEmpty($field, $value)
    {
        if (!empty($value)) {
            $this->field($field)->notEqual($value);
        }

        return $this;
    }

    /**
     * @param string $field
     * @param $value
     * @return $this
     */
    public function inToArray($field, $value)
    {
        is_array($value) ? $value : $value = [$value];
        $this->field($field)->in($value);

        return $this;
    }

    /**
     * @param string $field
     * @param $value
     * @return $this
     */
    public function notInToArray($field, $value)
    {
        is_array($value) ? $value : $value = [$value];
        $this->field($field)->notIn($value);

        return $this;
    }

    /**
     * @param string $field
     * @param $value
     * @return $this
     */
    public function inIfNotEmpty($field, $value)
    {
        if (!empty($value)) {
            $this->inToArray($field, $value);
        }

        return $this;
    }

    /**
     * @param string $field
     * @param $value
     * @return $this
     */
    public function notInIfNotEmpty($field, $value)
    {
        if (!empty($value)) {
            $this->notInToArray($field, $value);
        }

        return $this;
    }
}
