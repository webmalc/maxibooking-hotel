<?php


namespace MBH\Bundle\SearchBundle\Command;


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
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
            ->addArgument('month', InputArgument::OPTIONAL, 'The month number to warm up')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $date = new \DateTime('midnight');
        $warmer = $this->getContainer()->get('mbh_search.cache_warmer');
        $warmer->warmUp($date);
    }

}