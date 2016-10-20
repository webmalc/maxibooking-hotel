<?php

namespace MBH\Bundle\ChannelManagerBundle\Model;

class RequestInfo
{
    const PUT_METHOD_NAME = 'PUT';
    const POST_METHOD_NAME = 'POST';
    const GET_METHOD_NAME = 'GET';

    private $methodName = self::GET_METHOD_NAME;
    private $requestData = [];
    private $headersList = [];
    private $url;
    private $rowData;

    /**
     * @return mixed
     */
    public function getRowData()
    {
        return $this->rowData;
    }

    /**
     * @param mixed $rowData
     */
    public function setRowData($rowData)
    {
        $this->rowData = $rowData;
    }

    /**
     * @param mixed $methodName
     * @return $this
     */
    public function setMethodName($methodName)
    {
        $this->methodName = $methodName;
        return $this;
    }

    /**
     * @param $parameterName
     * @param $data
     * @return $this
     */
    public function addRequestParameter($parameterName, $data)
    {
        $this->requestData[$parameterName] = $data;
        return $this;
    }

    /**
     * @param $headerName
     * @param $data
     * @return $this
     */
    public function addHeader($headerName, $data)
    {
        $this->headersList[] = "$headerName: $data";
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMethodName()
    {
        return $this->methodName;
    }

    /**
     * @return mixed
     */
    public function getRequestData()
    {
        return $this->requestData;
    }

    /**
     * @return mixed
     */
    public function getHeadersList()
    {
        return count($this->headersList) > 0 ? $this->headersList : null;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param mixed $url
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

}