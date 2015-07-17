<?php

namespace MBH\Bundle\VegaBundle\Service;

use Symfony\Component\Yaml\Yaml;

/**
 * Class DictionaryProvider
 * Provide Vega dictionary as array
 * @author Aleksandr Arofikin <sasaharo@gmail.com>
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

    /**
     * @return array
     */
    private function getDictionary($name)
    {
        if(!isset($this->cache[$name])){
            $list = Yaml::parse(__DIR__.'/../Resources/config/'.$name.'.yml')[$name];
            $this->cache[$name] = $list;
        }

        return $this->cache[$name];
    }
}