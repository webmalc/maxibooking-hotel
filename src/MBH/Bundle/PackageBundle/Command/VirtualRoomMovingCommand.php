<?php

namespace MBH\Bundle\PackageBundle\Command;

use MBH\Bundle\BaseBundle\Service\Helper;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class VirtualRoomMovingCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('mbh:virtual_rooms:move')
            ->setDescription('Move virtual room')
            ->addOption('begin', null, InputOption::VALUE_OPTIONAL, 'Begin (date - d.m.Y)')
            ->addOption('end', null, InputOption::VALUE_OPTIONAL, 'End (date - d.m.Y)');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $beginDate = Helper::getDateFromString($input->getOption('begin')) ?? new \DateTime('midnight');
        $monthCount = $this->getContainer()->getParameter('packaging.month_count');
        $endDate = Helper::getDateFromString($input->getOption('end')) ?? new \DateTime('+' . $monthCount . ' month');

        $virtualRoomHandler = $this->getContainer()->get('mbh.package.virtual_room_handler');
        $movedPackagesData = $virtualRoomHandler->setVirtualRooms($beginDate, $endDate);
        $output->writeln('Completed');

        $this->sendMessage($movedPackagesData);
    }

    private function sendMessage($movedPackagesData)
    {
        $container = $this->getContainer();
        $notifier = $container->get('mbh.notifier.mailer');

        $message = $notifier::createMessage();
        $message
            ->setText('mailer.packaging.text')
            ->setFrom('system')
            ->setSubject('mailer.packaging.subject')
            ->setType('info')
            ->setCategory('notification')
            ->setTemplate('MBHBaseBundle:Mailer:packaging.html.twig')
            ->setAdditionalData([
                'movedPackagesDataArray' => $movedPackagesData,
            ])
            ->setAutohide(false)
            ->setEnd(new \DateTime('+1 minute'));

        $notifier
            ->setMessage($message)
            ->notify();
    }
}
