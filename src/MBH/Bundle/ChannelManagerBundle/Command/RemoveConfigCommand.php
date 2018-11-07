<?php

namespace MBH\Bundle\ChannelManagerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RemoveConfigCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('mbhchannel_manager:remove_config_command')
            ->addArgument('service', InputArgument::REQUIRED, 'Service name')
            ->addArgument('hotelId', InputArgument::REQUIRED, 'Hotel id')
            ->setDescription('Remove channel manager config');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dm = $this->getContainer()->get('doctrine.odm.mongodb.document_manager');

        $serviceName = $input->getArgument('service');
        $this->getContainer()
            ->get('mbh.channelmanager')
            ->checkForCMExistence($serviceName, true);

        $hotelId = $input->getArgument('hotelId');
        $hotel = $dm->find('MBHHotelBundle:Hotel', $hotelId);
        if (is_null($hotel)) {
            throw new \InvalidArgumentException('There is no hotel with ID=' . $hotelId);
        }

        $config = $this->getContainer()
            ->get('mbh.channelmanager')
            ->getConfigForHotel($hotel, $serviceName);
        if (is_null($config)) {
            throw new \InvalidArgumentException('There is no ' . $serviceName . ' config for hotel with ID=' . $hotelId);
        }

        $dm->remove($config);
        $dm->flush();
    }
}