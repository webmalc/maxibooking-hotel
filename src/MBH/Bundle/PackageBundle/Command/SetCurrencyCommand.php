<?php

namespace MBH\Bundle\PackageBundle\Command;

use MBH\Bundle\ClientBundle\Document\ClientConfig;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SetCurrencyCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('mbhpackage:currency_command')
            ->setDescription('Hello PhpStorm');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dm = $this->getContainer()->get('doctrine.odm.mongodb.document_manager');
        $currency = $this->getContainer()->getParameter('locale.currency');
        /** @var ClientConfig $clientConfig */
        $clientConfig = $this->getContainer()->get('mbh.client_config_manager')->fetchConfig();
        $clientConfig->setCurrency($currency);
        $this->getContainer()->get('logger')->info($currency);
        $dm->flush();
    }
}
