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
    /**
     * @var/ \Doctrine\Bundle\MongoDBBundle\ManagerRegistry
     */
    private $dm;

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

        $this->dm = $this->getContainer()->get('doctrine_mongodb')->getManager();
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

        $countries = $this->getCountries($filesPaths['country']);

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
            ];
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
                'title' => $row[3],
            ];
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
        $this->dm->createQueryBuilder('MBHHotelBundle:Country')->remove()->getQuery()->execute();
        $this->dm->createQueryBuilder('MBHHotelBundle:Region')->remove()->getQuery()->execute();
        $this->dm->createQueryBuilder('MBHHotelBundle:City')->remove()->getQuery()->execute();

        foreach ($combinedArray as $countryInfo) {
            $country = new Country();
            $country->setTitle($countryInfo['title']);
            $country->setTranslatableLocale('ru_RU');

            $this->dm->persist($country);

            foreach ($countryInfo['regions'] as $regionInfo) {
                $region = new Region();
                $region->setCountry($country)->setTitle($regionInfo['title']);

                $this->dm->persist($region);

                foreach ($regionInfo['cities'] as $cityTitle) {
                    $city = new City();
                    $city->setCountry($country)->setRegion($region)->setTitle($cityTitle);

                    $this->dm->persist($city);
                }
            }
        }

        $this->dm->flush();

        $translations = $this->getTranslations($filesPaths['country']);
        $this->translate($translations);


        $time = $start->diff(new \DateTime());
        $output->writeln('Check & generation complete. Total entries: ' . $total . '. Elapsed time: ' . $time->format('%H:%I:%S'));
    }

    /**
     * Get countries array
     * @param $filePath
     * @return array
     */
    private function getCountries($filePath)
    {
        $countries = [];
        $countryFile = fopen($filePath, 'r');
        while (($row = fgetcsv($countryFile, 1000, ';')) !== false ) {
            if(!is_numeric($row[0])) {
                continue;
            }
            $countries[$row[0]] = $row[2];
        }

        return $countries;
    }

    private function getTranslations($filePath)
    {
        $translations = [];
        $countryFile = fopen($filePath, 'r');
        while (($row = fgetcsv($countryFile, 1000, ';')) !== false ) {
            if(!is_numeric($row[0])) {
                continue;
            }
            $translations[$row[2]] = $row[3];
        }

        return $translations;
    }

    /**
     * @param array $translations
     */
    private function translate(array $translations)
    {
        foreach($this->dm->getRepository('MBHHotelBundle:Country')->findAll() as $country){
            $translate = $translations[$country->getTitle('sdfsdf')];
            if($translate) {
                $country->setTranslatableLocale('en_EN');
                $country->setTitle($translate);
                $this->dm->persist($country);
            }
        }
        $this->dm->flush();
    }
}