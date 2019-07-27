<?php


namespace MBH\Bundle\SearchBundle\Lib\Result;


interface ResultCacheablesInterface
{
    public function getId(): string;

    public function getCacheItemId(): string;

    public function setCacheItemId(string $id);

    public function setCached(bool $isCached);


}