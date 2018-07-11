<?php

namespace MBH\Bundle\BaseBundle\Service;

use Symfony\Component\Yaml\Yaml;

/**
 * Class YmlManager
 * @package MBH\Bundle\BaseBundle\Service
 */
class YmlManager
{
    /**
     * @param $filePath
     * @return array
     */
    public function getParsedData(string $filePath)
    {
        return Yaml::parse(file_get_contents($filePath));
    }

    /**
     * @param string $filePath
     * @param string $paramName
     * @return mixed
     */
    public function getParameter(string $filePath, string $paramName)
    {
        $parsedData = $this->getParsedData($filePath);

        return isset($parsedData[$paramName]) ? $parsedData[$paramName] : null;
    }

    /**
     * @param string $filePath
     * @param string $parentParameter
     * @param string $enclosedParameter
     * @return null|mixed
     */
    public function getEnclosedParameter(string $filePath, string $parentParameter, string $enclosedParameter)
    {
        $parsedData = $this->getParsedData($filePath);

        return isset($parsedData[$parentParameter][$enclosedParameter]) ? $parsedData[$parentParameter][$enclosedParameter] : null;
    }

    /**
     * @param string $filePath
     * @param string $paramName
     * @param $paramValue
     * @return bool
     */
    public function setSingleParameter(string $filePath, string $paramName, $paramValue)
    {
        $parsedData = $this->getParsedData($filePath);
        $parsedData[$paramName] = $paramValue;
        $this->setYamlContentFromArray($filePath, $parsedData);

        return true;
    }

    /**
     * @param string $filePath
     * @param string $parentParamName
     * @param string $paramName
     * @param string $paramValue
     * @return bool
     */
    public function setSingleEnclosedParameter(string $filePath, string $parentParamName, string $paramName, string $paramValue)
    {
        $parsedData = $this->getParsedData($filePath);
        if (isset($parsedData[$parentParamName])) {
            $parsedData[$parentParamName][$paramName] = $paramValue;
        } else {
            $parsedData[$parentParamName] = [$paramName => $paramValue];
        }

        $this->setYamlContentFromArray($filePath, $parsedData);

        return true;
    }

    /**
     * @param string $filePath
     * @param string $paramName
     * @return bool
     */
    public function unsetSingleParameter(string $filePath, string $paramName)
    {
        $parsedData = $this->getParsedData($filePath);
        unset($parsedData[$paramName]);
        $this->setYamlContentFromArray($filePath, $parsedData);

        return true;
    }

    /**
     * @param string $filePath
     * @param array $data
     */
    private function setYamlContentFromArray(string $filePath, array $data)
    {
        $stringValue = Yaml::dump($data);
        file_put_contents($filePath, $stringValue);
    }
}