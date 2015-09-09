<?php

namespace MBH\Bundle\PackageBundle\Document;

use MBH\Bundle\ClientBundle\Service\Mbhs;
use MBH\Bundle\HotelBundle\Document\City;
use MBH\Bundle\HotelBundle\Document\Hotel;

/**
 * Class UnwelcomeHistoryRepository
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
class UnwelcomeHistoryRepository
{
    /**
     * @var Mbhs
     */
    protected $mbhs;

    public function __construct(Mbhs $mbhs)
    {
        $this->mbhs = $mbhs;
    }

    /**
     * @param Unwelcome $unwelcome
     * @param Tourist $tourist
     * @return bool
     */
    public function add(Unwelcome $unwelcome, Tourist $tourist)
    {
        return $this->mbhs->addUnwelcome($unwelcome, $tourist);
    }

    /**
     * @param Unwelcome $unwelcome
     * @param Tourist $tourist
     * @return bool
     */
    public function update(Unwelcome $unwelcome, Tourist $tourist)
    {
        return $this->mbhs->updateUnwelcome($unwelcome, $tourist);
    }

    /**
     * @param Tourist $tourist
     * @return UnwelcomeHistory|null
     */
    public function findByTourist(Tourist $tourist)
    {
        $responseData = $this->mbhs->findUnwelcomeHistoryByTourist($tourist);
        if($responseData && isset($responseData['unwelcomeHistory'])) {
            $historyData = $responseData['unwelcomeHistory'];
            $unwelcomeHistory = new UnwelcomeHistory();

            $unwelcomeHistory->setTourist($tourist);
            foreach($historyData['items'] as $data) {
                $unwelcome = $this->hydrateUnwelcome($data);
                $unwelcomeHistory->addItem($unwelcome);
            }
            return $unwelcomeHistory;
        }

        return null;
    }

    /**
     * @param Tourist $tourist
     * @return bool
     */
    public function isUnwelcome(Tourist $tourist)
    {
        return $this->hasUnwelcomeHistory($tourist);
    }

    /**
     * @param Tourist $tourist
     * @return bool
     */
    public function hasUnwelcomeHistory(Tourist $tourist)
    {
        return $this->mbhs->hasUnwelcomeHistory($tourist);
    }

    /**
     * @param array $data
     * @return Unwelcome
     */
    private function hydrateUnwelcome(array $data)
    {
        $unwelcome = new Unwelcome();
        $unwelcome
            ->setIsAggressor($data['isAggressor'])
            ->setComment($data['comment'])
            ->setIsMy($data['isMy'])
        ;

        if(isset($data['arrivalTime']) && isset($data['departureTime'])) {
            $unwelcome
                ->setArrivalTime(\DateTime::createFromFormat('d.m.Y H:i:s', $data['arrivalTime']. ' 00:00:00'))
                ->setDepartureTime(\DateTime::createFromFormat('d.m.Y H:i:s', $data['departureTime']. ' 00:00:00'))
            ;
        }

        if(isset($data['hotel'])) {
            $hotel = new Hotel();
            $hotel->setTitle($data['hotel']['title']);
            $city = new City();
            $city->setTitle($data['hotel']['city']);
            $hotel->setCity($city);
            $unwelcome->setHotel($hotel);
        }

        if ($data['createdAt']) {
            $unwelcome->setCreatedAt(
                \DateTime::createFromFormat('d.m.Y H:i:s', $data['createdAt']. ' 00:00:00')
            );
        }
        return $unwelcome;
    }

    /**
     * @param Tourist $tourist
     * @return null
     */
    public function deleteByTourist(Tourist $tourist)
    {
        return $this->mbhs->deleteUnwelcomeByTourist($tourist);
    }

    /**
     * @param Tourist[] $tourists
     */
    public function updateTourists(array $tourists)
    {
        foreach($tourists as $tourist) {
            //$tourist->setIsInBlackList();
        }
    }
}