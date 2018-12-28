<?php
/**
 * Created by PhpStorm.
 * Date: 13.06.18
 */

namespace MBH\Bundle\OnlineBundle\Lib;


use Symfony\Component\DependencyInjection\ContainerInterface;

class SearchFormResult implements \JsonSerializable
{
    /**
     * @var bool
     */
    private $orderFound = false;

    /**
     * @var float
     */
    private $total;

    /**
     * @var string
     */
    private $orderId;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var \Symfony\Component\Translation\DataCollectorTranslator
     */
    private $translator;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        $this->translator = $this->container->get('translator');
    }

    /**
     * @param float $total
     */
    public function setTotal(float $total): void
    {
        $this->total = $total;
    }

    /**
     * @param string $orderId
     */
    public function setOrderId(string $orderId): void
    {
        $this->orderId = $orderId;
    }

    public function orderIsFound(): void
    {
        $this->orderFound = true;
    }

    public function jsonSerialize()
    {
        $result = [];
        if (!$this->orderFound) {
            $result['error'] = $this->translate('api.payment_form.result_search.not_found_order');
        } else {
            $result['needIsPaid'] = $this->isNeedIsPaid();
            if ($this->isNeedIsPaid()) {
                $result['data'] = [
                    'total'     => $this->total,
                    'orderId'   => $this->orderId,
                ];
            } else {
                $result['data'] = $this->translate('api.payment_form.result_search.order_has_been_paid');
            }
        }

        return $result;
    }

    private function translate(string $msg): string
    {
        return $this->translator->trans($msg);
    }

    /**
     * @return bool
     */
    private function isNeedIsPaid(): bool
    {
        return $this->total !== null && $this->orderId !== null;
    }
}