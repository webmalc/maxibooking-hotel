<?php
namespace MBH\Bundle\PackageBundle\Lib;

use Exception;

class DeleteException extends Exception
{
    public $total;

    public function __construct($message = "", $total = null, $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->total = $total;
    }
}