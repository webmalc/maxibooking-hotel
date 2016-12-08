<?php

namespace MBH\Bundle\VegaBundle\Service;

use Symfony\Component\Yaml\Yaml;

/**
 * Class DictionaryProvider
 * Provide Vega dictionary as array

 */
class DictionaryProvider
{
    private $cache;

    /**
     * @return array
     */
    public function getDocumentTypes()
    {
        return $this->getDictionary('docum');
    }

    /**
     * @return array
     */
    public function getScanTypes()
    {
        return $this->getDictionary('tpscan');
    }

    /**
     * @return array
     */
    public function getHouseParts()
    {
        return $this->getDictionary('housepart');
    }

    public function getDictTypes()
    {
        return $this->getDictionary('dict_type_sv');
    }

    /**
     * @return array
     */
    private function getDictionary($name)
    {
        if(!isset($this->cache[$name])){
            $list = Yaml::parse(file_get_contents(__DIR__.'/../Resources/config/'.$name.'.yml'))[$name];
            $this->cache[$name] = $list;
        }

        return $this->cache[$name];
    }
}