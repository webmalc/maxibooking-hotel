<?php
/**
 * Created by PhpStorm.
 * Date: 08.06.18
 */

namespace MBH\Bundle\ClientBundle\Service\PaymentSystem;


use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\NewRbk\InvoiceResponse;
use MBH\Bundle\PackageBundle\Document\Package;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
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

    public function errorInCurl(string $err): self
    {
        $this->loggerErrorInCurl($err);
        $this->data = ['error' => $err];

        return $this;
    }

    public function get($rawResponse, Package $package, CashDocument $cashDocument): self
    {
        $r = InvoiceResponse::load(json_decode($rawResponse, true));

        if ($r->isSuccess()) {
            $this->loggerSuccess($package);
            $this->dataIfOk($r, $package, $cashDocument);
        } else {
            $this->loggerFailed($package, $r);
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

    private function loggerErrorInCurl(string $err): void
    {
        $this->logger( 'Curl error: ' . $err);
    }

    private function loggerSuccess(Package $package): void
    {
        $this->logger('packageId = ' . $package->getId(), true);
    }

    private function loggerFailed(Package $package, InvoiceResponse $invoiceResponse): void
    {
        $msg = 'packageId = ' . $package->getId() . 'Error: ' .  $invoiceResponse->getError()->getInfo();

        $this->logger($msg);
    }

    private function logger(string $msg, bool $isSuccess = false): void
    {
        $logger = $this->container->get('mbh.new_rbk_create_invoice.logger');

        $prefix = 'Ok.';

        if (!$isSuccess) {
            $prefix = 'FAIL.';
        }

        $logger->info($prefix . 'Create Invoice.' . $msg);
    }

}