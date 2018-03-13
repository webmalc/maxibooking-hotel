<?php

namespace MBH\Bundle\PriceBundle\Services;

use Doctrine\MongoDB\CursorInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use Liip\FunctionalTestBundle\Validator\DataCollectingValidator;
use MBH\Bundle\BaseBundle\Service\Messenger\Notifier;
use MBH\Bundle\HotelBundle\Document\Room;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\OnlineBookingBundle\Lib\OnlineSearchFormData;
use MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper\OnlineDataProviderWrapperInterface;
use MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper\OnlineResultInstance;
use MBH\Bundle\OnlineBookingBundle\Service\SpecialDataPreparer;
use MBH\Bundle\PackageBundle\Document\Criteria\PackageQueryCriteria;
use MBH\Bundle\PackageBundle\Lib\SearchResult;
use MBH\Bundle\PackageBundle\Services\Search\SearchFactory;
use MBH\Bundle\PriceBundle\Document\Special;
use MBH\Bundle\PriceBundle\Document\SpecialPrice;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\PriceBundle\Lib\SpecialFilter;
use Monolog\Logger;

/**
 * Class SpecialHandler
 * @package MBH\Bundle\PriceBundle\Services
 */
class SpecialHandler
{
    /** @var SearchFactory $search */
    private $search;
    /** @var  DocumentManager */
    private $dm;
    /** @var  SpecialDataPreparer */
    private $specialHelper;
    /** @var OnlineDataProviderWrapperInterface */
    private $specialSearchHelper;
    /** @var  OnlineSearchFormData */
    private $onlineSearchFormData;
    /** @var Notifier */
    private $mailer;
    /** @var array */
    private $disabledSpecials = [];
    /** @var array */
    private $enabledSpecials = [];
    /** @var DataCollectingValidator  */
    private $validator;

    /**
     * SpecialHandler constructor.
     * @param SearchFactory $search
     * @param DocumentManager $dm
     * @param Logger $logger
     * @param SpecialDataPreparer $specialHelper
     * @param OnlineDataProviderWrapperInterface $specialSearchHelper
     * @param OnlineSearchFormData $onlineSearchFormData
     * @param Notifier $mailer
     */
    public function __construct(
        SearchFactory $search,
        DocumentManager $dm,
        Logger $logger,
        SpecialDataPreparer $specialHelper,
        OnlineDataProviderWrapperInterface $specialSearchHelper,
        OnlineSearchFormData $onlineSearchFormData,
        Notifier $mailer
//        DataCollectingValidator $validator

    ) {
        $this->dm = $dm;
        $this->search = $search;
        $this->logger = $logger;
        $this->specialHelper = $specialHelper;
        $this->specialSearchHelper = $specialSearchHelper;
        $this->onlineSearchFormData = $onlineSearchFormData;
        $this->mailer = $mailer;
//        $this->validator = $validator;

    }


    /**
     * @param array $specialIds
     * @param array $roomTypeIds
     * @return void
     */
    public function calculatePrices(array $specialIds = [], array $roomTypeIds = [], callable $output = null): void
    {
        $this->disabledSpecials = $this->enabledSpecials = [];
        $specials = $this->getSpecials($specialIds);
        /** @var Special $special */
        foreach ($specials as $special) {
            $this->calculateSpecial($special->getId(), $output);
        }

        $this->notify();
    }

