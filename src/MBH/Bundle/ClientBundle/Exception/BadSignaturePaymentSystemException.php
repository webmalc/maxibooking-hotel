<?php
/**
 * Created by PhpStorm.
 * Date: 26.09.18
 */

namespace MBH\Bundle\ClientBundle\Exception;


class BadSignaturePaymentSystemException extends PaymentSystemException
{
    public function __construct(string $message = "Bad signature")
    {
        parent::__construct($message, 404, null);
    }

}