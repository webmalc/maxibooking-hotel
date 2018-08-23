<?php
/**
 * Created by PhpStorm.
 * Date: 01.08.18
 */

namespace MBH\Bundle\ClientBundle\Lib\PaymentSystem\Tinkoff;


use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class InitCommon
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Идентификатор терминала, выдается Продавцу Банком
     * обязательный да
     * String(20)
     *
     * @var string
     */
    protected $terminalKey;

    /**
     * Сумма в копейках
     * обязательный да
     * Number(10)
     *
     * @var int
     */
    protected $amount;

    /**
     * Номер заказа в системе Продавца
     * обязательный да
     * String(50)
     *
     * @var string
     */
    protected $orderId;

    /**
     * @return string
     */
    public function getTerminalKey(): string
    {
        return $this->terminalKey;
    }

    /**
     * @return int
     */
    public function getAmount(): int
    {
        return $this->amount;
    }

    /**
     * @return string
     */
    public function getOrderId(): string
    {
        return $this->orderId;
    }

    /**
     * @param array $data
     * @return string
     */
    protected function returnSha256(array $data): string
    {
        ksort($data);

        return hash('sha256', implode('',$data));
    }
}