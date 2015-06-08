<?php

namespace MBH\Bundle\HotelBundle\Command;

use MBH\Bundle\HotelBundle\Document\City;
use MBH\Bundle\HotelBundle\Document\Country;
use MBH\Bundle\HotelBundle\Document\Region;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CitiesLoadCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('mbh:city:load')
            ->setDescription('Loading countries, regions & cities in the database')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $start = new \DateTime();
        $total = 0;
        
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->getContainer()->get('doctrine_mongodb')->getManager();
        $basePath = $this->getContainer()->get('kernel')->getRootDir() . '/../src/MBH/Bundle/HotelBundle/Resources/csv/';

        $filesPaths = [
            'country' => $basePath . 'country.csv',
            'region' => $basePath . 'region.csv',
            'city' => $basePath . 'city.csv',
        ];

        foreach ($filesPaths as $path) {
            if (!file_exists($path) || !is_readable($path)) {
                $output->writeln('<error>Error. New data not loaded. File ' . $path . ' not exist!!!</error>');
                return false;
            }
        }

        // get countries array
        $countryFile = fopen($filesPaths['country'], 'r');
        $countries = [];
        while (($row = fgetcsv($countryFile, 1000, ';')) !== false ) {
            if(!is_numeric($row[0])) {
                continue;
            }
            $countries[$row[0]] = $row[2];
        }

        // get regions array
        $regionFile = fopen($filesPaths['region'], 'r');
        $regions = [];
        while (($row = fgetcsv($regionFile, 1000, ';')) !== false ) {
            if(!is_numeric($row[0])) {
                continue;
            }
            $regions[$row[0]] = [
                'countryId' => $row[1],
                'title' => $row[3]
            ] ;
        }

        // get cities array
        $cityFile = fopen($filesPaths['city'], 'r');
        $cities = [];
        while (($row = fgetcsv($cityFile, 1000, ';')) !== false ) {
            if(!is_numeric($row[0])) {
                continue;
            }
            $cities[$row[0]] = [
                'countryId' => $row[1],
                'regionId' => $row[2],
                'title' => $row[3]
            ] ;
        }

        //combine arrays
        $combinedArray = [];
        foreach ($countries as $countryId => $country) {
            $combinedArray[$countryId] = [
                'title' => $country,
                'regions' => []
            ];
            foreach ($regions as $regionId => $region) {
                if ($region['countryId'] == $countryId) {
                    $combinedArray[$countryId]['regions'][$regionId] = [
                        'title' => $region['title'],
                        'cities' => []
                    ];

                    foreach ($cities as $cityId => $city) {
                        if ($city['regionId'] == $regionId) {
                            $combinedArray[$countryId]['regions'][$regionId]['cities'][$cityId] = $city['title'];
                        }
                    }
                }
            }
        }

        // clear old entries in database
        $dm->createQueryBuilder('MBHHotelBundle:Country')->remove()->getQuery()->execute();
        $dm->createQueryBuilder('MBHHotelBundle:Region')->remove()->getQuery()->execute();
        $dm->createQueryBuilder('MBHHotelBundle:City')->remove()->getQuery()->execute();

        foreach ($combinedArray as $countryInfo) {
            $country = new Country();
            $country->setTitle($countryInfo['title']);

            $dm->persist($country);

            foreach ($countryInfo['regions'] as $regionInfo) {
                $region = new Region();
                $region->setCountry($country)->setTitle($regionInfo['title']);

                $dm->persist($region);

                foreach ($regionInfo['cities'] as $cityTitle) {
                    $city = new City();
                    $city->setCountry($country)->setRegion($region)->setTitle($cityTitle);

                    $dm->persist($city);
                }
            }
        }
        $dm->flush();

        $time = $start->diff(new \DateTime());
        $output->writeln('Check & generation complete. Total entries: ' . $total . '. Elapsed time: ' . $time->format('%H:%I:%S'));
    }
}