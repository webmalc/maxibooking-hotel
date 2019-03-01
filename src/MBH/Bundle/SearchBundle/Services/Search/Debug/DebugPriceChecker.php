<?php


namespace MBH\Bundle\SearchBundle\Services\Search\Debug;


use MBH\Bundle\PackageBundle\Document\SearchQuery;
use MBH\Bundle\PackageBundle\Lib\SearchResult;
use MBH\Bundle\PackageBundle\Services\Search\Search;
use MBH\Bundle\PriceBundle\Document\TariffRepository;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class DebugPriceChecker
 * @package MBH\Bundle\SearchBundle\Services\Search\Debug
 */
class DebugPriceChecker
{

    /** @var Search */
    private $legacySearch;

    /** @var TariffRepository */
    private $tariffRepository;

    /** @var NotifierInterface */
    private $notifier;

    /**
     * DebugPriceChecker constructor.
     * @param Search $legacySearch
     * @param TariffRepository $tariffRepository
     * @param NotifierInterface $notifier
     */
    public function __construct(Search $legacySearch, TariffRepository $tariffRepository, NotifierInterface $notifier)
    {
        $this->legacySearch = $legacySearch;
        $this->tariffRepository = $tariffRepository;
        $this->notifier = $notifier;
    }

    /**
     * @param Request $request
     * @return array
     * @throws \Doctrine\ODM\MongoDB\LockException
     * @throws \Doctrine\ODM\MongoDB\Mapping\MappingException
     * @throws DebugPriceCheckerException
     */
    public function checkPrices(Request $request): array
    {
        $errors = [];
        $content = $request->getContent();
        $data = json_decode($content, true);
        if (null === $data) {
            throw new DebugPriceCheckerException('No Data to compare');
        }
        foreach ($data as $compareData) {
            $searchQuery = $this->createQuery($compareData['query']);
            $result = $this->legacySearch->search($searchQuery);
            $searchResult = reset($result);
            try {
                $this->comparePrice($searchResult, $compareData['price']);
            } catch (DebugPriceCheckerException $e) {
                $errors[] = $compareData['hash'];
                $this->notifier->notify($this->createMessage($compareData, $searchResult, $e->getLegacyPrice()));
            }
        }

        return $errors;
    }

    /**
     * @param $data
     * @return SearchQuery
     * @throws \Doctrine\ODM\MongoDB\LockException
     * @throws \Doctrine\ODM\MongoDB\Mapping\MappingException
     * @throws \Exception
     */
    private function createQuery($data): SearchQuery
    {
        $query = new SearchQuery();

        $tariffId = $data['tariff'];

        $tariff = $this->tariffRepository->find($tariffId);

        $query->tariff = $tariff;
        $query->begin = new \DateTime($data['begin']);
        $query->end = new \DateTime($data['end']);
        $query->adults = $data['adults'];
        $query->children = $data['children'];
        $query->childrenAges = $data['childrenAges'] ?? [];
        $query->roomTypes = [$data['roomType']];
        $query->forceRoomTypes = true;

        return $query;
    }

    /**
     * @param SearchResult $result
     * @param $price
     * @throws DebugPriceCheckerException
     */
    private function comparePrice(SearchResult $result, $price): void
    {
        $resultPrices = $result->getPrices();
        $legacyPrice = (int)array_values($resultPrices)[0];
        if ( $legacyPrice !== (int)$price) {
            throw new DebugPriceCheckerException('Wrong price found.', 0, null, $legacyPrice);
        }

    }

    private function createMessage(array $originData, SearchResult $result, int $legacyPrice): string
    {
        $query = $originData['query'];
        $message = sprintf(
            'Hotel:%s Begin:%s End:%s Adults:%s Children:%s ChildrenAges:%s isUseCache:%s RoomType:%s Tariff:%s WrongPrice:%s ActualLegacyPrice:%s',
            $result->getRoomType()->getHotel()->getName(),
            $query['begin'],
            $query['end'],
            $query['adults'],
            $query['children'],
            implode('_',$query['childrenAges']) ?: 'Нет',
            $query['isUseCache'] ? 'true' : 'false',
            $result->getRoomType()->getName(),
            $result->getTariff()->getName(),
            $originData['price'],
            $legacyPrice
            );

        return $message;
    }
}