<?php

namespace MBH\Bundle\ClientBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\EmbeddedDocument()
 * Class FrontSettings
 * @package MBH\Bundle\ClientBundle\Document
 */
class FrontSettings
{
    const DEFAULT_NUMBER_OF_ROOMS = 30;
    /**
     * @var int
     * @ODM\Field(type="hash")
     */
    private $roomsInChessboard = [];

    /**
     * @param string $username
     * @return int
     */
    public function getRoomsInChessboard(string $username): ?int
    {
        return isset($this->roomsInChessboard[$username]) ? $this->roomsInChessboard[$username] : self::DEFAULT_NUMBER_OF_ROOMS;
    }

    /**
     * @param int $roomsInChessboard
     * @param string $username
     * @return FrontSettings
     */
    public function setRoomsInChessboard(int $roomsInChessboard, string $username): FrontSettings
    {
        if (!is_array($this->roomsInChessboard)) {
            $this->roomsInChessboard = [];
        }

        $this->roomsInChessboard[$username] = $roomsInChessboard;

        return $this;
    }

}