<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\HomeAway;

use MBH\Bundle\ChannelManagerBundle\Lib\AbstractRequestDataFormatter;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Document\PriceCache;

class HomeAwayDataFormatter extends AbstractRequestDataFormatter
{

    /**
     * Форматирование данных, отправляемых в запросе обновления цен сервиса
     * @param $begin
     * @param $end
     * @param $roomTypes
     * @param $serviceTariffs
     * @param ChannelManagerConfigInterface $config
     * @return mixed
     */
    public function formatPriceRequestData(
        $begin,
        $end,
        $roomTypes,
        $serviceTariffs,
        ChannelManagerConfigInterface $config
    ) {
        $requestDataArray = $this->getPriceData($begin, $end, $roomTypes, $serviceTariffs, $config);
        $ratePeriodXMLElements = [];
        $currency = $this->container->getParameter('locale.currency');
        foreach ($requestDataArray as $roomTypeId => $pricesByDates) {
            foreach ($pricesByDates as $dateString => $priceByTariff) {
                /** @var PriceCache $priceCache */
                $priceCache = current($priceByTariff);
                $ratePeriodElement = new \SimpleXMLElement('<ratePeriod/>');
                $dateRangeElement = $ratePeriodElement->addChild('dateRange');
                $dateRangeElement->addChild('beginDate', $dateString);
                $dateRangeElement->addChild('endDate', $dateString);

                $ratesElement = $dateRangeElement->addChild('rates');
                $rateElement = $ratesElement->addChild('rate');
                $rateElement->addAttribute('rateType', 'EXTRA_NIGHT');
                $amountElement = $rateElement->addChild('amount', $priceCache->getPrice());
                $amountElement->addAttribute('currency', $currency);
                $ratePeriodXMLElements[] = $ratePeriodElement;
            }
        }

        return $this->formatTemplateData($ratePeriodXMLElements);
    }

    /**
     * Форматирование данных, отправляемых в запросе обновления квот на комнаты
     * @param $begin
     * @param $end
     * @param $roomTypes
     * @param ChannelManagerConfigInterface $config
     * @return mixed
     */
    public function formatRoomRequestData($begin, $end, $roomTypes, ChannelManagerConfigInterface $config)
    {
        // TODO: Implement formatRoomRequestData() method.
    }

    /**
     * Форматирование данных, отправляемых в запросе обновления ограничений
     * @param $begin
     * @param $end
     * @param $roomTypes
     * @param $serviceTariffs
     * @param ChannelManagerConfigInterface $config
     * @return mixed
     */
    public function formatRestrictionRequestData(
        $begin,
        $end,
        $roomTypes,
        $serviceTariffs,
        ChannelManagerConfigInterface $config
    ) {
        // TODO: Implement formatRestrictionRequestData() method.
    }

    /**
     * Форматирование данных, отправляемых в запросе закрытия продаж
     * @param ChannelManagerConfigInterface $config
     * @return mixed
     */
    public function formatCloseForConfigData(ChannelManagerConfigInterface $config)
    {
        // TODO: Implement formatCloseForConfigData() method.
    }

    /**
     * Форматирование данных, отправляемых в запросе получения броней
     * @param ChannelManagerConfigInterface $config
     * @return mixed
     */
    public function formatGetBookingsData(ChannelManagerConfigInterface $config)
    {
        // TODO: Implement formatGetBookingsData() method.
    }

    private function formatAvailabilityData(\DateTime $begin, \DateTime $end, RoomType $roomType) {
        $availabilityElement = new \SimpleXMLElement('unitAvailabilityEntities');
        $availabilityElement->addChild('listingExternalId', $roomType->getId());
        $availabilityElement->addChild('unitExternalId', $roomType->getId());
        $unitAvailabilityElement = $availabilityElement->addChild('unitAvailability');

        $unitAvailabilityElement = $availabilityElement->addChild('dateRange');
        $unitAvailabilityElement->addChild('beginDate', $begin->format('Y-m-d'));
        $unitAvailabilityElement->addChild('endDate', $end->format('Y-m-d'));

        //TODO: Уточнить
        $unitAvailabilityElement->addChild('maxStayDefault', 28);

    }

    private function formatTemplateData(array $xmlElements)
    {

    }
}