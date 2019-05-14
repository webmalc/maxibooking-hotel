<?php
/**
 * Date: 14.05.19
 */

namespace MBH\Bundle\OnlineBundle\Exception\FormConfig;


class NotFoundFormConfigException extends FormConfigException
{
    public function __construct(string $message = "Not found config for online form.")
    {
        parent::__construct($message, 404, null);
    }

}
