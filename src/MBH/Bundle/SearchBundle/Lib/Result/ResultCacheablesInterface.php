<?php


namespace MBH\Bundle\SearchBundle\Lib\Result;


interface ResultCacheablesInterface
{
    public function getId(): string;

    public function getSearchHash(): string;

}