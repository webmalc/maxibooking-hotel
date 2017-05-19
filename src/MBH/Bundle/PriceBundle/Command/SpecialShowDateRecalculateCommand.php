<?php


namespace MBH\Bundle\PriceBundle\Command;


use MBH\Bundle\PriceBundle\Document\Special;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SpecialShowDateRecalculateCommand extends ContainerAwareCommand
{

    const DAY_PERIOD = 7;

    protected function configure()
    {
        $this
            ->setName('mbh:special:show:date:recalculate')
            ->setDescription('Recalculate shown dates in Special');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $start = new \DateTime();

        $dm = $this->getContainer()->get('doctrine_mongodb.odm.document_manager');
        $specials = $dm->getRepository('MBHPriceBundle:Special')->findAll();
        foreach ($specials as $special) {
            /** @var Special $special */
            $begin = $special->getBegin();
            $end = $special->getEnd();
            $displayBegin = (clone($begin))->modify('- '.self::DAY_PERIOD.' days');
            $displayEnd = (clone($end))->modify('+ '.self::DAY_PERIOD.' days');
            $special->setDisplayFrom($displayBegin);
            $special->setDisplayTo($displayEnd);
            $output->writeln('Обратабывается '.$special->getName());
        }

        $dm->flush();

        $time = $start->diff(new \DateTime());
        $output->writeln(
            sprintf('Recalculate complete. Elapsed time: %s', $time->format('%H:%I:%S'))
        );
    }


}