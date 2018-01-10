<?php

namespace MBH\Bundle\BillingBundle\Lib\Model;


class Result
{
    private $isSuccessful = true;
    private $data;
    private $errors = [];

    /**
     * @return bool
     */
    public function isSuccessful(): ?bool
    {
        return $this->isSuccessful;
    }

    /**
     * @param bool $isSuccessful
     * @return Result
     */
    public function setIsSuccessful(bool $isSuccessful): Result
    {
        $this->isSuccessful = $isSuccessful;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     * @return Result
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @param mixed $errors
     * @return Result
     */
    public function setErrors($errors)
    {
        $this->errors = $errors;

        return $this;
    }

    /**
     * @return Result
     */
    public static function createErrorResult()
    {
        return (new self())->setIsSuccessful(false);
    }

    /**
     * @param null $data
     * @return Result
     */
    public static function createSuccessResult($data = null)
    {
        $result =  new self();
        if (!is_null($data)) {
            $result->setData($data);
        }

        return $result;
    }
}