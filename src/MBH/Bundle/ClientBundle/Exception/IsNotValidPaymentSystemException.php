<?php
/**
 * Created by PhpStorm.
 * Date: 26.09.18
 */

namespace MBH\Bundle\ClientBundle\Exception;



class IsNotValidPaymentSystemException extends PaymentSystemException
{
    public function __construct(string $paymentSystemName)
    {
        $message = sprintf('Specified payment system "%s" is not found.', $paymentSystemName);

        parent::__construct($message, 404, null);
    }

}