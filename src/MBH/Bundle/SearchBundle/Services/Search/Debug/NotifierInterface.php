<?php


namespace MBH\Bundle\SearchBundle\Services\Search\Debug;


interface NotifierInterface
{
    public function notify(string $message);
}