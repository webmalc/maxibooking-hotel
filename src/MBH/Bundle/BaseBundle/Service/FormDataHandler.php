<?php

namespace MBH\Bundle\BaseBundle\Service;

class FormDataHandler
{
    /**
     * @param $data
     * @param $fields
     * @return array
     */
    public function getUnsetFields($data, $fields)
    {
        return array_filter($fields, function ($fieldName) use ($data) {
            return !isset($data[$fieldName]);
        });
    }

    /**
     * @param $data
     * @param $keysInFilledArrayByKeysInDataArray
     * @param array $fieldCallbacks
     * @return array
     */
    public function fillArrayByKeys($data, $keysInFilledArrayByKeysInDataArray, array $fieldCallbacks = [])
    {
        $result = [];
        foreach ($keysInFilledArrayByKeysInDataArray as $dataArrayKey => $filledArrayKey) {
            if (isset($data[$dataArrayKey])) {
                $filledData = $data[$dataArrayKey];

                $result[$filledArrayKey] = isset($fieldCallbacks[$dataArrayKey])
                    ? $fieldCallbacks[$dataArrayKey]($filledData)
                    : $filledData;
            }
        }

        return $result;
    }
}