<?php


namespace MBH\Bundle\SearchBundle\Lib\Data;


interface DataFetchQueryInterface
{
    public function getHash(): string;

    public function getBegin(): \DateTime;

    public function getEnd(): \DateTime;

    public function getMaxBegin(): \DateTime;

    public function getMaxEnd(): \DateTime;
}