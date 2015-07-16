<?php

namespace MBH\Bundle\VegaBundle\Service;

use Symfony\Component\Yaml\Yaml;

/**
 * Class DictionaryProvider
 * @author Aleksandr Arofikin <sasaharo@gmail.com>
 */
class DictionaryProvider
{
    private $cache;

    public function getDocumentTypes()
    {
        return $this->getDictionary('docum');
    }

    public function getScanTypes()
    {
        return $this->getDictionary('tpscan');
    }

    public function getHouseParts()
    {
        return $this->getDictionary('housepart');
    }

    private function getDictionary($name)
    {
        if(!isset($this->cache[$name])){
            $list = Yaml::parse(__DIR__.'/../Resources/config/'.$name.'.yml')[$name];
            $this->cache[$name] = $list;
        }

        return $this->cache[$name];
    }
}