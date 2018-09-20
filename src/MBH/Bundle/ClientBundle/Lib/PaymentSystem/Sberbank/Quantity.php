<?php
/**
 * Created by PhpStorm.
 * Date: 04.09.18
 */

namespace MBH\Bundle\ClientBundle\Lib\PaymentSystem\Sberbank;

/**
 * Class Quantity
 * @package MBH\Bundle\ClientBundle\Lib\PaymentSystem\Sberbank
 * @see https://securepayments.sberbank.ru/wiki/doku.php/integration:api:rest:requests:register_cart#quantity
 */
class Quantity implements \JsonSerializable
{
    /**
     * @var string
     */
    private $value;

    /**
     * @var string
     */
    private $measure;

    /**
     * Quantity constructor.
     * @param string $value
     * @param string $measure
     */
    public function __construct(string $value, string $measure)
    {
        $this->value = $value;
        $this->measure = $measure;
    }

    public function jsonSerialize()
    {
        return [
            'value'   => $this->value,
            'measure' => $this->measure,
        ];
    }
}