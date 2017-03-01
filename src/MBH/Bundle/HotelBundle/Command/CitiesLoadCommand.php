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
            ->setDescription('Loading countries, regions & cities in the database');
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
        while (($row = fgetcsv($countryFile, 1000, ';')) !== false) {
            if (!is_numeric($row[0])) {
                continue;
            }
            $countries[$row[0]] = [
                'title_ru' => $row[2],
                'title_en' => $row[3],
            ];
            !isset($row[4]) ?: $countries[$row[0]]['ISO-3166-Alpha-2'] = $row[4];
            !isset($row[5]) ?: $countries[$row[0]]['ISO-3166-Alpha-3'] = $row[5];
            !isset($row[6]) ?: $countries[$row[0]]['ISO-3166-digital'] = $row[6];
        }

        // get regions array
        $regionFile = fopen($filesPaths['region'], 'r');
        $regions = [];
        while (($row = fgetcsv($regionFile, 1000, ';')) !== false) {
            if (!is_numeric($row[0])) {
                continue;
            }
            $regions[$row[0]] = [
                'countryId' => $row[1],
                'title_ru' => $row[3],
                'title_en' => $row[4]
            ];
        }

        // get cities array
        $cityFile = fopen($filesPaths['city'], 'r');
        $cities = [];
        while (($row = fgetcsv($cityFile, 1000, ';')) !== false) {
            if (!is_numeric($row[0])) {
                continue;
            }
            $cities[$row[0]] = [
                'countryId' => $row[1],
                'regionId' => $row[2],
                'title_ru' => $row[3],
                'title_en' => $row[4]
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

            !isset($country['ISO-3166-Alpha-2']) ?: $combinedArray[$countryId]['ISO-3166-Alpha-2'] = $country['ISO-3166-Alpha-2'];
            !isset($country['ISO-3166-Alpha-3']) ?: $combinedArray[$countryId]['ISO-3166-Alpha-3'] = $country['ISO-3166-Alpha-3'];
            !isset($country['ISO-3166-digital']) ?: $combinedArray[$countryId]['ISO-3166-digital'] = $country['ISO-3166-digital'];

            foreach ($regions as $regionId => $region) {
                if ($region['countryId'] == $countryId) {
                    $combinedArray[$countryId]['regions'][$regionId] = [
                        'title_ru' => $region['title_ru'],
                        'title_en' => $region['title_en'],
                        'cities' => []
                    ];

                    foreach ($cities as $cityId => $city) {
                        if ($city['regionId'] == $regionId) {
                            $combinedArray[$countryId]['regions'][$regionId]['cities'][$cityId] = [
                                'title_ru' => $city['title_ru'],
                                'title_en' => $city['title_en']
                            ];
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

        $output->writeln([
            '',
            'Сохранение данных о городах, регионах и странах. Это может занять несколько минут',
            ''
        ]);
        foreach ($combinedArray as $countryInfo) {
            $country = new Country();
            !isset($countryInfo['ISO-3166-Alpha-2']) ?: $country->setIsoAlpha2($countryInfo['ISO-3166-Alpha-2']);
            !isset($countryInfo['ISO-3166-Alpha-3']) ?: $country->setIsoAlpha2($countryInfo['ISO-3166-Alpha-3']);
            !isset($countryInfo['ISO-3166-digital']) ?: $country->setIsoAlpha2($countryInfo['ISO-3166-digital']);

            $country->setTitle($countryInfo['title_ru']);
            $country->setTranslatableLocale('ru_RU');

            $translationRepository
                ->translate($country, 'title', 'en_EN', $countryInfo['title_en'])
                ->translate($country, 'title', 'ru_RU', $countryInfo['title_ru']);

            $this->dm->persist($country);

            foreach ($countryInfo['regions'] as $regionInfo) {
                $region = new Region();
                $region->setCountry($country);

                $region->setTitle($regionInfo['title_ru']);
                $region->setTranslatableLocale('ru_RU');

                $translationRepository
                    ->translate($region, 'title', 'en_EN', $regionInfo['title_en'])
                    ->translate($region, 'title', 'ru_RU', $regionInfo['title_ru']);

                $this->dm->persist($region);

                foreach ($regionInfo['cities'] as $cityTitles) {
                    $city = new City();
                    $city->setCountry($country)->setRegion($region);

                    $city->setTitle($cityTitles['title_ru']);
                    $city->setTranslatableLocale('ru_RU');

                    $translationRepository
                        ->translate($city, 'title', 'en_EN', $cityTitles['title_en'])
                        ->translate($city, 'title', 'ru_RU', $cityTitles['title_ru']);

                    $this->dm->persist($city);
                }
            }
            $output->writeln('Сохранена страна ' . $country);
            $this->dm->flush();
            $total++;
        }
        $this->dm->flush();

        $time = $start->diff(new \DateTime());
        $output->writeln('Check & generation complete. Total entries: ' . $total . '. Elapsed time: ' . $time->format('%H:%I:%S'));
    }
}