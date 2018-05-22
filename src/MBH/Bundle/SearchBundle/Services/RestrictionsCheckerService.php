<?php


namespace MBH\Bundle\SearchBundle\Services;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\PriceBundle\Document\Restriction;
use MBH\Bundle\SearchBundle\Document\SearchConditions;
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

    /** @var string[] */
    private $errors = [];

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

    //** TODO -  */
    public function check(SearchQuery $searchQuery): bool
    {
        if ($searchQuery->isIgnoreRestrictions()) {
            return true;
        }

        if (null === $this->restrictions) {
            $this->restrictions = $this->getRestrictions();
        }


        if (!$searchQuery->isRestrictionsWhereChecked()) {
            $restrictions = $this->getNecessaryRestrictions($searchQuery);
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

    /**
     * @throws RestrictionsCheckerServiceException
     */
    private function getRestrictions(): array
    {
        if (!$this->conditions) {
            throw new RestrictionsCheckerServiceException('There is no conditions in checker service!');
        }

        $restrictions = $this->dm->getRepository(Restriction::class)->getWithConditions($this->conditions);
        $result = [];
        foreach ($restrictions as $restriction) {
            $key = $this->generateRestrictionKey(
                $restriction['date'],
                $restriction['tariff'],
                $restriction['roomType']
            );
            $result[$key] = $restriction;
        }

        return $result;

    }

    /**
     * @param SearchQuery $query
     * @return array
     */
    private function getNecessaryRestrictions(SearchQuery $query): array
    {
        $restrictions = [];
        $accessor = PropertyAccess::createPropertyAccessor();
        $tariffId = $query->getTariffId();
        $roomTypeId = $query->getRoomTypeId();
        $restrictionBegin = $query->getBegin();
        $restrictionEnd = (clone $query->getEnd())->modify('+ 1 day');
        foreach (new \DatePeriod($restrictionBegin, \DateInterval::createFromDateString('1 day'), $restrictionEnd) as $day) {
            $key = $this->getAccessRestrictionKey($day, $tariffId, $roomTypeId);
            if (null !== $restriction = $accessor->getValue($this->restrictions, $key)) {
                $restrictions[] = $restriction;
            }
        }

        return $restrictions;

    }

    private function generateRestrictionKey($date, array $tariff, array $roomType): string
    {
        if ($date instanceof \MongoDate) {
            return "{$date->toDateTime()->format('d-m-Y')}_{$tariff['$id']}_{$roomType['$id']}";
        }
        /** @var \DateTime $date */
        return "{$date->format('d-m-Y')}_{$tariff['$id']}_{$roomType['$id']}";
    }

    private function getAccessRestrictionKey(\DateTime $date, string $tariffId, string $roomTypeId)
    {
        return "[{$date->format('d-m-Y')}_{$tariffId}_{$roomTypeId}]";
    }

    public function setConditions(SearchConditions $conditions): RestrictionsCheckerService
    {
        $this->conditions = $conditions;

        return $this;
    }


}