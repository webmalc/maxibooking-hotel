<?php

namespace MBH\Bundle\PackageBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ODM\Document(collection="RoomCacheTmp")
 * @Gedmo\Loggable
 */
class RoomCacheTmp extends RoomCache
{   

}
