<?php

namespace MBH\Bundle\BaseBundle\Service;

use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\HttpKernel\KernelInterface;

class CsvReader
{
    private $kernel;

    public function __construct(KernelInterface $kernel) {
        $this->kernel = $kernel;
    }

    /**
     * @param string $fileName
     * @return array
     */
    public function readAsArray($fileName)
    {
        return $this->readByCallback($fileName, function (array $rowData, &$result) {
            $result[] = $rowData;
        });
    }

    /**
     * @param string $fileName
     * @param int $valueFieldNumber
     * @param int $keyFieldNumber
     * @param bool $keyAsNumber
     * @return array
     */
    public function readAsValueByKey(string $fileName, int $valueFieldNumber, int $keyFieldNumber, $keyAsNumber = false)
    {
        return $this->readByCallback($fileName, function($rowData, &$result) use ($valueFieldNumber, $keyFieldNumber, $keyAsNumber) {
            $key = $keyAsNumber ? (int)$rowData[$keyFieldNumber] : $rowData[$keyFieldNumber];
            $result[$key] = $rowData[$valueFieldNumber];
        });
    }

    /**
     * @param string $fileName
     * @param int $keyFieldNumber
     * @param bool $keyAsNumber
     * @return array
     */
    public function readAsArrayByKey(string $fileName, int $keyFieldNumber, $keyAsNumber = false)
    {
        return $this->readByCallback($fileName, function($rowData, &$result) use ($keyFieldNumber, $keyAsNumber) {
            $key = $keyAsNumber ? (int)$rowData[$keyFieldNumber] : $rowData[$keyFieldNumber];
            $result[$key] = $rowData;
        });
    }

    public function readAsArrayByNumberKeyWithoutFirstRow($fileName)
    {
        return $this->readByCallback($fileName, function($rowData, &$result, $rowNumber) {
            if ($rowNumber != 0) {
                $key = intval($rowData[0]);
                $result[$key] = $rowData;
            }
        });
    }
    
    /**
     * @param string $fileName
     * @param array $fieldNames
     * @return array
     */
    public function readAsAssociativeArray(string $fileName, array $fieldNames)
    {
        return $this->readByCallback($fileName, function ($rowData, &$result) use ($fieldNames) {
            $rowAssocData = [];
            foreach ($rowData as $fieldNumber => $value) {
                $rowAssocData[$fieldNames[$fieldNumber]] = $value;
            }
            $result[] = $rowAssocData;
        });
    }

    /**
     * @param string $fileName
     * @param callable $handlerFunction
     * @return array
     */
    public function readByCallback(string $fileName, callable $handlerFunction)
    {
        $resource = fopen($this->getFilePath($fileName), 'r');

        $result = [];
        $rowNumber = 0;
        if ($resource) {
            while (($rowData = fgetcsv($resource, 1000, ";")) !== false) {
                $handlerFunction($rowData, $result, $rowNumber);
                $rowNumber++;
            }

            fclose($resource);

            return $result;
        }

        throw new FileNotFoundException('Specified file ' . $fileName . ' not found');
    }

    /**
     * @param $fileName
     * @return string
     */
    private function getFilePath(string $fileName)
    {
        $root = $this->kernel->getBundle('MBHPackageBundle')->getPath();

        return $root . DIRECTORY_SEPARATOR . 'Resources' . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . $fileName;
    }
}