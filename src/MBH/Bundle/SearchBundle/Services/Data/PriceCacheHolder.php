<?php


namespace MBH\Bundle\SearchBundle\Services\Data;


use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\ClientBundle\Document\ClientConfigRepository;
use MBH\Bundle\SearchBundle\Lib\Data\DataFetchQueryInterface;
use MBH\Bundle\SearchBundle\Lib\Data\PriceCacheFetchQuery;

class PriceCacheHolder implements DataHolderInterface
{
    protected $data;

    /** @var bool */
    private $isUseCategory;

    public function __construct(ClientConfigRepository $configRepository)
    {
        $this->isUseCategory = $configRepository->fetchConfig()->getUseRoomTypeCategory();
    }


    /**
     * @param DataFetchQueryInterface|PriceCacheFetchQuery $fetchQuery
     * @return array|null
     */
    public function get(DataFetchQueryInterface $fetchQuery): ?array
    {

        $hash = $fetchQuery->getHash();
        $hashedPriceCaches = $this->data[$hash] ?? null;
        if (null === $hashedPriceCaches) {
            return null;
        }

        $priceCacheBegin = $fetchQuery->getBegin();
        $priceCacheEnd = $fetchQuery->getEnd();
        $roomTypeId = $fetchQuery->getRoomTypeId();
        $searchingTariffId = $fetchQuery->getTariffId();

        $groupedPriceCaches = $hashedPriceCaches[$roomTypeId.'_'.$searchingTariffId] ?? [];
        $priceCaches = [];
        if (\count($groupedPriceCaches)) {
            foreach (new \DatePeriod($priceCacheBegin, \DateInterval::createFromDateString('1 day'), (clone $priceCacheEnd)->modify('+1 days')) as $day) {
                /** @var \DateTime $day */
                $dateKey = $day->format('d-m-Y');
                $priceCache = $groupedPriceCaches[$dateKey] ?? null;
                if ($priceCache) {
                    $priceCaches[] = $priceCache;
                }
            }

        }

        return $priceCaches;

    }

    /**
     * @param DataFetchQueryInterface $fetchQuery
     * @param array $data
     */
    public function set(DataFetchQueryInterface $fetchQuery, array $data): void
    {
        $hash = $fetchQuery->getHash();
        $priceCaches = [];
        foreach ($data as $priceCache) {
            $priceSetKey = $this->createPriceCacheSetKey($priceCache);
            $dateTimeKey = Helper::convertMongoDateToDate($priceCache['date'])->format('d-m-Y');
            $priceCaches[$priceSetKey][$dateTimeKey] = $priceCache;
        }
        $this->data[$hash] = $priceCaches;
    }

    private function createPriceCacheSetKey(array $priceCache): string
    {
        $roomTypeField = $this->isUseCategory ? 'roomTypeCategory' : 'roomType';
        $key = (string)$priceCache[$roomTypeField]['$id'] . '_' . (string)$priceCache['tariff']['$id'];

        return $key;
    }

}