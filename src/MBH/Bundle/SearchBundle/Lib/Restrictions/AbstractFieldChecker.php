<?php


namespace MBH\Bundle\SearchBundle\Lib\Restrictions;


use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\SearchBundle\Lib\Exceptions\RestrictionsCheckerException;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use Symfony\Component\PropertyAccess\PropertyAccess;

abstract class AbstractFieldChecker implements RestrictionsCheckerInterface
{

    public function check(SearchQuery $searchQuery, array $restrictions): void
    {
        $accessor = PropertyAccess::createPropertyAccessor();
        $key = "[{$this->getCheckingFieldName()}]";
        foreach ($restrictions as $restriction) {
            $date = Helper::convertMongoDateToDate($restriction['date']);
            if (null !== $value = $accessor->getValue($restriction, $key)) {
                $this->doCheck($date, $value, $searchQuery);
            }
        }

    }

    /** @throws RestrictionsCheckerException */
    abstract protected function doCheck(\DateTime $date, $value, SearchQuery $searchQuery): void;

    abstract protected function getCheckingFieldName(): string;



}