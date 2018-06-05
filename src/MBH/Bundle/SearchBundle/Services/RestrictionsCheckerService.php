<?php


namespace MBH\Bundle\SearchBundle\Services;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\PriceBundle\Document\Restriction;
use MBH\Bundle\SearchBundle\Document\SearchConditions;
use MBH\Bundle\SearchBundle\Lib\DataHolder;
use MBH\Bundle\SearchBundle\Lib\Exceptions\RestrictionsCheckerException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\RestrictionsCheckerServiceException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchException;
use MBH\Bundle\SearchBundle\Lib\Restrictions\RestrictionsCheckerInterface;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use Symfony\Component\PropertyAccess\PropertyAccess;

class RestrictionsCheckerService
{
    /**
     * @var RestrictionsCheckerInterface[]
     */
    private $checkers;

    /** @var DataHolder */
    private $dataHolder;

    /** @var string[] */
    private $errors = [];

    public function __construct(DataHolder $dataHolder)
    {
        $this->dataHolder = $dataHolder;
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

        if (!$searchQuery->isRestrictionsWhereChecked()) {
            $restrictions = $this->dataHolder->getCheckNecessaryRestrictions($searchQuery);
            if (!empty($restrictions)) {
                foreach ($this->checkers as $checker) {
                    try {
                        $checker->check($searchQuery, $restrictions);
                    } catch (RestrictionsCheckerException $e) {
                        $this->errors[] = $e->getMessage();
                    }
                }
            }
            $searchQuery->setRestrictionsWhereChecked();
        }

        return !(bool)\count($this->errors);
    }

}