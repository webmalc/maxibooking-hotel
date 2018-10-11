<?php
/**
 * Created by PhpStorm.
 * Date: 10.10.18
 */

namespace MBH\Bundle\PriceBundle\Lib;


use MBH\Bundle\PriceBundle\Document\PriceCache;

class PriceCacheFactory extends PriceCacheKit
{
    public function create(array $data): PriceCache
    {
        foreach ($data as $property => $value) {
            if ($value === '') {
                continue;
            }
            if ($property === 'isPersonPrice') {
                $value = (bool) $value;
            }
            $this->$property = is_numeric($value) ? (float) $value : $value;
        }

        return $this->createPriceCache();
    }
}