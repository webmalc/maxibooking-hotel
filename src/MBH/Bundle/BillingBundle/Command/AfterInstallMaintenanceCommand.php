<?php


namespace MBH\Bundle\BillingBundle\Command;


use MBH\Bundle\BillingBundle\Lib\Exceptions\AfterInstallException;
use MBH\Bundle\BillingBundle\Lib\Model\Client;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AfterInstallMaintenanceCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('mbh:client:after:install')
            ->setDescription('Do after install maintenance client')
            ->addArgument('client', InputArgument::REQUIRED, 'User name');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $cache = $this->getContainer()->get('cache.app');
        $clientName = $input->getArgument('client');
        $key = Client::CACHE_PREFIX.$clientName;
        if ($cache->hasItem($key)) {
            /** @var Client $client */
            $client = $cache->getItem($key)->get();
            $dm = $this->getContainer()->get('doctrine_mongodb.odm.default_document_manager');
            $hotels = $dm->getRepository('MBHHotelBundle:Hotel')->findAll();
            $dm->remove($document);
            foreach ($client->getProperties() as $property) {
                
            }

        } else {
            throw new AfterInstallException('No client in cache, error');
        }

    }


}