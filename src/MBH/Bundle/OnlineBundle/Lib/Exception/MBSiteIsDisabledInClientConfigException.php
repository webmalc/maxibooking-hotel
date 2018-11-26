<?php
/**
 * Created by PhpStorm.
 * Date: 23.11.18
 */

namespace MBH\Bundle\OnlineBundle\Lib\Exception;


class MBSiteIsDisabledInClientConfigException extends MBSiteException
{
    public function __construct(string $message = "Site is disabled in settings.")
    {
        parent::__construct($message, 410, null);
    }

}