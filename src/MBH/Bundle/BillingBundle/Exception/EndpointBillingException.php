<?php
/**
 * Date: 20.06.19
 */

namespace MBH\Bundle\BillingBundle\Exception;


use Throwable;

class EndpointBillingException extends BillingException
{
    public function __construct(string $endpoint)
    {
        $message = sprintf('Endpoint "%s": ID is not specified', $endpoint);

        parent::__construct($message, 500, null);
    }

}
