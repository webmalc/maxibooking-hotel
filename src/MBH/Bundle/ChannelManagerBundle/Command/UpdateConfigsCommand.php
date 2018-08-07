<?php

namespace MBH\Bundle\ChannelManagerBundle\Command;

use MBH\Bundle\ChannelManagerBundle\Document\BookingConfig;
use MBH\Bundle\ChannelManagerBundle\Document\ExpediaConfig;
use MBH\Bundle\ChannelManagerBundle\Document\HundredOneHotelsConfig;
use MBH\Bundle\ChannelManagerBundle\Document\MyallocatorConfig;
use MBH\Bundle\ChannelManagerBundle\Document\OstrovokConfig;
use MBH\Bundle\ChannelManagerBundle\Document\VashotelConfig;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateConfigsCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('mbh:update_configs_command')
            ->setDescription('Update channel manager configs with new fields');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dm = $this->getContainer()->get('doctrine.odm.mongodb.document_manager');

        $channelManagerConfigs = [
            BookingConfig::class,
            HundredOneHotelsConfig::class,
            OstrovokConfig::class,
            VashotelConfig::class,
            MyallocatorConfig::class,
            ExpediaConfig::class
        ];

        foreach ($channelManagerConfigs as $configType) {
            $configs = $dm->getRepository($configType)->findAll();
            /** @var ChannelManagerConfigInterface $config */
            foreach ($configs as $config) {
                $config->setIsConfirmedWithDataWarnings(true);
                if (method_exists($config, 'setIsConnectionSettingsRead')) {
                    $config->setIsConnectionSettingsRead(true);
                }
                if (method_exists($config, 'setIsAllPackagesPulled')) {
                    $config->setIsAllPackagesPulled(true);
                }
            }
        }

        $dm->flush();
    }
}
