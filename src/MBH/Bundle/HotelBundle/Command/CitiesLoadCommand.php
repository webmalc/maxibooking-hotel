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
     * @var \Doctrine\Bundle\MongoDBBundle\ManagerRegistry
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

        $countries = [];
        $countryFile = fopen($filesPaths['country'], 'r');
        while (($row = fgetcsv($countryFile, 1000, ';')) !== false ) {
            if(!is_numeric($row[0])) {
                continue;
            }
            $countries[$row[0]] =[
                'title_ru' => $row[2],
                'title_en' => $row[3],
            ];
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
                'title_ru' => $country['title_ru'],
                'title_en' => $country['title_en'],
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

        /** @var \Gedmo\Translatable\Document\Repository\TranslationRepository $translationRepository */
        $translationRepository = $this->dm->getRepository('Gedmo\Translatable\Document\Translation');

        // clear old entries in database
        $translationRepository->createQueryBuilder()->remove()->getQuery()->execute();
        $this->dm->createQueryBuilder('MBHHotelBundle:Country')->remove()->getQuery()->execute();
        $this->dm->createQueryBuilder('MBHHotelBundle:Region')->remove()->getQuery()->execute();
        $this->dm->createQueryBuilder('MBHHotelBundle:City')->remove()->getQuery()->execute();

        foreach ($combinedArray as $countryInfo) {
            $country = new Country();
            //$output->writeln($countryInfo['title_ru'].' - '.$countryInfo['title_en']);

            $country->setTitle($countryInfo['title_ru']);
            $country->setTranslatableLocale('ru_RU');

            $translationRepository
                ->translate($country, 'title', 'en_EN', $countryInfo['title_en'])
                ->translate($country, 'title', 'ru_RU', $countryInfo['title_ru']);

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

            $total++;
        }

        $this->dm->flush();

        $time = $start->diff(new \DateTime());
        $output->writeln('Check & generation complete. Total entries: ' . $total . '. Elapsed time: ' . $time->format('%H:%I:%S'));
    }
}