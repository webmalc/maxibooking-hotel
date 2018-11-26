<?php
/**
 * Created by PhpStorm.
 * Date: 21.11.18
 */

namespace MBH\Bundle\OnlineBundle\Lib\Exception;


class NotFoundConfigMBSiteException extends MBSiteException
{

    /**
     * NotFoundMBSiteConfigException constructor.
     */
    public function __construct()
    {
        parent::__construct('Not found config for mb site.', 404, null);
    }
}