<?php

namespace MBH\Bundle\PackageBundle\Document;

use MBH\Bundle\BaseBundle\Service\Helper;
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
            return $this->hydrateUnwelcomeHistory($responseData['unwelcomeHistory']);;
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
     * @param Tourist $tourist
     * @return null
     */
    public function deleteByTourist(Tourist $tourist)
    {
        return $this->mbhs->deleteUnwelcomeByTourist($tourist);
    }

    /**
     * @param array $historyData
     * @return UnwelcomeHistory
     */
    public function hydrateUnwelcomeHistory(array $historyData)
    {
        $unwelcomeHistory = new UnwelcomeHistory();

        if(isset($historyData['tourist'])) {
            $tourist = $this->hydrateTourist($historyData['tourist']);
            $unwelcomeHistory->setTourist($tourist);
        }
        foreach($historyData['items'] as $unwelcomeData) {
            $unwelcome = $this->hydrateUnwelcome($unwelcomeData);
            $unwelcomeHistory->addItem($unwelcome);
        }

        return $unwelcomeHistory;
    }

    /**
     * @param array $touristData
     * @return Tourist
     */
    private function hydrateTourist(array $touristData)
    {
        $tourist = new Tourist();
        $tourist->setFirstName($touristData['firstName']);
        $tourist->setLastName($touristData['lastName']);
        $tourist->setPatronymic($touristData['patronymic']);
        $tourist->setBirthday(Helper::getDateFromString($touristData['birthday']));
        $tourist->setPhone($touristData['phone']);
        $tourist->setEmail($touristData['email']);
        //$tourist->setCitizenship($touristData['citizenship']);
        return $tourist;
    }

    /**
     * @param array $unwelcomeData
     * @return Unwelcome
     */
    private function hydrateUnwelcome(array $unwelcomeData)
    {
        $unwelcome = new Unwelcome();
        $unwelcome
            ->setFoul($unwelcomeData['foul'])
            ->setAggression($unwelcomeData['aggression'])
            ->setInadequacy($unwelcomeData['inadequacy'])
            ->setDrunk($unwelcomeData['drunk'])
            ->setDrugs($unwelcomeData['drugs'])
            ->setDestruction($unwelcomeData['destruction'])
            ->setMaterialDamage($unwelcomeData['materialDamage'])
            ->setComment($unwelcomeData['comment'])
            ->setIsMy($unwelcomeData['isMy'])
        ;

        if(isset($unwelcomeData['arrivalTime']) && isset($unwelcomeData['departureTime'])) {
            $unwelcome
                ->setArrivalTime(\DateTime::createFromFormat('d.m.Y H:i:s', $unwelcomeData['arrivalTime']. ' 00:00:00'))
                ->setDepartureTime(\DateTime::createFromFormat('d.m.Y H:i:s', $unwelcomeData['departureTime']. ' 00:00:00'))
            ;
        }

        if(isset($unwelcomeData['hotel'])) {
            $hotel = new Hotel();
            $hotel->setTitle($unwelcomeData['hotel']['title']);
            $city = new City();
            $city->setTitle($unwelcomeData['hotel']['city']);
            $hotel->setCity($city);
            $unwelcome->setHotel($hotel);
        }

        if ($unwelcomeData['createdAt']) {
            $unwelcome->setCreatedAt(
                \DateTime::createFromFormat('d.m.Y H:i:s', $unwelcomeData['createdAt']. ' 00:00:00')
            );
        }
        return $unwelcome;
    }
}