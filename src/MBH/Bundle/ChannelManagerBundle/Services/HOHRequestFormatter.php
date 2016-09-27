<?php

namespace MBH\Bundle\ChannelManagerBundle\Services;

use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;

class HOHRequestFormatter
{
    const API_KEY = 'ayKWtlRrCobH8ohFFrJO';

    private $serviceName;
    private $requestData = [];
    /**
     * @var ChannelManagerConfigInterface
     */
    private $config;

    const QUOTA = 'quota';
    const CLOSED = 'closed';
    const CLOSED_TO_ARRIVAL = 'closed_to_arrival';
    const CLOSED_TO_DEPARTURE = 'closed_to_departure';
    const MIN_STAY = 'min_stay';
    const PRICES = 'prices';

    /**
     * HOHRequestFormatter constructor.
     * @param ChannelManagerConfigInterface $config
     * @param string $serviceName
     */
    public function __construct($config, $serviceName = 'set_calendar')
    {
        $this->serviceName = $serviceName;
        $this->config = $config;
    }

    /**
     * get array key, where "day" field equal to specified value
     * @param $dateString
     * @return int|null
     */
    private function getDateInfoKey($dateString)
    {
        foreach ($this->requestData as $key => $dateInfo) {
            if($dateInfo['day'] == $dateString) {
                return (int)$key;
            }
        }
        return null;
    }

    public function addDateCondition(\DateTime $startDate, \DateTime $endDate)
    {
        $this->requestData =
            ["start" => $this->formatDate($startDate, true), "end" => $this->formatDate($endDate, true)];
    }

    /**
     * Add conditions like "quota" or "closed"
     * @param \DateTime $date
     * @param $conditionName
     * @param $roomTypeId
     * @param $value
     */
    public function addSingleParamCondition(\DateTime $date, $conditionName, $roomTypeId, $value)
    {
        $dateString = $this->formatDate($date);
        if ($dateInfoKey = $this->getDateInfoKey($dateString)) {
            $this->requestData[$dateInfoKey][$conditionName][$roomTypeId] = $value;
        } else {
            $data = ['day' => $dateString, $conditionName => [$roomTypeId => $value]];
            $this->requestData[] = $data;
        }
    }

    /**
     * Add conditions like "prices", "closed_to_arrival" etc, where it is necessarily specifying placement data
     * @param \DateTime $date
     * @param $conditionName
     * @param $roomTypeId
     * @param $placementId
     * @param $value
     */
    public function addDoubleParamCondition(\DateTime $date, $conditionName, $roomTypeId, $placementId, $value)
    {
        $dateString = $this->formatDate($date);
        $dateInfoKey = $this->getDateInfoKey($dateString);
        if (isset($dateInfoKey)) {
            $this->requestData[$dateInfoKey][$conditionName][$roomTypeId][$placementId] = $value;
        } else {
            $data = ['day' => $dateString, $conditionName => [$roomTypeId => [$placementId => $value]]];
            $this->requestData[] = $data;
        }
    }

    public function isDataEmpty()
    {
        return count($this->requestData) == 0;
    }

    /**
     * @param \DateTime $date
     * @param bool $isFullFormat
     * @return string
     */
    private function formatDate(\DateTime $date, $isFullFormat = false)
    {
        if ($isFullFormat) {
            return $date->format('Y-m-d H:i:s');
        }
        return $date->format('Y-m-d');
    }

    /**
     * format request
     * @return array
     */
    public function getRequest()
    {
        $template = [
            'api_key' => self::API_KEY,
            'hotel_id' => $this->config->getHotelId(),
            'service' => $this->serviceName
        ];

        if ($this->requestData) {
            $template['data'] = $this->requestData;
        }

        //отправляемые сообщения должны содержать один POST параметр 'request', содержащий данные в json-формате
        $requestData = ['request' => json_encode($template)];
        return $requestData;
    }
}