<?php


namespace MBH\Bundle\SearchBundle\Lib\Events;


use Symfony\Component\EventDispatcher\Event;

class InvalidateKeysEvent extends Event
{
    /**
     * @var string[]
     */
    private $keys;

    /**
     * @return mixed
     */
    public function getKeys()
    {
        return $this->keys;
    }

    /**
     * @param mixed $keys
     * @return InvalidateKeysEvent
     */
    public function setKeys(array $keys): InvalidateKeysEvent
    {
        $this->keys = $keys;

        return $this;
    }



}