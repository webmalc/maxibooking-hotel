<?php
/**
 * Created by PhpStorm.
 * Date: 07.06.18
 */

namespace MBH\Bundle\ClientBundle\Lib\PaymentSystem\NewRbk;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\ClientBundle\Document\ClientConfig;
use MBH\Bundle\ClientBundle\Document\NewRbk;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\PackageService;

/**
 * Class InvoiceRequest
 * @package MBH\Bundle\ClientBundle\Lib\PaymentSystem\NewRbk
 *
 * @see https://v1.api.developer.rbk.money/#operation/createInvoice
 */
class InvoiceRequest extends InvoiceCommon implements \JsonSerializable
{
    /**
     * @param DocumentManager $documentManager
     * @param ClientConfig $config
     * @param Package $package
     * @param CashDocument $cashDocument
     * @return InvoiceRequest
     */
    public static function create(
        DocumentManager $documentManager,
        ClientConfig $config,
        Package $package,
        CashDocument $cashDocument
    )
    {
        $newRbk = $config->getNewRbk();

        $dueDate = new \DateTime('+' . $newRbk->getLifetimeInvoice() . 'days');

        $self = new self();
        $self->setShopID($newRbk->getShopId());
        $self->setDueDate($dueDate->format(DATE_ATOM)) ;
        $self->setProduct($cashDocument->getPayer()->getName() . ', ' . $package->getNumberWithPrefix());
        $self->setCurrency($config->getCurrency());
        $self->setCashDocumentId($cashDocument);
//        $self->setMetadata(['cashId' => $cashDocument->getId()]);
        $self->setDescription($package);
        $self->setAmount($cashDocument->getTotal());

        $self->setCart($newRbk->isWithFiscalization(), $cashDocument->getOrder(), $newRbk);

        return $self;
    }

    public function setCashDocumentId(CashDocument $cashDocument): void
    {
        $this->setMetadata(['cashId' => $cashDocument->getId()]);
    }

    /**
     * @return array|mixed
     */
    public function jsonSerialize()
    {
        $data = [
            "shopID"      => $this->getShopId(),
            'dueDate'     => $this->getDueDate(),
            'amount'      => $this->getAmount(),
            'currency'    => $this->getCurrency(),
            'product'     => $this->getProduct(),
            'description' => $this->getDescription(),
            'metadata'    => $this->getMetadata(),
        ];

        $cart = $this->getCart();

        if ($cart !== []) {
            $data['cart'] = $cart;
        }

        return $data;
    }

    /**
     * @return string
     */
    public function getShopID()
    {
        return $this->shopID;
    }

    /**
     * @param string $shopID
     */
    public function setShopID(string $shopID): void
    {
        $this->shopID = $shopID;
    }

    /**
     * @param string $dueDate
     */
    public function setDueDate(string $dueDate): void
    {
        $this->dueDate = $dueDate;
    }

    /**
     * @param int $amount
     */
    public function setAmount(float $amount): void
    {
        $this->amount = $amount * 100;
    }

    /**
     * @param string $currency
     */
    public function setCurrency(string $currency): void
    {
        if (preg_match('/^[A-Z]{3}$/', strtoupper($currency), $match)) {
            $this->currency = $match[0];
        } else {
            $this->currency = 'RUB';
        }
    }

    /**
     * @param string $product
     */
    public function setProduct(string $product): void
    {
        $this->product = $product;
    }

    /**
     * @param string $description
     */
    public function setDescription(Package $package): void
    {
        $this->description = function ($location) use ($package) {
            $begin = $package->getBegin();
            $end = $package->getEnd();

            $msg = 'Проживание в ';
            if ($location === 'room') {
                $msg .= $package->getRoomType()->getName();
            } else {
                $msg .= $package->getHotel()->getName();
            }
            $msg .= ' c ' . $begin->format('d.m.Y') . ' по ' . $end->format('d.m.Y');

            return $msg;
        };
    }

    /**
     * @return string
     */
    public function getDescription($location = 'hotel'): string
    {
        $desc = $this->description;

        return $desc($location);
    }

    /**
     * cart формируется если включена "фискализацией платежа"
     *
     * @param Cart[] $cart
     */
    public function setCart(bool $needCart, Order $order, NewRbk $newRbk): void
    {
        $cart = [];

        if ($needCart) {
            $dataForTaxMode = ['rate' => $newRbk->getTaxationRateCode()];

            $taxMode = TaxMode::create($dataForTaxMode);

            foreach ($order->getPackages() as $package) {
                $c = new Cart();
                $c->setProduct($this->getDescription('room'));
                $c->setPrice($package->getOrder()->getPrice()*100);
                $c->setTaxMode($taxMode);
                $c->setCost($c->getPrice()*$c->getQuantity());

                $cart[] = $c;

                /** До выяснения подробностей */
//                /** @var PackageService $packageService */
//                foreach ($package->getServices() as $packageService) {
//                    $quantity = $packageService->getAmount() * $packageService->getNights() * $packageService->getPersons();
//
//                    $c = new Cart();
//                    $c->setProduct($packageService->getService()->getName());
//                    $c->setPrice($packageService->getPrice()*100);
//                    $c->setQuantity($quantity);
//                    $c->setTaxMode($taxMode);
//                    $c->setCost($c->getPrice()*$c->getQuantity());
//
//                    $cart[] = $c;
//                }
            }
        }

        $this->cart = $cart;
    }

    /**
     * @param mixed $metadata
     */
    private function setMetadata($metadata): void
    {
        $this->metadata = $metadata;
    }
}