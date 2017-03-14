<?php

namespace MBH\Bundle\HotelBundle\Command;

use MBH\Bundle\BaseBundle\Service\YandexTranslator;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TerritoriesTranslateCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('mbh:territory:translate')
            ->setDescription('Translate cities in the csv file using yandex translator')
            ->addArgument('translatedData', InputArgument::OPTIONAL, 'Input value can be "cities" or "regions"')
            ->addArgument('translationDirection', InputArgument::OPTIONAL);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $basePath = $this->getContainer()->get('kernel')->getRootDir() . '/../src/MBH/Bundle/HotelBundle/Resources/csv/';

        $filesPaths = [
            'region' => $basePath . 'region.csv',
            'city' => $basePath . 'city.csv',
        ];

        $translatedData = $input->getArgument('translatedData');
        $translationDirectionInput = $input->getArgument('translationDirection');
        $translationDirection = $translationDirectionInput
        && in_array($translationDirectionInput, YandexTranslator::getTranslationOptions())
            ? $translationDirectionInput
            : YandexTranslator::RUSSIAN_TO_ENGLISH_TRANSLATION_DIRECTION;

        if ($translatedData == 'cities') {
            $this->translateAndWrite($filesPaths['city'], 'city', $output, $translationDirection);
        } elseif ($translatedData == 'regions') {
            $this->translateAndWrite($filesPaths['region'], 'region', $output, $translationDirection);
        } elseif (is_null($translatedData)) {
            foreach ($filesPaths as $name => $filePath) {
                $this->translateAndWrite($filePath, $name, $output, $translationDirection);
            }
        } else {
            $output->writeln([
                '============',
                'Wrong first parameter',
                '============'
            ]);
        }
    }

    /**
     * Add to specified file translated data
     *
     * @param $filePath
     * @param $translatedDataName
     * @param OutputInterface $output
     * @param $translationDirection
     */
    private function translateAndWrite($filePath, $translatedDataName, OutputInterface $output, $translationDirection)
    {
        $output->writeln([
            '',
            "Add $translatedDataName names translations. It may take a few minutes...",
            '============',
            '',
        ]);

        if (!file_exists($filePath) || !is_readable($filePath)) {
            $output->writeln('<error>Error. New data not loaded. File ' . $filePath . ' not exist!!!</error>');
            return;
        }

        $yandexTranslator = $this->getContainer()->get('mbh.yandex_translator');
        $dataArray = $this->getDataArray($filePath);
        $translatedValues = $this->getTranslatedData($dataArray, $yandexTranslator, $translationDirection);
        $this->writeTranslatedValues($translatedValues, $filePath, $translationDirection);
        $output->writeln([
            ucwords($translatedDataName) . " translation completed",
            ''
        ]);
    }

    /**
     * Get array of data needed to translate
     *
     * @param $filePath
     * @return array
     */
    private function getDataArray($filePath)
    {
        $regionFile = fopen($filePath, 'r');
        $regions = [];

        while (($row = fgetcsv($regionFile, 1000, ';')) !== false) {
            if (!is_numeric($row[0])) {
                continue;
            }

            $regions[] = $row[3];
        }

        return $regions;
    }

    /**
     * Get array of translated data from array of values needed to translate
     *
     * @param $dataArray
     * @param YandexTranslator $translator
     * @param $translationDirection
     * @return array
     */
    private function getTranslatedData($dataArray, YandexTranslator $translator, $translationDirection)
    {
        //Переводим по 100 слов за раз.
        $regionsCount = count($dataArray);
        $iterationElementCount = 100;
        $iterationCount = ceil($regionsCount / $iterationElementCount);

        $translatedValues = [];
        for ($i = 0; $i < $iterationCount; $i++) {
            $translatedDataArray = array_splice($dataArray, 0, $iterationElementCount);
            $translatedValues = array_merge($translatedValues,
                $translator->translate($translatedDataArray, $translationDirection));
            sleep(1);
        }

        return $translatedValues;
    }

    /**
     * Write translated values in specified file
     *
     * @param $translatedValues
     * @param $filePath
     * @param $translationDirection
     */
    private function writeTranslatedValues($translatedValues, $filePath, $translationDirection)
    {
        $count = 0;
        $newCsvData = array();
        if (($handle = fopen($filePath, "r")) !== false) {
            while (($data = fgetcsv($handle, 1000, ";")) !== false && isset($translatedValues[$count])) {
                if (is_numeric($data[0])) {
                    $data[] = $translatedValues[$count];
                    $count++;
                } else {
                    $index = strpos($translationDirection, '-');
                    $data[] = substr($translationDirection, $index + 1) . '_name';
                }
                $newCsvData[] = $data;
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