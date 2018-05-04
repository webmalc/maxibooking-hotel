<?php


namespace MBH\Bundle\SearchBundle\Services;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchException;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\Lib\SearchResult;
use Symfony\Component\PropertyAccess\PropertyAccess;

class Searcher
{
    /** @var DocumentManager */
    private $dm;

    /**
     * Searcher constructor.
     * @param DocumentManager $dm
     */
    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }


    /**
     * @param SearchQuery $searchQuery
     * @return SearchResult|null
     * @throws SearchException
     */
    public function search(SearchQuery $searchQuery): ?SearchResult
    {
        $searchResult = new SearchResult();

        $this->preFilter($searchQuery);

        return $searchResult;
    }

    /**
     * @param SearchQuery $searchQuery
     * @throws SearchException
     */
    private function preFilter(SearchQuery $searchQuery): void
    {
        $accessor = PropertyAccess::createPropertyAccessor();
        $needFields = [
            'begin',
            'end',
            'tariffId',
            'roomTypeId',
            'adults'
        ];
        foreach ($needFields as $needField) {
            if (!$accessor->getValue($searchQuery, $needField)) {
                throw new SearchException('Terminate Search cause error in search query');
            }
        }
    }

    private function getRoomCaches(): array
    {
        $roomCaches = [];

        if (empty($roomCaches)) {
            throw new SearchException('No RoomCaches Found');
        }

    }
}