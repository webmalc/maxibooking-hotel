<?php


namespace MBH\Bundle\SearchBundle\Services;


use MBH\Bundle\SearchBundle\Lib\Data\RestrictionsFetchQuery;
use MBH\Bundle\SearchBundle\Lib\Exceptions\RestrictionsCheckerException;
use MBH\Bundle\SearchBundle\Lib\Restrictions\RestrictionsCheckerInterface;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\Services\Data\RestrictionsFetcher;

class RestrictionsCheckerService
{
    /**
     * @var RestrictionsCheckerInterface[]
     */
    private $checkers;

    /** @var RestrictionsFetcher */
    private $restrictionFetcher;

    /** @var string[] */
    private $errors = [];

    public function __construct(RestrictionsFetcher $restrictionsFetcher)
    {
        $this->restrictionFetcher = $restrictionsFetcher;
    }


    /**
     * @param RestrictionsCheckerInterface $checker
     */
    public function addChecker(RestrictionsCheckerInterface $checker): void
    {
        $this->checkers[] = $checker;
    }

    /**
     * @param SearchQuery $searchQuery
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function check(SearchQuery $searchQuery): bool
    {
        $isError = false;
        if (!$searchQuery->isRestrictionsWhereChecked()) {
            $fetchQuery = RestrictionsFetchQuery::createInstanceFromSearchQuery($searchQuery);
            $restrictions = $this->restrictionFetcher->fetchNecessaryDataSet($fetchQuery);
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