<?php
/**
 * Date: 20.06.19
 */

namespace MBH\Bundle\BillingBundle\Exception;


class EmptyResponseBillingException extends BillingException
{
    public function __construct(string $url)
    {
       $message = sprintf('Can not get data by url %s', $url);

       parent::__construct($message, 500, null);
    }

}
