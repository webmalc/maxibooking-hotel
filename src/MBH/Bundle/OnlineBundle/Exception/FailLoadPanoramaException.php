<?php
/**
 * Created by PhpStorm.
 * Date: 21.01.19
 */

namespace MBH\Bundle\OnlineBundle\Exception;


class FailLoadPanoramaException extends MBSiteException
{
    public function __construct(string $message = "Failed to create file.")
    {
        parent::__construct($message, 500, null);
    }

}