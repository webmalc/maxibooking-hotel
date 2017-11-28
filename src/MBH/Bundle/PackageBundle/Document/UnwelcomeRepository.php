<?php

namespace MBH\Bundle\PackageBundle\Document;

use MBH\Bundle\BaseBundle\Lib\Exception;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\ClientBundle\Service\Mbhs;
use MBH\Bundle\HotelBundle\Document\City;
use MBH\Bundle\HotelBundle\Document\Hotel;

/**
 * Class UnwelcomeHistoryRepository

 */
class UnwelcomeRepository
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
     * for insert to unwelcome history repository
     * @param Tourist $tourist
     * @return bool
     */
    public function isInsertedTouristValid(Tourist $tourist)
    {
        return
            $tourist->getDocumentRelation() &&
            $tourist->getDocumentRelation()->getNumber() &&
            $tourist->getDocumentRelation()->getSeries()
        ;
    }

    public function isFoundTouristValid(Tourist $tourist)
    {
        return $this->isInsertedTouristValid($tourist) ||
        ($tourist->getFirstName() && $tourist->getLastName() && $tourist->getBirthday());
    }

    /**
     * @param Unwelcome $unwelcome
     * @param Tourist $tourist
     * @return bool
     * @throws Exception
     */
    public function add(Unwelcome $unwelcome, Tourist $tourist)
    {
        if(!$this->isInsertedTouristValid($tourist)) {
            throw new Exception('Tourist is not valid for adding to unwelcome history repository');
        }

        $this->fillUnwelcome($unwelcome, $tourist);
        return $this->mbhs->addUnwelcome($unwelcome, $tourist);
    }

    private function fillUnwelcome(Unwelcome $unwelcome, Tourist $tourist)
    {
        $unwelcome->setTouristCitizenship($tourist->getCitizenshipTld() ? $tourist->getCitizenshipTld()->getName() : null);
        $unwelcome->setTouristEmail($tourist->getEmail());
        $unwelcome->setTouristPhone($tourist->getPhone());
    }

    /**
     * @param Unwelcome $unwelcome
     * @param Tourist $tourist
     * @return bool
     */
    public function update(Unwelcome $unwelcome, Tourist $tourist)
    {
        $this->fillUnwelcome($unwelcome, $tourist);
        return $this->mbhs->updateUnwelcome($unwelcome, $tourist);
    }

    /**
     * @param Tourist $tourist
     * @return UnwelcomeHistory|null
     */
    public function findByTourist(Tourist $tourist)
    {
        if(!$this->isFoundTouristValid($tourist)) {
            //throw new Exception;
        }

        $responseData = $this->mbhs->findUnwelcomeListByTourist($tourist);
        if($responseData && isset($responseData['unwelcomeList'])) {
            return $this->hydrateUnwelcomeList($responseData['unwelcomeList']);;
        }

        return null;
    }

    /**
     * @param Tourist $tourist
     * @return bool
     */
    public function isUnwelcome(Tourist $tourist)
    {
        if(!$this->isFoundTouristValid($tourist)) {
            //throw new Exception;
        }

        return $this->hasUnwelcome($tourist);
    }

    /**
     * @param Tourist $tourist
     * @return bool
     */
    public function hasUnwelcome(Tourist $tourist)
    {
        return $this->mbhs->hasUnwelcome($tourist);
    }

    /**
     * @param Tourist $tourist
     * @return array|null
     */
    public function deleteByTourist(Tourist $tourist)
    {
        return $this->mbhs->deleteUnwelcomeByTourist($tourist);
    }


    /**
     * @param array $unwelcomeList
     * @return Unwelcome[]
     */
    public function hydrateUnwelcomeList(array $unwelcomeList)
    {
        $result = [];
        foreach($unwelcomeList as $unwelcomeData) {
            $result[] = $this->hydrateUnwelcome($unwelcomeData);
        }

        return $result;
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
            //->setTouristCitizenship($unwelcomeData['touristCitizenship'])
            //->setTouristEmail($unwelcomeData['touristEmail'])
            //->setTouristPhone($unwelcomeData['touristPhone'])
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
            $hotel->setCityId($city);
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