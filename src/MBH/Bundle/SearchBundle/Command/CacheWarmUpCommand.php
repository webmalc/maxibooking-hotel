<?php


namespace MBH\Bundle\SearchBundle\Command;


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CacheWarmUpCommand
 * @package MBH\Bundle\SearchBundle\Command
 */
class CacheWarmUpCommand extends ContainerAwareCommand
{
    /**
     *
     */
    protected function configure(): void
    {
        $this
            ->setName('mbh:search:cache:warmup')
            ->setDescription('Warm the search cache up.')
            ->addOption('begin', null, InputOption::VALUE_REQUIRED, 'WarmUpBegin in format dd-mm-yy')
            ->addOption('end', null, InputOption::VALUE_REQUIRED, 'WarmUpBegin in format dd-mm-yy')
            ->addOption('flush', null, InputOption::VALUE_NONE, 'flush cache')
        ;

    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\SearchConditionException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\SearchQueryGeneratorException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\SearchResultComposerException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\SharedFetcherException
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $begin = $input->getOption('begin');
        $end = $input->getOption('end');
        $flush = $input->getOption('flush');
        try {
            $begin = new \DateTime($begin);
            $end = new \DateTime($end);
        } catch (\Exception $e) {
            $output->writeln('Can not parse begin - end');

            return;
        }

        $searchCache = $this->getContainer()->get('mbh_search.search_cache_invalidator');
        if ($flush) {
            $searchCache->flushCache();
        }
        $warmer = $this->getContainer()->get('mbh_search.cache_warmer');
        $output->writeln('Start warmUp for '.$begin->format('d.m.Y').' - '. $end->format('d.m.Y'));
        $warmer->warmUp($begin, $end);
    }

}