<?php

namespace MBH\Bundle\ApiBundle\Lib;

use MBH\Bundle\BaseBundle\Lib\QueryBuilder;

class RequestParams
{
    /** @var array */
    private $ids;
    /** @var bool */
    private $isEnabled;
    /** @var string */
    private $locale;

    /**
     * @return array
     */
    public function getIds(): ?array
    {
        return $this->ids;
    }

    /**
     * @param array $ids
     * @return RequestParams
     */
    public function setIds(array $ids): RequestParams
    {
        $this->ids = $ids;

        return $this;
    }

    /**
     * @return bool
     */
    public function isEnabled(): ?bool
    {
        return $this->isEnabled;
    }

    /**
     * @param bool $isEnabled
     * @return RequestParams
     */
    public function setIsEnabled(bool $isEnabled): RequestParams
    {
        $this->isEnabled = $isEnabled;

        return $this;
    }

    /**
     * @return string
     */
    public function getLocale(): ?string
    {
        return $this->locale;
    }

    /**
     * @param string $locale
     * @return RequestParams
     */
    public function setLocale(string $locale): RequestParams
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * @param QueryBuilder $builder
     * @return mixed
     */
    public function fillQueryBuilder(QueryBuilder $builder)
    {
        $builder->inIfNotEmpty('id', $this->getIds());
        if (!is_null($this->isEnabled())) {
            $builder->field('isEnabled')->equals($this->isEnabled());
        }

        return $builder;
    }
}