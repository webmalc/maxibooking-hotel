<?php


namespace MBH\Bundle\SearchBundle\Command;


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CacheWarmUpCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('mbh:search:cache:warmup')
            ->setDescription('Warm the search cache up.')
            ->addArgument('month', InputArgument::OPTIONAL, 'The month number to warm up')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $monthNum = (int)$input->getArgument('month');

        $output->writeln('Start cache month '.$monthNum);

        $date = new \DateTime('midnight');
        $warmer = $this->getContainer()->get('mbh_search.cache_warmer');

        $progress = new ProgressBar($output);
        $externalProgress = function ($expected, $count) use ($progress, &$started, $output) {
            if (true === $started) {
                $progress->setProgress($count);
            } else {
                $started = true;
                $progress->start($expected);
            }
            $output->writeln("\n");
        };

        $iProgress = new ProgressBar($output);
        $iProgress->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%');
        $internalProgress = function ($expected, $count, $reset = false) use ($iProgress, &$iStarted) {
            if (true === $iStarted) {
                $iProgress->setProgress($count);
            } else {
                $iStarted = true;
                $iProgress->start($expected);
            }

            if (true === $reset) {
                $iProgress->clear();
            }
        };

        $warmer->warmUp($date, $externalProgress, $internalProgress);
        $progress->finish();
        $iProgress->finish();
    }

}