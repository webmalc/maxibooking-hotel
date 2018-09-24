<?php


namespace MBH\Bundle\SearchBundle\Command;


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
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
        $now = new \DateTime();
        $currentYear = $now->format('Y');
        $this
            ->setName('mbh:search:cache:warmup')
            ->setDescription('Warm the search cache up.')
            ->addOption('include', null, InputOption::VALUE_OPTIONAL, 'which month cache warmUp comma separated.')
            ->addOption(
                'exclude',
                null,
                InputOption::VALUE_OPTIONAL,
                'which month exclude cache warmUp comma separated.'
            )
            ->addOption('year', null, InputOption::VALUE_OPTIONAL, 'year of warmup', $currentYear);
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
        $include = explode(',', trim($input->getOption('include')));
        $include = array_map('intval', array_unique(array_filter(array_map('trim', $include))));
        $exclude = explode(',', trim($input->getOption('exclude')));
        $exclude = array_map('intval', array_unique(array_filter(array_map('trim', $exclude))));
        $year = (int)$input->getOption('year');
        if (!$include) {
            $include = range(1, 12);
        }

        $cacheMonth = array_diff($include, $exclude);

        $dates = [];
        foreach ($cacheMonth as $month) {
            $dates[] = new \DateTime("1-${month}-${year}");
        }

        $now = new \DateTime();
        $dates = array_filter(
            $dates,
            function ($date) use ($now) {
                /** @var \DateTime $date */
                return $date > $now ||
                    (($date->format('Y') === $now->format('Y')) && ($date->format('m') === $now->format('m')));
            }
        );

        $searchCache = $this->getContainer()->get('mbh_search.cache_search');
        $searchCache->flushCache();

        $warmer = $this->getContainer()->get('mbh_search.cache_warmer');
        foreach ($dates as $date) {
            $output->writeln('Start warmUp for '.$date->format('m.Y'));
            $warmer->warmUp($date);
        }
    }

}