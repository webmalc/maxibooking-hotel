<?php
/**
 * Created by PhpStorm.
 * Date: 01.08.18
 */

namespace MBH\Bundle\ClientBundle\Lib\PaymentSystem\Tinkoff;

use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\ClientBundle\Document\Tinkoff;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\PackageService;

/**
 * @see https://oplata.tinkoff.ru/landing/develop/documentation
 *
 * Class Receipt
 * @package MBH\Bundle\ClientBundle\Lib\PaymentSystem\Tinkoff
 */
class Receipt implements \JsonSerializable
{
    /**
     * Массив, содержащий в себе информацию о товарах
     *
     * обязательное да
     *
     * @var Item[]
     */
    private $items = [];

    /**
     * Электронный адрес для отправки чека покупателю
     *
     * обязательное да
     * String(64)
     *
     * @var string
     */
    private $email;

    /**
     * Телефон покупателя
     *
     * обязательное нет
     * String(64)
     *
     * @var null|string
     */
    private $phone;

    /**
     * Система налогообложения.
     *
     * обязательное да
     *
     * @var string
     */
    private $taxation;

    public static function create(CashDocument $cashDocument, Tinkoff $tinkoff): self
    {
        $payer = $cashDocument->getPayer();

        $receipt = new Receipt();
        $receipt->setEmail($payer->getEmail());
        $receipt->setTaxation($tinkoff->getTaxationSystemCode());

        $payerPhone = $payer->getPhone();
        if ($payerPhone !== null) {
            $receipt->setPhone($payerPhone);
        }

        $tax = $tinkoff->getTaxationRateCode();

        $order = $cashDocument->getOrder();
        /** @var Package $package */
        foreach ($order->getPackages() as $package) {
            $name = 'Проживание в номере "%1$s".';

            $item = new Item();
            $item->setName(sprintf($name, $package->getRoomType()->getName()));
            $item->setPrice($package->getPackagePrice(true) * 100);
            $item->setQuantity(1);
            $item->setAmount($package->getPackagePrice(true) * 100);
            $item->setTax($tax);

            $receipt->addItems($item);

            /** @var PackageService $service */
            foreach ($package->getServices() as $service) {
                $quantity = $service->getAmount() * $service->getNights() * $service->getPersons();

                $item = new Item();
                $item->setName('Услуга: ' . $service->getService()->getName());
                $item->setPrice($service->getPrice() * 100);
                $item->setQuantity($quantity);
                $item->setAmount($quantity * $item->getPrice());
                $item->setTax($tax);

                $receipt->addItems($item);
            }
        }

        return $receipt;
    }

    /**
     * @return Item[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @param Item $item
     */
    public function addItems(Item $item): void
    {
        $this->items[] = $item;
    }

    /**
     * @param Item[] $items
     */
    public function setItems(array $items): void
    {
        $this->items = $items;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return null|string
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * @param null|string $phone
     */
    public function setPhone(?string $phone): void
    {
        $this->phone = $phone;
    }

    /**
     * @return string
     */
    public function getTaxation(): string
    {
        return $this->taxation;
    }

    /**
     * @param string $taxation
     */
    public function setTaxation(string $taxation): void
    {
        $this->taxation = $taxation;
    }

    public function jsonSerialize()
    {
        $data = [
            'Email'    => $this->getEmail(),
            'Taxation' => $this->getTaxation(),
            'Items'    => $this->getItems(),
        ];

        if ($this->getPhone() !== null) {
            $data['Phone'] = $this->getPhone();
        }

        return $data;
    }
}