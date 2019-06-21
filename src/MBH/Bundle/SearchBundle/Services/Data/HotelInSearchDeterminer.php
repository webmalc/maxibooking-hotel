<?php


namespace MBH\Bundle\SearchBundle\Services\Data;


use Doctrine\ODM\MongoDB\MongoDBException;
use MBH\Bundle\BaseBundle\Service\HotelSelector;
use MBH\Bundle\HotelBundle\Document\HotelRepository;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SharedFetcherException;

/**
 * Class HotelInSearchDeterminer
 * @package MBH\Bundle\SearchBundle\Services\Data
 */
class HotelInSearchDeterminer
{
    /** @var HotelRepository */
    private $hotelRepository;

    /** @var HotelSelector */
    private $hotelSelector;

    /** @var SharedDataFetcher */
    private $sharedDataFetcher;

    /**
     * HotelInSearchDeterminer constructor.
     * @param HotelRepository $hotelRepository
     * @param HotelSelector $hotelSelector
     * @param SharedDataFetcher $sharedDataFetcher
     */
    public function __construct(
        HotelRepository $hotelRepository,
        HotelSelector $hotelSelector,
        SharedDataFetcher $sharedDataFetcher
    ) {
        $this->hotelRepository = $hotelRepository;
        $this->hotelSelector = $hotelSelector;
        $this->sharedDataFetcher = $sharedDataFetcher;
    }


    /**
     * @return array
     * @throws MongoDBException
     * @throws SharedFetcherException
     */
    public function getHotelIdsInSearch(): array
    {
        $result = [];
        $searchHotelIds = $this->hotelRepository->getSearchActiveIds();
        foreach ($searchHotelIds as $searchHotelId) {
            $hotel = $this->sharedDataFetcher->getFetchedHotel($searchHotelId);
            if ($this->hotelSelector->checkPermissions($hotel)) {
                $result[] = $searchHotelId;
            }
        }

        return $result;
    }
}