    /**
     * @param string $specialId
     * @param callable|null $output
     */
    private function calculateSpecial(string $specialId, callable $output = null): void
    {
        $special = $this->getSpecial($specialId);

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
        $searchForm->setForceCapacityRestriction(true);
        /** @var array $searchResults */
        $searchResults = $this->specialSearchHelper->getResults($searchForm);
        $error = '';

        /** @var OnlineResultInstance $onlineSearchResult */
        if (!count($searchResults)) {
            if ($special->getSold() === 0) {
                $error = 'Поиск не вернул результат для спецпредложения';
            }

        }

        if (count($searchResults)) {
            $onlineSearchResult = $searchResults[0];
            /** @var SearchResult $searchResult */

            if (count($onlineSearchResult->getResults())) {

                $searchResult = $searchResults[0]->getResults()->first();
                $specialPrice = $this->createSpecialPrice(
                    $searchResult->getTariff(),
                    $searchResult->getRoomType(),
                    $searchResult->getPrices()
                );
                $special->addPrice($specialPrice);
                $this->addLogMessage('Найдены цены', $searchResult->getPrices(), $output);

                if (!$onlineSearchResult->isSameVirtualRoomInSpec()) {
                    $error ='Виртуальная комната занята';
                }
            }

        }

        if ($error && $special->getIsEnabled()) {
            $special->setError($error);
            $special->setIsEnabled(false);
            $this->disabledSpecials[] = $special;
            $this->addLogMessage(
                $error,
                ['specialId' => $special->getId(), 'specialName' => $special->getName()],
                $output
            );
        }

        if (!$error && !$special->getIsEnabled()){
            $special->setIsEnabled(true);
            $special->clearError();
            $this->enabledSpecials[] = $special;
            $this->addLogMessage(
                'Повторное включение спецпредложения',
                ['specialId' => $special->getId(), 'specialName' => $special->getName()],
                $output
            );
        }

        $special->setNoRecalculation();
//        $errors = $this->validator->validate($special);
//        if (!count($errors)) {
//            $this->dm->flush();
//        } else {
//            $this->addLogMessage('Ошибка сохранения спецпредложения!', ['specialName' => $special->getName], $output);
//        }
        $this->dm->flush();
        $this->dm->clear();

        $this->addLogMessage(
            'End recalculate for special',
            ['specialId' => $special->getId(), 'specialName' => $special->getName()],
            $output
        );

    }

    private function createSpecialPrice(Tariff $tariff, RoomType $roomType, array $prices)
    {
        $specialPrice = new SpecialPrice();
        $specialPrice
            ->setTariff($tariff)
            ->setRoomType($roomType)
            ->setPrices($prices);

        return $specialPrice;
    }


    private function getSpecial(string $specialId) {
        $special = $this->dm->find('MBHPriceBundle:Special', ['id' => $specialId]);

        return $special;
    }



    //Задумывал выводить какая бронь перекрывает спецпредолжение. Пока не надо.
    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param Room $virtualRoom
     * @return array|\MBH\Bundle\PackageBundle\Document\Package[]
     */
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

    /**
     * @param string $message
     * @param array $context
     * @param callable|null $output
     */
    private function addLogMessage(string $message, array $context, callable $output = null)
    {
        $this->logger->addInfo($message, $context);
        if ($output && is_callable($output)) {
            $output($message, $context);
        }
    }

    /**
     * @param Special $special
     * @return OnlineSearchFormData
     */
    private function getFormData(Special $special): OnlineSearchFormData
    {
        $roomType = $special->getVirtualRoom() ? $special->getVirtualRoom()->getRoomType() : null;
        $data = clone($this->onlineSearchFormData);
        $data->setSpecial($special);
        if ($roomType) {
            $data->setRoomType($roomType);
        }
        $data->setForceSearchDisabledSpecial(true);
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
            $specialFilter = new SpecialFilter();
            $filterBegin = new \DateTime('midnight');
            $specialFilter
                ->setBegin($filterBegin)
                ->setIsEnabled(true);


            $specials = $this->dm->getRepository('MBHPriceBundle:Special')->getFiltered($specialFilter);
        }

        return $specials;
    }


    private function notify()
    {
        if (count($this->disabledSpecials) || count($this->enabledSpecials)) {
            $message = $this->mailer::createMessage();
            $message
                ->setText('special.auto.disable')
                ->setFrom('system')
                ->setSubject('special.disable')
                ->setType('info')
                ->setTemplate('MBHBaseBundle:Mailer:specials.autoDisable.html.twig')
                ->setAdditionalData([
                    'disabledSpecials' => $this->disabledSpecials,
                    'enabledSpecials' => $this->enabledSpecials
                ])
                ->setAutohide(false)
                ->setEnd(new \DateTime('+1 minute'));

            $this->mailer
                ->setMessage($message)
                ->notify();
        }
    }
}