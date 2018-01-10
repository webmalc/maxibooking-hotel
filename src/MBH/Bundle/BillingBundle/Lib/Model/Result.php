<?php

namespace MBH\Bundle\BillingBundle\Lib\Model;

use MBH\Bundle\BaseBundle\Lib\Exception;

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

    public function addError($error)
    {
        $this->isSuccessful = false;
        if (!is_array($this->errors)) {
            $this->errors = [];
        }
        $this->errors[] = $error;

        return $this;
    }

    /**
     * @param array $errors
     * @return Result
     */
    public static function createErrorResult($errors = [])
    {
        $result = (new self())
            ->setIsSuccessful(false)
            ->setErrors($errors);

        return $result;
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

    /**
     * @param bool $dataAsSeparatedFields
     * @return array
     * @throws Exception
     */
    public function getApiResponse($dataAsSeparatedFields = false)
    {
        $response = [
            'status' => $this->isSuccessful(),
            'errors' => $this->getErrors()
        ];

        $responseData = $this->getData();
        if ($this->isSuccessful() && $responseData) {
            if ($dataAsSeparatedFields) {
                if (!is_array($responseData)) {
                    throw new Exception('Data in response with separated data fields should be type of array ');
                }
                array_merge($response, $responseData);
            } else {
                $response['data'] = $responseData;
            }
        }

        return $response;
    }
}