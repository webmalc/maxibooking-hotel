<?php

namespace MBH\Bundle\ClientBundle\Lib;

use MBH\Bundle\BaseBundle\Service\CsvReader;
use Symfony\Component\Translation\TranslatorInterface;

class FMSDictionaries
{
    const RUSSIAN_PASSPORT_ID = 103008;
    const TRAVEL_PASSPORT = 103007;

    /** @var  CsvReader */
    private $csvReader;

    /** @var TranslatorInterface */
    private $translator;

    public function __construct(CsvReader $csvReader, TranslatorInterface $translator) {
        $this->csvReader = $csvReader;
        $this->translator = $translator;
    }

    /**
     * @return array
     */
    public function getVisaMultiplicityData()
    {
        return $this->readFMSDictionary('dict_visamultiplicity.csv');
    }

    /**
     * @return array
     */
    public function getVisaCategories()
    {
        return $this->readFMSDictionary('dict_visacategory.csv');
    }

    /**
     * @return array
     */
    public function getVisitPurposesData()
    {
        return $this->readFMSDictionary('dict_visitpurpose.csv');
    }

    /**
     * @return array
     */
    public function getEntryGoalOptions()
    {
        return $this->readFMSDictionary('dict_entrygoal.csv', false);
    }

    /**
     * @return array
     */
    public function getMigrationSpecStatuses()
    {
        return $this->readFMSDictionary('dict_mig_specialstatus.csv');
    }

    /**
     * @return array
     */
    public function getDocumentTypes()
    {
        return $this->readFMSDictionary('dict_documenttype.csv');
    }

    /**
     * @param string $fileName
     * @param bool $filterByActuality
     * @return array
     */
    private function readFMSDictionary(string $fileName, $filterByActuality = true)
    {
        return $this->csvReader->readByCallback($fileName, function($rowData, &$result, $rowNumber) use ($filterByActuality) {
            if ($rowNumber != 0 && (!$filterByActuality || $rowData[2] == 'Actual')) {
                $key = intval($rowData[0]);
                $result[$key] = $this->translator->trans($rowData[1]);
            }
        });
    }
}
