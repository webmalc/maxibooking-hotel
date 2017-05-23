<?php

namespace MBH\Bundle\PriceBundle\Services;

use Doctrine\MongoDB\CursorInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\HotelBundle\Document\Room;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\OnlineBookingBundle\Lib\OnlineSearchFormData;
use MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper\OnlineResultInstance;
use MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper\OnlineSpecialResultGenerator;
use MBH\Bundle\OnlineBookingBundle\Service\SpecialDataPreparer;
use MBH\Bundle\PackageBundle\Document\Criteria\PackageQueryCriteria;
use MBH\Bundle\PackageBundle\Lib\SearchResult;
use MBH\Bundle\PackageBundle\Services\Calculation;
use MBH\Bundle\PackageBundle\Services\Search\SearchFactory;
use MBH\Bundle\PriceBundle\Document\Special;
use MBH\Bundle\PriceBundle\Document\SpecialPrice;
use MBH\Bundle\PriceBundle\Document\Tariff;
use Monolog\Logger;

class SpecialHandler
{
    /** @var SearchFactory $search */
    private $search;
    /** @var  DocumentManager */
    private $dm;
    /** @var  SpecialDataPreparer */
    private $specialHepler;
    /** @var OnlineSpecialResultGenerator */
    private $specialSearchHelper;
    /** @var  OnlineSearchFormData */
    private $onlineSearchFormData;

    /**
     * SpecialHandler constructor.
     * @param SearchFactory $search
     * @param DocumentManager $dm
     * @param Logger $logger
     * @param SpecialDataPreparer $specialHelper
     * @param OnlineSpecialResultGenerator $specialSearchHelper
     * @param OnlineSearchFormData $onlineSearchFormData
     */
    public function __construct(
        SearchFactory $search,
        DocumentManager $dm,
        Logger $logger,
        SpecialDataPreparer $specialHelper,
        OnlineSpecialResultGenerator $specialSearchHelper,
        OnlineSearchFormData $onlineSearchFormData
    ) {
        $this->dm = $dm;
        $this->search = $search;
        $this->logger = $logger;
        $this->specialHepler = $specialHelper;
        $this->specialSearchHelper = $specialSearchHelper;
        $this->onlineSearchFormData = $onlineSearchFormData;
    }


    /**
     * @param array $specialIds
     * @param array $roomTypeIds
     * @return void
     */
    public function calculatePrices(array $specialIds = [], array $roomTypeIds = [], callable $output = null): void
    {
        $specials = $this->getSpecials($specialIds);
        /** @var Special $special */
        foreach ($specials as $special) {
            $this->calculateSpecial($special->getId(), $output);
        }
    }

    private function calculateSpecial(string $specialId, callable $output = null): void
    {
        $special = $this->dm->find('MBHPriceBundle:Special', ['id' => $specialId]);
        if (!$special) {
            return;
        }
        $special->setRecalculation();
        $this->dm->flush();
        $special->removeAllPrices();
        $this->addLogMessage(
            'Start calculate for special',
            ['specialId' => $special->getId(), 'specialName' => $special->getName()],
            $output
        );
        //Здесь используется уже готовый код для поиска в онлайн
        $searchForm = $this->getFormData($special);
        $searchResults = $this->specialSearchHelper->getResults($searchForm);
        $err = '';
        /** @var OnlineResultInstance $onlineSearchResult */
        if (!$searchResults->isEmpty()) {
            $onlineSearchResult = $searchResults->first();
            /** @var SearchResult $searchResult */
            if (count($onlineSearchResult->getResults()) && $onlineSearchResult->isSameVirtualRoomInSpec()) {
                $searchResult = $searchResults->first()->getResults()->first();
                $specialPrice = new SpecialPrice();
                $specialPrice
                    ->setTariff($searchResult->getTariff())
                    ->setRoomType($searchResult->getRoomType())
                    ->setPrices($searchResult->getPrices());
                $special->addPrice($specialPrice);
                $special->clearError();
                $this->addLogMessage('Найдены цены', $searchResult->getPrices(), $output);
            } else {
                $err = 'Нет подходящих вариантов для спецпредложения';
                if (!$onlineSearchResult->isSameVirtualRoomInSpec()) {
                    $err.=' виртуальная комната занята';
                }
                $special->setError($err);

            }
        } else {
            $err = 'Поиск не вернул результат для спецпредложения';
        }

        if ($err) {
            $this->addLogMessage(
                $err,
                ['specialId' => $special->getId(), 'specialName' => $special->getName()],
                $output
            );
        }


        $special->setNoRecalculation();
        $this->dm->flush();
        $this->dm->clear();
        $this->addLogMessage(
            'End recalculate for special',
            ['specialId' => $special->getId(), 'specialName' => $special->getName()],
            $output
        );
    }

    private function getPackage(\DateTime $begin, \DateTime $end, Room $virtualRoom)
    {
        $criteria = new PackageQueryCriteria();
        $criteria->begin = $begin;
        $criteria->end = $end;
        $criteria->roomType = $virtualRoom->getRoomType();
        $criteria->virtualRoom = $virtualRoom;
        $packages = $this->dm->getRepository('MBHPackageBundle:Package')->findByQueryCriteria($criteria);
        if ($packages && count($packages) && $packages instanceof CursorInterface) {
            $packages = $packages->toArray();
        }

        return $packages;
    }

    private function addLogMessage(string $message, array $context, callable $output = null)
    {
        $this->logger->addInfo($message, $context);
        if ($output && is_callable($output)) {
            $output($message, $context);
        }
    }

    private function getFormData(Special $special): OnlineSearchFormData
    {
        $roomType = $special->getVirtualRoom() ? $special->getVirtualRoom()->getRoomType() : null;
        $data = clone($this->onlineSearchFormData);
        $data->setSpecial($special);
        if ($roomType) {
            $data->setRoomType($roomType);
        }
        $data->setCache(false);

        return $data;
    }

    /**
     * @param array $specialIds
     * @return mixed
     */
    private function getSpecials(array $specialIds)
    {
        if (count($specialIds)) {
            $qb = $this->dm->getRepository('MBHPriceBundle:Special')->createQueryBuilder();
            $specials = $qb->field('id')->in($specialIds)->getQuery()->execute();
        } else {
            $specials = $this->specialHepler->getSpecials();
        }

        return $specials;
    }
}