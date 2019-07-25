<?php

namespace MBH\Bundle\ChannelManagerBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use MBH\Bundle\ChannelManagerBundle\Lib\ICalType\AbstractICalTypeChannelManagerRoom;

/**
 * @ODM\EmbeddedDocument
 */
class HomeAwayRoom extends AbstractICalTypeChannelManagerRoom
{

}
