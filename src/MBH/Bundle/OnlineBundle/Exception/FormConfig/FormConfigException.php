<?php
/**
 * Date: 14.05.19
 */

namespace MBH\Bundle\OnlineBundle\Exception\FormConfig;


use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class FormConfigException extends \RuntimeException implements HttpExceptionInterface
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
