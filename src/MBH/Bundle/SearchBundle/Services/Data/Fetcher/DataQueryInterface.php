<?php


namespace MBH\Bundle\SearchBundle\Services\Data\Fetcher;


use MBH\Bundle\SearchBundle\Document\SearchConditions;

interface DataQueryInterface
{
    public function getSearchHash(): string;

    public function getBegin();

    public function getEnd();

    public function getTariffId(): ?string;

    public function getRoomTypeId(): ?string;

    public function getSearchConditions(): ?SearchConditions;

}