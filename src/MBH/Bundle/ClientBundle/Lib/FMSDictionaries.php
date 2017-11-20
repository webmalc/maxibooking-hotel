<?php

namespace MBH\Bundle\ClientBundle\Lib;

use MBH\Bundle\BaseBundle\Service\CsvReader;

class FMSDictionaries
{
    const RUSSIAN_PASSPORT_ID = 103008;
    const TRAVEL_PASSPORT = 103007;

    /** @var  CsvReader */
    private $csvReader;

    public function __construct(CsvReader $csvReader) {
        $this->csvReader = $csvReader;
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
                $result[$key] = $rowData[1];
            }
        });
    }
}