<?php
/**
 * Created by PhpStorm.
 * Date: 06.06.18
 */

namespace MBH\Bundle\ClientBundle\Service\PaymentSystem;


use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\ClientBundle\Document\ClientConfig;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\NewRbk\InvoiceRequest;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\Package;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class NewRbkInvoiceCreate
{
    const URL_RBK_MONEY_V_1_PROCESSING_INVOICES = "https://api.rbk.money/v1/processing/invoices";

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var \Doctrine\ODM\MongoDB\DocumentManager
     */
    private $dm;

    /**
     * @var \MBH\Bundle\ClientBundle\Document\NewRbk
     */
    private $entity;

    /**
     * @var Order
     */
    private $order;

    /**
     * @var Package
     */
    private $package;

    /**
     * @var integer
     */
    private $total;

    /**
     * @var ClientConfig
     */
    private $config;

    /**
     * @var CashDocument
     */
    private $cashDocument;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->dm = $this->container->get('doctrine.odm.mongodb.document_manager');
        $this->config = $this->dm->getRepository('MBHClientBundle:ClientConfig')->fetchConfig();
        if ($this->config->getNewRbk() === null) {
            throw new \LogicException('must not be empty');
        }

        $this->entity = $this->config->getNewRbk();
    }

    /**
     * @param Request $request
     * @return NewRbkCreateInvoiceResponse
     */
    public function getDataFromInvoice(Request $request):NewRbkCreateInvoiceResponse
    {
        $this->parseRequest($request);
        $this->checkCashDocument();

        return $this->sendQuery();
    }

    /**
     * Создаём инвойс на платворме Rbk
     *
     * @return NewRbkCreateInvoiceResponse
     */
    private function sendQuery(): NewRbkCreateInvoiceResponse
    {
        $apiKey = $this->entity->getApiKey();

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => self::URL_RBK_MONEY_V_1_PROCESSING_INVOICES,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $this->getPostFields(),
            CURLOPT_HTTPHEADER => (function () use ($apiKey) {
                $headers = [];
                $headers[] = 'X-Request-ID: ' . uniqid('mbh', true);
                $headers[] = 'Authorization: Bearer ' . $apiKey;
                $headers[] = 'Content-type: application/json; charset=utf-8';
                $headers[] = 'Accept: application/json';
                return $headers;
            })(),
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        $r = new NewRbkCreateInvoiceResponse($this->container);

        if (!empty($err)) {
            return $r->errorInCurl($err);
        }

        return $r->get($response, $this->package, $this->getCashDocument());
    }

    /**
     * @return string
     */
    private function getPostFields()
    {
        $invoice = InvoiceRequest::create($this->dm, $this->config, $this->package, $this->getCashDocument());

        return json_encode($invoice, JSON_UNESCAPED_UNICODE);
    }

    /**
     * @param Request $request
     */
    private function parseRequest(Request $request): void
    {
        $this->total = $request->get('total');

        $this->package = $this->dm->getRepository('MBHPackageBundle:Package')
            ->find($request->get('packageId'));
        $this->order = $this->package->getOrder();
    }

    /**
     * Проверяет наличие документа в случае отсутствия
     * (или если сумма из формы не равна сумме ордера)
     * формируется новый cashDocuments
     */
    private function checkCashDocument(): void
    {
        $cashDocument = null;

        if ($this->order->getCashDocuments() !== null) {
            /** @var CashDocument $cd */
            foreach ($this->order->getCashDocuments()->toArray() as $cd) {
                if ($cd->getMethod() === CashDocument::METHOD_ELECTRONIC &&
                    !$cd->getIsPaid() &&
                    $cd->getTotal() == $this->getTotal() &&
                    $cd->getOperation() === CashDocument::OPERATION_IN &&
                    $cd->getDeletedAt() === null
                ) {
                    $cashDocument = $cd;
                }
            }
        }

        if ($cashDocument === null) {
            $cashDocument = $this->generateCashDocuments();
        }

        $this->cashDocument = $cashDocument;
    }

    /**
     * @return CashDocument
     */
    private function generateCashDocuments(): CashDocument
    {
        $maxSum = $this->order->getPrice() - $this->order->getPaid();
        $total = $maxSum >= $this->getTotal() ? $this->getTotal() : $maxSum ;

        $cashDocument = new CashDocument();
        $cashDocument->setIsConfirmed(false)
            ->setIsPaid(false)
            ->setMethod(CashDocument::METHOD_ELECTRONIC)
            ->setOperation(CashDocument::OPERATION_IN)
            ->setOrder($this->order)
            ->setTotal($total);

        if ($this->order->getMainTourist() !== null) {
            $cashDocument->setTouristPayer($this->order->getMainTourist());
        } else if ($this->order->getOrganization() !== null) {
            $cashDocument->setOrganizationPayer($this->order->getOrganization());
        }

        $this->order->addCashDocument($cashDocument);
        $this->dm->persist($cashDocument);
        $this->dm->flush();

        return $cashDocument;
    }

    /**
     * @return CashDocument
     */
    private function getCashDocument(): CashDocument
    {
        return $this->cashDocument;
    }

    /**
     * @return int
     */
    private function getTotal(): int
    {
        return $this->total;
    }
}