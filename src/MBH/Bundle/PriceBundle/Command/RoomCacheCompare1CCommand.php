<?php

namespace MBH\Bundle\PriceBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;


class RoomCacheCompare1CCommand extends ContainerAwareCommand
{
    const FILE_PATH = '/../protectedUpload/1C/';

    protected function configure()
    {
        $this
            ->setName('azovsky:cache:compare')
            ->setDescription('Compare 1C room cache')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $start = new \DateTime();
        $notifier = $this->getContainer()->get('mbh.notifier.mailer');

        $message = $notifier::createMessage();
        $message
            ->setText('hide')
            ->setFrom('report')
            ->setSubject('mailer.comapre.1c.subject')
            ->setType('info')
            ->setCategory('report')
            ->setTemplate('MBHBaseBundle:Mailer:compare1C.html.twig')
            ->setAutohide(false)
            ->setEnd(new \DateTime('+1 minute'))
        ;
        $notifier
            ->setMessage($message)
            ->notify()
        ;

        $time = $start->diff(new \DateTime());
        $output->writeln(
            sprintf('Compare complete. Elapsed time: %s', $time->format('%H:%I:%S'))
        );
    }

}