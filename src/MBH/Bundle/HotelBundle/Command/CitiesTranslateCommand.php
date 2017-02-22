<?php

namespace MBH\Bundle\HotelBundle\Command;


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CitiesTranslateCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('mbh:city:load')
            ->setDescription('Translate cities in the csv')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $basePath = $this->getContainer()->get('kernel')->getRootDir() . '/../src/MBH/Bundle/HotelBundle/Resources/csv/';

        $filesPaths = [
            'region' => $basePath . 'region.csv',
            'city' => $basePath . 'city.csv',
        ];

        $regions = $this->getRegions($filesPaths['region']);

        $yandexTranslator = $this->getContainer()->get('mbh.yandex_translator');

        $regionsCount = count($regions);
        $iterationElementCount = 100;
        $iterationCount = ceil($regionsCount / $iterationElementCount);

        $translatedValues = [];
        for ($i = 0; $i < $iterationCount; $i++) {
            $translatedDataArray = array_splice($regions, 0, $iterationElementCount);
            $translatedValues = array_merge($translatedValues, $yandexTranslator->translate($translatedDataArray));
            sleep(1);
        }

        $this->writeTranslatedValues($translatedValues, $filesPaths['region']);
    }

    private function getRegions($filePath)
    {
        $regionFile = fopen($filePath, 'r');
        $regions = [];

        while (($row = fgetcsv($regionFile, 1000, ';')) !== false ) {
            if(!is_numeric($row[0])) {
                continue;
            }

            $regions[] = $row[3];
        }

        return $regions;
    }

    private function writeTranslatedValues($translatedValues, $filePath)
    {
        $count = 0;
        $newCsvData = array();
        if (($handle = fopen($filePath, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ";")) !== FALSE && isset($translatedValues[$count])) {
                if(!is_numeric($data[0])) {
                    continue;
                }
                $data[] = $translatedValues[$count];
                $newCsvData[] = $data;
                $count++;
            }

            fclose($handle);
        }

        $handle = fopen($filePath, 'w');

        foreach ($newCsvData as $line) {
            fputcsv($handle, $line, ';');
        }

        fclose($handle);
    }
}