<?php

namespace MBH\Bundle\PackageBundle\Services;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\BaseBundle\Service\DocumentFieldsManager;
use MBH\Bundle\PackageBundle\Document\CalculatedPackagePrices;

class CalculationCache
{
    const SEARCH_CRITERIA_FIELDS = [
        'roomType',
        'tariff',
        'begin',
        'end',
        'promotion',
        'useCategories',
        'special',
    ];

    private $dm;
    private $fieldsManager;
    private $calculation;

    public function __construct(DocumentManager $dm, DocumentFieldsManager $fieldsManager, Calculation $calculation) {
        $this->dm = $dm;
        $this->fieldsManager = $fieldsManager;
        $this->calculation = $calculation;
    }

    /**
     * @param CalculatedPackagePrices $calculatedPackagePrices
     * @return CalculatedPackagePrices|null|object
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function find(CalculatedPackagePrices $calculatedPackagePrices)
    {
        $searchCriteriaArray = $this->fieldsManager
            ->fillByDocumentFieldsWithFieldNameKeys($calculatedPackagePrices, self::SEARCH_CRITERIA_FIELDS);

        /** @var DocumentRepository $cachesRepo */
        $cachesRepo = $this->dm->getRepository('MBHPackageBundle:CalculatedPackagePrices');
        $pricesCachedObj = $cachesRepo->findOneBy($searchCriteriaArray);

        if (!is_null($pricesCachedObj)) {
            return $pricesCachedObj;
        }

        $partialCaches = $this->getPartialCaches($calculatedPackagePrices, $searchCriteriaArray, $cachesRepo);

        /** @var CalculatedPackagePrices $cache */
        foreach ($partialCaches as $cache) {
            if ($cache->getBegin() > $calculatedPackagePrices->getBegin()) {
                $emptyIntervalPrices = $this->calculation->calcPrices(
                    $calculatedPackagePrices->getRoomType(),
                    $calculatedPackagePrices->getTariff(),
                    $calculatedPackagePrices->getBegin(),
                    $cache->getBegin(),
                    0,
                    0,
                    $calculatedPackagePrices->getPromotion(),
                    $calculatedPackagePrices->isUseCategories(),
                    $calculatedPackagePrices->getSpecial()
                );

                if (!$emptyIntervalPrices) {
                    return null;
                }

                foreach ($calculatedPackagePrices->getPackagePrices() as $packagePriceForCombination) {
                    $mergedPackagePriceForCombination = $emptyIntervalPrices->getPackagePriceForCombination(
                        $packagePriceForCombination->getAdults(),
                        $packagePriceForCombination->getChildren()
                    );
                    if ($mergedPackagePriceForCombination) {
                        return null;
                    }

                    $missingPeriod = new \DatePeriod($cache->getBegin(), new \DateInterval('P1D'), $calculatedPackagePrices->getBegin());
                    /** @var \DateTime $day */
                    foreach ($missingPeriod as $day) {
                        $packageDayPrice = $mergedPackagePriceForCombination->getPackagePriceOnDate($day);

                    }
                }
            }

            if ($cache->getEnd() > $calculatedPackagePrices->getEnd()) {

            }
        }

        return null;
    }

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param array $tariffsIds
     * @param array $roomTypesIds
     * @return integer Number of removed cache items
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function clearCache(\DateTime $begin, \DateTime $end, $tariffsIds = [], $roomTypesIds = [])
    {
        /** @var DocumentRepository $cacheRepo */
        $cacheRepo = $this->dm->getRepository('MBHPackageBundle:SearchResultCacheItem');
        $result = $cacheRepo
            ->createQueryBuilder()
            ->remove()
            ->field('begin')->lte($end)
            ->field('end')->gt($begin)
            ->field('tariff.id')->in($tariffsIds)
            ->field('roomTypeId')->in($roomTypesIds)
            ->getQuery()
            ->execute();

        return $result['n'];
    }

    public function warmup()
    {
        
    }

    private function mergePackagePrices(CalculatedPackagePrices $resultPackagePrice, CalculatedPackagePrices $mergedPackagePrice)
    {

    }

    /**
     * @param CalculatedPackagePrices $calculatedPackagePrices
     * @param array $searchCriteriaArray
     * @param DocumentRepository $cachesRepo
     * @return mixed
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    private function getPartialCaches(CalculatedPackagePrices $calculatedPackagePrices, array $searchCriteriaArray, DocumentRepository $cachesRepo)
    {
        $partialCacheObjectsCriteriaFields = array_diff(array_keys($searchCriteriaArray), ['begin', 'end']);
        $partialCacheObjectsCriteria = $this->fieldsManager
            ->fillByDocumentFieldsWithFieldNameKeys($calculatedPackagePrices, $partialCacheObjectsCriteriaFields);

        $partialCaches = $cachesRepo
            ->createQueryBuilder()
            ->setQueryArray($partialCacheObjectsCriteria)
            ->sort('begin')
            ->field('begin')->lte($calculatedPackagePrices->getEnd())
            ->field('end')->gt($calculatedPackagePrices->getBegin())
            ->getQuery()
            ->execute();

        return $partialCaches;
    }
}