<?php
/**
 * Created by PhpStorm.
 * Date: 02.07.18
 */

namespace MBH\Bundle\ClientBundle\Lib\PaymentSystem\Robokassa;


use MBH\Bundle\ClientBundle\Document\Robokassa;
use MBH\Bundle\ClientBundle\Service\DocumentSerialize\CashDocument;
use MBH\Bundle\PackageBundle\Document\Order;

class Receipt implements \JsonSerializable
{
    /**
     * Система налогообложения
     *
     * @var string
     */
    private $sno;

    /**
     * @var Item[]
     */
    private $items = [];

    public static function create(Order $order, Robokassa $robokassa): self
    {
        $items = [];
        $tax = $robokassa->getTaxationRateCode();

        foreach ($order->getPackages() as $package) {
            $item = new Item();
            $item->setName($package->getRoomType()->getName());
            $item->setTax($tax);
            $item->setQuantity((string) 1);
            $item->setSum($order->getPrice());

            $items[] = $item;

            foreach ($package->getServices() as $service) {
                $quantity = $service->getAmount() * $service->getNights() * $service->getPersons();

                $item = new Item();
                $item->setName($service->getService()->getName());
                $item->setTax($tax);
                $item->setQuantity((string) $quantity);
                $item->setSum($service->getPrice());

                $items[] = $item;
            }
        }

        $self = new self();
        $self->setSno($robokassa->getTaxationSystemCode());
        $self->setItems($items);

        return $self;
    }

    /**
     * @return string
     */
    public function getSno(): string
    {
        return $this->sno;
    }

    /**
     * @param string $sno
     */
    public function setSno(string $sno): void
    {
        $this->sno = $sno;
    }

    /**
     * @return Item[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @param Item[] $items
     */
    public function setItems(array $items): void
    {
        $this->items = $items;
    }

    public function jsonSerialize()
    {
        return [
            'sno'   => $this->getSno(),
            'items' => $this->getItems(),
        ];
    }
}