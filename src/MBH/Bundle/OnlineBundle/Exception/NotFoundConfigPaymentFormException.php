<?php
/**
 * Created by PhpStorm.
 * Date: 04.12.18
 */

namespace MBH\Bundle\OnlineBundle\Exception;


class NotFoundConfigPaymentFormException extends PaymentFormException
{
    public function __construct(string $message = 'Not found config for payment form.')
    {
        parent::__construct($message, 404, null);
    }

}