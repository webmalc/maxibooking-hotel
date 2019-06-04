<?php


namespace MBH\Bundle\SearchBundle\Services;


use MBH\Bundle\SearchBundle\Lib\Exceptions\RestrictionsCheckerException;
use MBH\Bundle\SearchBundle\Lib\Restrictions\RestrictionsCheckerInterface;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\Services\Data\Fetcher\DataManager;
use MBH\Bundle\SearchBundle\Services\Data\Fetcher\RestrictionsRawFetcher;

class RestrictionsCheckerService
{
    /**
     * @var RestrictionsCheckerInterface[]
     */
    private $checkers;

    /** @var DataManager */
    private $dataManager;

    /** @var string[] */
    private $errors = [];

    public function __construct(DataManager $restrictionsFetcher)
    {
        $this->dataManager = $restrictionsFetcher;
    }


    /**
     * @param RestrictionsCheckerInterface $checker
     */
    public function addChecker(RestrictionsCheckerInterface $checker): void
    {
        $this->checkers[] = $checker;
    }

    public function check(SearchQuery $searchQuery): bool
    {
        $this->errors = [];
        if ($searchQuery->isIgnoreRestrictions()) {
            return true;
        }
        $isError = false;
        if (!$searchQuery->isRestrictionsAlreadyChecked()) {
            /** TODO: Remove all commented and dependencies */
//            $fetchQuery = RestrictionsFetchQuery::createInstanceFromSearchQuery($searchQuery);
//            $restrictions = $this->restrictionFetcher->fetchNecessaryDataSet($fetchQuery);
            $restrictions = $this->dataManager->fetchData($searchQuery, RestrictionsRawFetcher::NAME);

            if (!empty($restrictions)) {
                foreach ($this->checkers as $checker) {
                    try {
                        $checker->check($searchQuery, $restrictions);
                    } catch (RestrictionsCheckerException $e) {
                        $isError = true;
                        $this->addErrorInfo($searchQuery, $e);
                    }
                }
            }
            $searchQuery->setRestrictionsWhereChecked();
        }

        return !$isError;
    }

    private function addErrorInfo(SearchQuery $searchQuery, RestrictionsCheckerException $e): void
    {
        $error = [
            'date' => $searchQuery->getBegin()->format('d-m-Y') . '_' . $searchQuery->getEnd()->format('d-m-Y'),
            'tariff' => $searchQuery->getTariffId(),
            'roomType' => $searchQuery->getRoomTypeId(),
            'error' => $e->getMessage(),

        ];
        $this->errors[] = $error;
    }

    public function getErrors(): ?array
    {
        return $this->errors;
    }

}