<?php


namespace MBH\Bundle\SearchBundle\Services;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\SearchBundle\Document\SearchConditions;
use MBH\Bundle\SearchBundle\Document\SearchResultHolder;
use MBH\Bundle\SearchBundle\Document\SearchResultHolderRepository;
use MBH\Bundle\SearchBundle\Document\SearchResultRepository;

class AsyncResultReceiver
{

    /** @var SearchResultRepository */
    private $searchResultRepository;

    /** @var SearchResultHolderRepository */
    private $searchResultHolderRepository;

    /** @var DocumentManager */
    private $dm;

    /**
     * AsyncResultReceiver constructor.
     * @param SearchResultRepository $searchResultRepository
     * @param SearchResultHolderRepository $searchResultHolder
     */
    public function __construct(SearchResultRepository $searchResultRepository, SearchResultHolderRepository $searchResultHolder)
    {
        $this->searchResultRepository = $searchResultRepository;
        $this->searchResultHolderRepository = $searchResultHolder;
        $this->dm = $searchResultRepository->getDocumentManager();
    }


    public function receive(SearchConditions $conditions): array
    {
        $result = [];
        $searchHolder = $this->searchResultHolderRepository->findOneBy(['conditionsId' => $conditions->getId()]);
        if (!$searchHolder) {
            $searchHolder = new SearchResultHolder();
            $searchHolder->setSearchConditionsId($conditions->getId());
        }

        /** @var SearchResultRepository $searchResultRepo */
        $resultIds = $this->searchResultRepository
            ->createQueryBuilder()
            ->field('status')
            ->equals('ok')
            ->field('queryId')
            ->equals($conditions->getId())
            ->distinct('_id')
            ->getQuery()
            ->execute()
            ->toArray()
        ;

        $noResultIds = $this->searchResultRepository
            ->createQueryBuilder()
            ->field('status')
            ->equals('error')
            ->field('queryId')
            ->equals($conditions->getId())
            ->distinct('_id')
            ->getQuery()
            ->execute()
            ->toArray()
        ;

        $alreadyTaken = $searchHolder->getTakenSearchResultIds();



        $takenResults = array_unique(array_merge($resultIds, $noResultIds));

        $searchHolder->addTakenResultIds($takenResults);
        $dm = $this->dm;
        $dm->persist($searchHolder);
        $dm->flush($searchHolder);

//        $resIds = array_diff($searchHolder->getTakenSearchResultIds(), $resultIds);
//        $results = $dm->getRepository(SearchResult::class)->createQueryBuilder()->field('_id')->in($resIds)->getQuery()->execute()->toArray();

        /** @var QueryBuilder $qb */
        $a = 'b';
        return $result;
    }

    private function getResultsOk(string $conditionsId)
    {

    }

    private function
}