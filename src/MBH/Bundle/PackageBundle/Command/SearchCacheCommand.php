<?php

namespace MBH\Bundle\PackageBundle\Command;

use MBH\Bundle\PackageBundle\Document\SearchQuery;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Process\Process;

class SearchCacheCommand extends ContainerAwareCommand
{

    const CHUNK_MAX_DAYS = 31;
    const TITLE = 'mbh:cache:warm-search';

    /**
     * @var array
     */
    private $params;

    /**
     * @var ContainerInterface
     */
    private $container;

    protected function configure()
    {
        $this
            ->setName(self::TITLE)
            ->setDescription('Warm search cache')
            ->addOption('begin', null, InputOption::VALUE_OPTIONAL, 'From (date - d.m.Y)')
            ->addOption('end', null, InputOption::VALUE_OPTIONAL, 'To (date - d.m.Y)')
            ->addOption('force', null, InputOption::VALUE_NONE)
        ;
    }

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     */
    private function cacheCreate(\DateTime $begin, \DateTime $end)
    {
        $search = $this->container->get('mbh.package.search')
            ->setWithTariffs();

        $query = new SearchQuery();
        $query->begin = $begin;
        $query->end = $end;
        $query->adults = 0;
        $query->children = 0;
        $query->memcached = true;
        $query->isOnline = true;

        $search->search($query);
    }


    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $start = new \DateTime();
        $this->container = $this->getContainer();
        $helper = $this->container->get('mbh.helper');
        $logger = $this->container->get('mbh.cache.logger');

        $this->params = $this->container->getParameter('mbh_cache')['search'];

        $from = $helper->getDateFromString($input->getOption('begin')) ?? new \DateTime('midnight');
        $to = $helper->getDateFromString($input->getOption('end')) ?? new \DateTime('midnight +' . $this->params['months'] . ' months');

        if ($input->getOption('force')) {
            $this->cacheCreate($from, $to);
            return;
        }

        $dm = $this->container->get('doctrine_mongodb')->getManager();
        $restrictions = $dates = $caches = [];


        foreach ($dm->getRepository('MBHPriceBundle:RoomCache')->fetch($from, $to) as $roomCache) {
            $caches[$roomCache->getDate()->format('d.m.Y')] = true;
        }

        foreach ($dm->getRepository('MBHPriceBundle:Restriction')->fetch($from, $to) as $restriction) {
            $date = $restriction->getDate()->format('d.m.Y');
            if (!isset($restrictions[$date])) {
                $restrictions[$date] = [
                    'getClosedOnArrival' => $restriction->getClosedOnArrival(),
                    'getClosedOnDeparture' => $restriction->getClosedOnDeparture(),
                    'closed' => $restriction->getClosed(),
                    'max' => (int) $restriction->getMaxStay() ?? (int) $restriction->getMaxStayArrival(),
                    'min' => (int) $restriction->getMinStay() ?? (int) $restriction->getMinStayArrival(),
                ];
            } else {
                if (!$restriction->getClosedOnArrival()) {
                    $restrictions[$date]['getClosedOnArrival'] = false;
                }
                if (!$restriction->getClosedOnDeparture()) {
                    $restrictions[$date]['getClosedOnDeparture'] = false;
                }
                if (!$restriction->getClosed()) {
                    $restrictions[$date]['closed'] = false;
                }
                if ($restrictions[$date]['max'] >  (int) $restriction->getMaxStay() ?? (int) $restriction->getMaxStayArrival()) {
                    $restrictions[$date]['max'] = (int) $restriction->getMaxStay() ?? (int) $restriction->getMaxStayArrival();
                }
                if ($restrictions[$date]['min'] >  (int) $restriction->getMinStay() ?? (int) $restriction->getMinStayArrival()) {
                    $restrictions[$date]['min'] = (int) $restriction->getMinStay() ?? (int) $restriction->getMinStayArrival();
                }
            }
        }

        foreach (new \DatePeriod($from, \DateInterval::createFromDateString('1 day'), $to) as $begin) {
            foreach (new \DatePeriod($from, \DateInterval::createFromDateString('1 day'), $to) as $end) {
                $beginStr = $begin->format('d.m.Y');
                $endStr = $end->format('d.m.Y');
                $duration = $end->diff($begin)->format("%a");

                if (!isset($caches[$beginStr]) || !isset($caches[$endStr])) {
                    continue;
                }

                if ($duration > $this->params['max_duration'] || $duration < $this->params['min_duration'] || $begin >= $end) {
                    continue;
                }
                if (isset($restrictions[$beginStr]) && $restrictions[$beginStr]['getClosedOnArrival']) {
                    continue;
                }
                if (isset($restrictions[$beginStr]) && $restrictions[$beginStr]['closed']) {
                    continue;
                }
                if (isset($restrictions[$beginStr]) && $restrictions[$beginStr]['max'] && $duration > $restrictions[$beginStr]['max']) {
                    continue;
                }
                if (isset($restrictions[$beginStr]) && $restrictions[$beginStr]['min'] && $duration < $restrictions[$beginStr]['min']) {
                    continue;
                }
                if (isset($restrictions[$endStr]) && $restrictions[$endStr]['getClosedOnDeparture']) {
                    continue;
                }
                if (isset($restrictions[$endStr]) && $restrictions[$endStr]['closed']) {
                    continue;
                }
                if (isset($restrictions[$endStr]) && $restrictions[$endStr]['max'] && $duration > $restrictions[$endStr]['max']) {
                    continue;
                }
                if (isset($restrictions[$endStr]) && $restrictions[$endStr]['min'] && $duration < $restrictions[$endStr]['min']) {
                    continue;
                }

                $dates[$begin->format('d.m.Y') . '-' . $end->format('d.m.Y')] = [$begin, $end];
            }
        }

        $output->writeln('Dates count: ' . count($dates));
        $num = 0;

        $console = $this->container->get('kernel')->getRootDir() . '/../bin/console ';
        foreach ($dates as $key => $pair) {
            $num++;
            $startSearch = new \DateTime();
            $output->writeln(sprintf('Start search #%d-%d [%s - %s]', $num, count($dates), $pair[0]->format('d.m.Y'), $pair[1]->format('d.m.Y')));

            //run command
            $command = 'nohup php ' . $console . self::TITLE .' --begin='. $pair[0]->format('d.m.Y') .' --end='. $pair[1]->format('d.m.Y') .' --force --env=prod';
            $process = new Process($command);
            $process->setTimeout(null)->setIdleTimeout(null)->run();
            $timeSearch = $startSearch->diff(new \DateTime());
            $logger->info('SEARCH: '. $command);

            $output->writeln(sprintf('Time elapsed: %s', $timeSearch->format('%H:%I:%S')));
        }

        $time = $start->diff(new \DateTime());
        $output->writeln('Complete. Elapsed time: ' . $time->format('%H:%I:%S'));
    }
}
