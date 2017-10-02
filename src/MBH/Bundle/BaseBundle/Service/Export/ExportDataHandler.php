<?php
/**
 * Created by PhpStorm.
 * User: danya
 * Date: 28.07.17
 * Time: 11:36
 */

namespace MBH\Bundle\BaseBundle\Service\Export;

class ExportDataHandler
{
    const DEFAULT_DATE_FORMAT = 'd.m.Y H:i';

    /**
     * @param array $mongoData
     * @param null $fieldNames
     * @param $entityName
     * @return array
     */
    public function handleRawMongoData(array $mongoData, $fieldNames = null, $entityName)
    {
        $resultData = [];
        $columnData = $entityName::getExportableFieldsData();
        foreach ($mongoData as $id => $entityData) {
            $handledData = [];
            if (!is_null($fieldNames)) {
                foreach ($fieldNames as $fieldName) {
                    $handledData[$fieldName] = $this->getHandledData($fieldName, $entityData, $columnData[$fieldName]);
                }
            } else {
                foreach ($entityData as $fieldName => $fieldsData) {
                    $handledData[$fieldName] = $this->getHandledData($fieldName, $entityData, $columnData[$fieldName]);
                }
            }
            $resultData[$id] = $handledData;
        }

        return $resultData;
    }

    /**
     * @param $columnName
     * @param $entityData
     * @param $columnData
     * @return string
     * @throws \Exception
     */
    private function getHandledData($columnName, $entityData, $columnData)
    {
        if (isset($columnData['callback'])) {
            return $columnData['callback']($entityData);
        } elseif(isset($columnData['field'])) {
            if (isset($entityData[$columnData['field']])) {
                $fieldData = $entityData[$columnData['field']];
                if ($fieldData instanceof \MongoDate) {
                    return $fieldData->toDateTime()->format(self::DEFAULT_DATE_FORMAT);
                } elseif ($fieldData instanceof \MongoId) {
                    return $fieldData->serialize();
                }

                return $fieldData;
            }

            return '';
        }

        throw new \Exception('missing description for column "' . $columnName . '"');
    }
}