<?php
/**
 * Created by PhpStorm.
 * Date: 27.08.18
 */

namespace MBH\Bundle\ClientBundle\Lib\PaymentSystem;


class HolderNamePaymentSystem
{
    /**
     * @var string
     */
    private $key;

    /**
     * @var string
     */
    private $name;

    /**
     * HolderNamePaymentSystem constructor.
     * @param string $key
     * @param string $name
     */
    public function __construct(string $key, string $name)
    {
        $this->key = $key;
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}