<?php

namespace MBH\Bundle\HotelBundle\Command;


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CitiesTranslateCommand extends ContainerAwareCommand
{
    /**
     * @var \Doctrine\Bundle\MongoDBBundle\ManagerRegistry
     */
    private $dm;

    protected function configure()
    {
        $this
            ->setName('mbh:city:load')
            ->setDescription('Translate cities in the csv')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $basePath = $this->getContainer()->get('kernel')->getRootDir() . '/../src/MBH/Bundle/HotelBundle/Resources/csv/';

        $filesPaths = [
            'country' => $basePath . 'country.csv',
            'region' => $basePath . 'region.csv',
            'city' => $basePath . 'city.csv',
        ];

        $filesPaths['region']
    }

}