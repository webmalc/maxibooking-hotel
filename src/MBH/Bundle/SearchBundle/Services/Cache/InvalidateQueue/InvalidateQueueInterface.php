<?php


namespace MBH\Bundle\SearchBundle\Services\Cache\InvalidateQueue;


interface InvalidateQueueInterface
{
    public function addToQueue($data): void;
}