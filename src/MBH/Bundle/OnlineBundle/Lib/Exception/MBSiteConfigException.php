<?php
/**
 * Created by PhpStorm.
 * Date: 21.11.18
 */

namespace MBH\Bundle\OnlineBundle\Lib\Exception;


use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class MBSiteConfigException extends \RuntimeException implements HttpExceptionInterface
{
    /**
     * @var integer
     */
    private $statusCode;

    public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
    {
        $this->statusCode = $code;
        parent::__construct($message, $code, $previous);
    }

    public function getStatusCode()
    {
        $msg = $this->statusCode;

        if (!empty($this->getMessage())) {
            $msg .= ' ' . $this->getMessage();
        }

        return $msg;
    }

    public function getHeaders()
    {
        return [];
    }
}