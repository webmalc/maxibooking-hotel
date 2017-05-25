<?php

namespace MBH\Bundle\PackageBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class LimitedVirtualRoomMovingCommand
 * @package MBH\Bundle\PackageBundle\Command
 */
class LimitedVirtualRoomMovingCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('mbh:limited_virtual_room_moving_command')
            ->setDescription('Move limited count of virtual rooms')
            ->addOption('begin', null, InputOption::VALUE_REQUIRED, 'Begin (date - d.m.Y)')
            ->addOption('end', null, InputOption::VALUE_REQUIRED, 'End (date - d.m.Y)')
            ->addOption('limit', null, InputOption::VALUE_REQUIRED)
            ->addOption('isLastPart', null, InputOption::VALUE_OPTIONAL)
            ->addOption('offset', null, InputOption::VALUE_REQUIRED);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getContainer()->get('mbh.helper');
        $limit = $input->getOption('limit');
        $offset = $input->getOption('offset');
        $isLastPart = $input->getOption('isLastPart') == 1;
        $beginDate = $helper->getDateFromString($input->getOption('begin'));
        $endDate = $helper->getDateFromString($input->getOption('end'));

        $virtualRoomHandler = $this->getContainer()->get('mbh.package.virtual_room_handler');
        $movedPackagesData = $virtualRoomHandler->setVirtualRooms($beginDate, $endDate, $limit, $offset);
        $rightEdge = $offset + $limit;
        $this->getContainer()->get('mbh.virtual_room_handler.logger')->alert("Packages between $offset and $rightEdge handled");
        if (count($movedPackagesData) || $isLastPart) {
            $this->sendMessage($movedPackagesData);
        }
    }

    /**
     * @param $movedPackagesData
     */
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
