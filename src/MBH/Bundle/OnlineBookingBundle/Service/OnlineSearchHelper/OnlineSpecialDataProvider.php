<?php


namespace MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper;


use Doctrine\ODM\MongoDB\Cursor;
use MBH\Bundle\OnlineBookingBundle\Lib\OnlineSearchFormData;
use MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper\SearchQuery\OnlineSearchQueryGenerator;
use MBH\Bundle\PackageBundle\Lib\SearchResult;
use MBH\Bundle\PackageBundle\Services\Search\SearchFactory;
use MBH\Bundle\PriceBundle\Document\Special;

/**
 * Class OnlineSpecialDataProvider
 * @package MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper
 */
class OnlineSpecialDataProvider implements OnlineDataProviderInterface
{
    /** @var string */
    const TYPE = 'special';

    /** @var SearchFactory */
    private $search;

    /** @var array */
    private $onlineOptions;

    /** @var OnlineResultCreator */
    private $onlineResultCreator;
    /**
     * @var OnlineSearchQueryGenerator
     */
    private $queryGenerator;


    /**
     * OnlineSpecialDataProvider constructor.
     * @param SearchFactory $search
     * @param OnlineResultCreator $creator
     * @param OnlineSearchQueryGenerator $queryGenerator
     * @param array $onlineOptions
     */
    public function __construct(
        SearchFactory $search,
        OnlineResultCreator $creator,
        OnlineSearchQueryGenerator $queryGenerator,
        array $onlineOptions
    ) {
        $this->search = $search;
        $this->onlineResultCreator = $creator;
        $this->onlineOptions = $onlineOptions;
        $this->queryGenerator = $queryGenerator;
    }


    /**
     * @param OnlineSearchFormData $formData
     * @return array
     * @throws \MBH\Bundle\OnlineBookingBundle\Lib\Exceptions\OnlineBookingSearchException
     */
    public function search(OnlineSearchFormData $formData): array
    {
        if (!$this->onlineOptions['show_specials'] || $formData->isAddDates()) {
            return [];
        }
        $results = $founded = [];
        if ($this->isSearchForCertainSpecial($formData)) {
            $foundedSpecial = $this->searchCertainSpecial($formData);
            if ($foundedSpecial) {
                $founded[] = $foundedSpecial;
            }
        } else {
            /** @var Cursor $specials */
            $searchQuery = $this->queryGenerator->createSearchQuery($formData);
            $specials = $this->search->searchStrictSpecials($searchQuery);
            if (count($specials)) {
                $count = 0;
                foreach ($specials as $special) {
                    $newFormData = clone $formData;
                    /** @var Special $special */
                    $newFormData
                        ->setSpecial($special)
                        ->setRoomType($special->getVirtualRoom()->getRoomType());
                    $foundedSpecial = $this->searchCertainSpecial($newFormData);
                    if ($foundedSpecial) {
                        $founded[] = $foundedSpecial;
                        $count++;
                    }
                    if ($this->isSpecialLimitExceeded($count)) {
                        break;
                    }

                }
            }
        }
        if (count($founded)) {
            foreach ($founded as $result) {
                $results[] = $this->onlineResultCreator->createSpecial($result['searchResult'], $result['searchQuery'], $this->getType());
            }
        }

        return $results;
    }


    /**
     * @param OnlineSearchFormData $formData
     * @return array|null
     */
    private function searchCertainSpecial(OnlineSearchFormData $formData): ?array
    {
        $searchQuery = $this->queryGenerator->createSearchQuery($formData);
        $special = $formData->getSpecial();
        $searchQuery->begin = $special->getBegin();
        $searchQuery->end = $special->getEnd();
        $searchQuery->forceRoomTypes = true;
        $searchQuery->setPreferredVirtualRoom($special->getVirtualRoom());
        if ($formData->isForceCapacityRestriction() && $searchQuery->getPreferredVirtualRoom()) {
            $searchQuery->setIgnoreGuestRestriction(true);
        }
        $searchResults = $this->search->search($searchQuery);
        if (count($searchResults)) {
            /** @var SearchResult $searchResult */
            $searchResult = reset($searchResults);
        }
        if (isset($searchResult) && !$this->isVirtualRoomIsNull($searchResult)) {
            return [
                'searchResult' => $searchResult,
                'searchQuery' => $searchQuery,
            ];
        } else {
            return [];
        }
    }

    /**
     * @param SearchResult $searchResult
     * @return bool
     */
    private function isVirtualRoomIsNull(SearchResult $searchResult)
    {
        return $searchResult->getVirtualRoom() === null ? true : false;
    }

    /**
     * @param int $count
     * @return bool
     */
    private function isSpecialLimitExceeded(int $count): bool
    {
        $limit = $this->onlineOptions['show_special_restrict'] ?? null;

        return ($limit && $count >= $limit);
    }

    /**
     * @param OnlineSearchFormData $formData
     * @return bool
     */
    private function isSearchForCertainSpecial(OnlineSearchFormData $formData)
    {
        return $formData->getSpecial() && $formData->getRoomType();
    }


    /**
     * @return string
     */
    public function getType(): string
    {
        return self::TYPE;
    }
}