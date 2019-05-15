<?php
/**
 * Created by PhpStorm.
 * Date: 22.11.18
 */

namespace MBH\Bundle\OnlineBundle\Document\SocialLink;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\EmbeddedDocument()
 */
class SocialService extends SocialLink
{

}
