<?php
/**
 * Created by PhpStorm.
 * Date: 08.06.18
 */

namespace MBH\Bundle\ClientBundle\Service\PaymentSystem;


use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\NewRbk\InvoiceResponse;
use MBH\Bundle\PackageBundle\Document\Package;
use Psr\Container\ContainerInterface;

/**
 * TODO добавить логирование ошибок
 *
 * Class NewRbkCreateInvoiceResponse
 * @package MBH\Bundle\ClientBundle\Service\PaymentSystem
 */
class NewRbkCreateInvoiceResponse implements \JsonSerializable
{
    /**
     * @var boolean
     */
    private $created = false;

    /**
     * @var array
     */
    private $data;

    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function errorInCurl(int $err): self
    {
        $this->data = ['error' => $err];

        return $this;
    }

    public function get($rawResponse, Package $package, CashDocument $cashDocument): self
    {
        $r = InvoiceResponse::load(json_decode($rawResponse, true));

        if ($r->isSuccess()) {
            $this->dataIfOk($r, $package, $cashDocument);
        } else {
            $this->data = ['error' => $r->getError()->getInfo()];
        }

        return $this;
    }

    public function arrayData(): array
    {
        return [
            'status' => $this->created,
            'data'   => $this->data,
        ];
    }

    public function jsonSerialize()
    {
        return $this->arrayData();
    }

    /**
     * @return bool
     */
    public function isCreated(): bool
    {
        return $this->created;
    }

    private function dataIfOk(InvoiceResponse $dataResponse, Package $package, CashDocument $cashDocument)
    {
        $this->created = true;

        $data = [
            'invoiceID'          => $dataResponse->getId(),
            'invoiceAccessToken' => $dataResponse->getInvoiceAccessToken(),
            'name'               => $package->getHotel()->getFullTitle(),
            'obscureCardCvv'     => true,
            'requireCardHolder'  => true,
            'description'        => $dataResponse->getDescription(),
        ];

        $payer = $cashDocument->getPayer();

        if ($payer !== null) {
            $data['email'] = $payer->getEmail();
        }

        $this->data = $data;
    }

}