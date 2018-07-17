<?php


namespace MBH\Bundle\SearchBundle\Document;


use Doctrine\ODM\MongoDB\DocumentRepository;

class SearchResultRepository extends DocumentRepository
{
    public const OK_STATUS = 'ok';

    public const ERROR_STATUS = 'error';

    public function fetchOkResultIds(string $conditionId): array
    {
        return $this->fetchResultsIds(self::OK_STATUS, $conditionId);
    }

    public function fetchErrorResultIds(string $conditionId): array
    {
        return $this->fetchResultsIds(self::ERROR_STATUS, $conditionId);

    }

    public function fetchResultsByIds(array $resultIds): array
    {
        return $this
            ->createQueryBuilder()
            ->field('_id')
            ->in($resultIds)
            ->getQuery()
            ->execute()
            ->toArray();
    }

    private function fetchResultsIds(string $status, string $conditionId): array
    {
        $qb = $this->createQueryBuilder();

        return $qb
            ->field('status')
            ->equals($status)
            ->field('queryId')
            ->equals($conditionId)
            ->distinct('_id')
            ->getQuery()
            ->execute()
            ->toArray();
    }


}