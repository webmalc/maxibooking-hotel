<?php


namespace MBH\Bundle\SearchBundle\Services\Cache;


use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\PriceBundle\Document\TariffRepository;
use MBH\Bundle\SearchBundle\Services\GuestCombinator;
use MBH\Bundle\SearchBundle\Services\Search\CacheSearcher;
use MBH\Bundle\SearchBundle\Services\Search\WarmUpSearcher;
use MBH\Bundle\SearchBundle\Services\SearchConditionsCreator;
use MBH\Bundle\SearchBundle\Services\SearchQueryGenerator;
use Psr\Log\LoggerInterface;

class CacheWarmer
{
    /** @var SearchQueryGenerator */
    private $queryGenerator;

    /** @var LoggerInterface */
    private $logger;
    /**
     * @var SearchConditionsCreator
     */
    private $conditionCreator;
    /**
     * @var CacheSearcher
     */
    private $warmUpSearcher;
    /**
     * @var GuestCombinator
     */
    private $combinator;
    /**
     * @var TariffRepository
     */
    private $tariffRepository;

    /**
     * CacheWarmer constructor.
     * @param SearchConditionsCreator $creator
     * @param SearchQueryGenerator $queryGenerator
     * @param WarmUpSearcher $searcher
     * @param GuestCombinator $combinator
     * @param TariffRepository $tariffRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        SearchConditionsCreator $creator,
        SearchQueryGenerator $queryGenerator,
        WarmUpSearcher $searcher,
        GuestCombinator $combinator,
        TariffRepository $tariffRepository,
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
        $this->conditionCreator = $creator;
        $this->queryGenerator = $queryGenerator;
        $this->warmUpSearcher = $searcher;
        $this->combinator = $combinator;
        $this->tariffRepository = $tariffRepository;
    }


    /**
     * @param \DateTime|null $date
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\SearchConditionException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\SearchQueryGeneratorException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\SearchResultComposerException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\SharedFetcherException
     */
    public function warmUp(\DateTime $date = null): void
    {

        $this->logger->info('Start cache warmUp');
        if (null === $date) {
            $date = new \DateTime('midnight');
        }

        $month = (int)$date->format('m');
        $year = (int)$date->format('Y');
        $begin = new \DateTime("8-${month}-${year} midnight");
        $end = new \DateTime("22-${month}-${year} midnight");

        $sharedConditions = [
            'begin' => '24.09.2018'/*$begin->format('d.m.Y')*/,
            'end' => '01.10.2018'/*$end->format('d.m.Y')*/,
            'additionalBegin' => 1/*9*/,
            'additionalEnd' => 1/*10*/,
            'isUseCache' => true,
            'isThisWarmUp' => true
        ];

        $dm = $this->tariffRepository->getDocumentManager();
        $hotelIds = $dm->getRepository(Hotel::class)->getSearchActiveIds();
        $tariffs = $this->tariffRepository->getTariffsByHotelsIds($hotelIds);
        $combinationTypes = $this->combinator->getCombinations($tariffs);
        foreach ($combinationTypes as $combinationType) {
            $guestCombinations = $combinationType->getCombinations();
            foreach ($guestCombinations as $combination) {
                $conditionsData = array_merge($sharedConditions, $combination, ['tariffs' => $combinationType->getTariffIds()]);
                $conditions = $this->conditionCreator->createSearchConditions($conditionsData);
                $conditions->setId('warmerConditions');
                $queries = $this->queryGenerator->generate($conditions, false);
                $queryChunks = array_chunk($queries, 100);
                foreach ($queryChunks as $chunk) {
                    $this->warmUpSearcher->search($chunk);
                }
            }

        }

    }


}