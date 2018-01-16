<?php

namespace MBH\Bundle\ChannelManagerBundle\Model\Expedia;


class NotificationRequestData
{
    private $requestId;
    private $requestorId;
    private $responderId;
    private $sourceId;
    private $destinationId;
    private $version;
    private $mbhHotelId;

    /**
     * @return mixed
     */
    public function getRequestId()
    {
        return $this->requestId;
    }

    /**
     * @param mixed $requestId
     * @return NotificationRequestData
     */
    public function setRequestId($requestId)
    {
        $this->requestId = $requestId;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRequestorId()
    {
        return $this->requestorId;
    }

    /**
     * @param mixed $requestorId
     * @return NotificationRequestData
     */
    public function setRequestorId($requestorId)
    {
        $this->requestorId = $requestorId;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getResponderId()
    {
        return $this->responderId;
    }

    /**
     * @param mixed $responderId
     * @return NotificationRequestData
     */
    public function setResponderId($responderId)
    {
        $this->responderId = $responderId;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSourceId()
    {
        return $this->sourceId;
    }

    /**
     * @param mixed $sourceId
     * @return NotificationRequestData
     */
    public function setSourceId($sourceId)
    {
        $this->sourceId = $sourceId;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDestinationId()
    {
        return $this->destinationId;
    }

    /**
     * @param mixed $destinationId
     * @return NotificationRequestData
     */
    public function setDestinationId($destinationId)
    {
        $this->destinationId = $destinationId;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param mixed $version
     * @return NotificationRequestData
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMbhHotelId()
    {
        return $this->mbhHotelId;
    }

    /**
     * @param mixed $mbhHotelId
     * @return NotificationRequestData
     */
    public function setMbhHotelId($mbhHotelId)
    {
        $this->mbhHotelId = $mbhHotelId;

        return $this;
    }
}