<?php


namespace MBH\Bundle\SearchBundle\Services\Data\Fetcher;


use DateTime;
use MBH\Bundle\SearchBundle\Document\SearchConditions;

interface DataQueryInterface
{
    public function getSearchHash(): ?string;

    public function getBegin(): DateTime;

    public function getEnd(): DateTime;

    public function getTariffId(): ?string;

    public function getRoomTypeId(): ?string;

    public function isExtendedDataQuery(): bool;

    public function getSearchConditions(): SearchConditions;

}