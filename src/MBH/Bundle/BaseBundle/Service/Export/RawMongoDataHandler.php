<?php
/**
 * Created by PhpStorm.
 * User: danya
 * Date: 28.07.17
 * Time: 11:36
 */

namespace MBH\Bundle\BaseBundle\Service\Export;

use MBH\Bundle\BaseBundle\Lib\Exportable;

class RawMongoDataHandler
{
    const DEFAULT_DATE_FORMAT = 'd.m.Y H:i';

    /**
     * @param array $mongoData
     * @param null $fieldNames
     * @param $entityName
     * @return array
     */
    public function handleRawMongoData(array $mongoData, $entityName, $fieldNames = null)
    {
        $resultData = [];
        /** @var Exportable $entityName */
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
                    return date(self::DEFAULT_DATE_FORMAT, $fieldData->sec);
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