<?php
/**
 * Created by PhpStorm.
 * User: danya
 * Date: 28.07.17
 * Time: 14:44
 */

namespace MBH\Bundle\PackageBundle\Lib;

use MBH\Bundle\BaseBundle\Lib\Exception;
use MBH\Bundle\PackageBundle\Document\Order;

class PackageCreationException extends Exception
{
    /**
     * @var Order
     */
    public $order;

    public function __construct(Order $order, $message = "", $code = 0, \Exception $previous = null)
    {
        $this->order = $order;
        parent::__construct($message, $code, $previous);
    }
}