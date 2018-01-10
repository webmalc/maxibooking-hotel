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
     * @param array $excludedFields
     * @return array
     */
    public function fillArrayByKeys($data, $keysInFilledArrayByKeysInDataArray, $excludedFields = [])
    {
        $result = [];
        foreach ($data as $itemKey => $dataItem) {
            if (in_array($itemKey, $excludedFields)) {
                $result[$itemKey] = $dataItem;
            } elseif (isset($keysInFilledArrayByKeysInDataArray[$itemKey])) {
                $filledArrayKey = $keysInFilledArrayByKeysInDataArray[$itemKey];
                $result[$filledArrayKey] = $dataItem;
            } else {
                throw new \InvalidArgumentException('Key for data key "' . $itemKey . '" is not specified!' );
            }
        }

        return $result;
    }

    /**
     * @param array $data
     * @param array $callbacksByFieldKeys
     * @return array
     */
    public function convertArrayDataByCallbacks(array $data, array $callbacksByFieldKeys)
    {
        $result = [];
        foreach ($data as $key => $value) {
            $result[$key] = isset($callbacksByFieldKeys[$key]) ? $callbacksByFieldKeys[$key]($value) : $value;
        }

        return $result;
    }
}