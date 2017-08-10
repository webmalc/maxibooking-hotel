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

    /**
     * @param mixed $methodName
     * @return RequestInfo
     */
    public function setMethodName($methodName)
    {
        $this->methodName = $methodName;
        return $this;
    }

    /**
     * @param $parameterName
     * @param $data
     * @return RequestInfo
     */
    public function addRequestParameter($parameterName, $data)
    {
        $this->requestData[$parameterName] = $data;
        return $this;
    }

    /**
     * @param $headerName
     * @param $data
     * @return RequestInfo
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

    public function setRequestData($data)
    {
        $this->requestData = $data;
        return $this;
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
     * @return RequestInfo
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

}