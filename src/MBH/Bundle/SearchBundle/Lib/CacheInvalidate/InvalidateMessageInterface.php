<?php


namespace MBH\Bundle\SearchBundle\Lib\CacheInvalidate;


interface InvalidateMessageInterface
{
    public function getBegin(): ?\DateTime;

    public function getEnd(): ?\DateTime;

    public function getTariffIds(): ?array;

    public function getRoomTypeIds(): ?array;
}