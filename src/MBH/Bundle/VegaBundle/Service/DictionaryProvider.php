<?php

namespace MBH\Bundle\VegaBundle\Service;

use Symfony\Component\Translation\DataCollectorTranslator;
use Symfony\Component\Yaml\Yaml;

/**
 * Class DictionaryProvider
 * Provide Vega dictionary as array

 */
class DictionaryProvider
{
    private $cache;

    /** @var DataCollectorTranslator */
    private $translator;

    public function __construct(DataCollectorTranslator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @return array
     */
    public function getDocumentTypes(): array
    {
        return $this->getDictionary('docum');
    }

    /**
     * @return array
     */
    public function getScanTypes(): array
    {
        return $this->getDictionary('tpscan');
    }

    /**
     * @return array
     */
    public function getHouseParts(): array
    {
        return $this->getDictionary('housepart');
    }

    public function getDictTypes(): array
    {
        return $this->getDictionary('dict_type_sv');
    }

    /**
     * @param $name
     * @return array
     */
    private function getDictionary($name): array
    {
        if(!isset($this->cache[$name])){
            $list = Yaml::parse(file_get_contents(__DIR__.'/../Resources/config/'.$name.'.yml'))[$name];
            $this->cache[$name] = $this->trans($list);
        }

        return $this->cache[$name];
    }

    private function trans(array $data): array
    {
        $data = array_values($data);
        for ($i = 0, $iMax = count($data); $i < $iMax; ++$i) {
            $data[$i] = $this->translator->trans($data[$i]);
        }

        return $data;
    }
}
