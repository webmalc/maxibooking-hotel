<?php


namespace MBH\Bundle\SearchBundle\Services;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\PriceBundle\Document\Restriction;
use MBH\Bundle\SearchBundle\Document\SearchConditions;
use MBH\Bundle\SearchBundle\Lib\Exceptions\RestrictionsCheckerException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\RestrictionsCheckerServiceException;
use MBH\Bundle\SearchBundle\Lib\Restrictions\RestrictionsCheckerInterface;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;

class RestrictionsCheckerService
{
    /**
     * @var RestrictionsCheckerInterface[]
     */
    private $checkers;

    /**
     * @var array
     */
    private $restrictions;

    /**
     * @var SearchConditions
     */
    private $conditions;

    /** @var DocumentManager */
    private $dm;

    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
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
        if (null === $this->restrictions) {
            $this->restrictions = $this->getRestrictions();
        }

        try {
            if (!$searchQuery->isRestrictionsWhereChecked()) {
                $restrictions = $this->getNecessaryRestrictions($searchQuery);
                foreach ($this->checkers as $checker) {
                    $checker->check($searchQuery, $restrictions);
                }
                $searchQuery->setRestrictionsWhereChecked();
            }

        } catch (RestrictionsCheckerException $e) {
            return false;
        }

        return true;
    }

    private function getNecessaryRestrictions(SearchQuery $query)
    {
        return [];
    }

    /**
     * @throws RestrictionsCheckerServiceException
     */
    private function getRestrictions(): array
    {
        if (!$this->conditions) {
            throw new RestrictionsCheckerServiceException('There is no conditions in checker service!');
        }

        $restrictions = $this->dm->getRepository(Restriction::class)->getWithConditions($this->conditions);

        return $restrictions;

    }

    public function setConditions(SearchConditions $conditions): RestrictionsCheckerService
    {
        $this->conditions = $conditions;

        return $this;
    }


}