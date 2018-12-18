<?php
/**
 * Created by PhpStorm.
 * Date: 07.06.18
 */

namespace MBH\Bundle\ClientBundle\Lib\PaymentSystem\NewRbk;


class Error
{
    /**
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $message;

    /**
     * @var string
     */
    private $errorType;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $name;

    public static function instance($data)
    {
        $self = new self();

        foreach ($data as $key => $value) {
            if (property_exists($self, $key)) {
                $self->$key = $value;
            }
        }

        return $self;
    }

    public function getInfo():string
    {
        $errMsg = 'hmm, unknown error at create the invoice';
        if ($this->code !== null) {
            $errMsg = 'code: ' . $this->code . ', msg: ' . $this->message;
        } elseif ($this->errorType !== null) {
            $errMsg = 'errorType: ' . $this->errorType;
            $errMsg .= ', name: ' . $this->name;
            $errMsg .= ', desc: ' . $this->description;
        }

        return $errMsg;
    }

    /**
     * @return string
     */
    public function getErrorType(): ?string
    {
        return $this->errorType;
    }

    /**
     * @return string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getMessage(): ?string
    {
        return $this->message;
    }
}