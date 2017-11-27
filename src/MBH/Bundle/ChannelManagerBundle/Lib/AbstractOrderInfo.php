<?php

namespace MBH\Bundle\ChannelManagerBundle\Lib;

use MBH\Bundle\PackageBundle\Document\CreditCard;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\PackageService;
use MBH\Bundle\PackageBundle\Document\PackageSource;
use MBH\Bundle\PackageBundle\Document\Tourist;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class AbstractOrderInfo
{
    /** @var  ContainerInterface $container */
    protected $container;
    /** @var  DocumentManager $dm */
    protected $dm;
    protected $translator;
    protected $orderNote = '';

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->dm = $container->get('doctrine_mongodb')->getManager();
        $this->translator = $container->get('translator');
    }
    abstract public function getPayer() : Tourist;
    abstract public function getChannelManagerOrderId() : string;
    abstract public function getPrice();
    abstract public function getCashDocuments(Order $order);
    abstract public function getSource() : ?PackageSource;

    /**
     * Возвращает массив объектов, хранящих данные о бронях в заказе
     * @return AbstractPackageInfo[]
     */
    abstract public function getPackagesData();

    /**
     * @return PackageService[]
     */
    abstract public function getServices();

    /**
     * Возвращает данные о кредитной карте, если указаны.
     * @return CreditCard|null
     */
    abstract public function getCreditCard();
    abstract public function getChannelManagerName() : string;
    abstract public function isOrderModified() : bool;
    abstract public function isOrderCreated() : bool;
    abstract public function isOrderCancelled() : bool;

    /**
     * Обрабатывать ли данный заказ как новый?
     * @param Order $order
     * @return bool
     */
    abstract public function isHandledAsNew(?Order $order) : bool ;

    /**
     * Обрабатывать ли данный заказ как измененный?
     * @param Order $order
     * @return bool
     */
    abstract public function isHandledAsModified(?Order $order) : bool ;

    /**
     * Обрабатывать ли данный заказ как законченный
     * @param Order $order
     * @return bool
     */
    abstract public function isHandledAsCancelled(?Order $order) : bool;

    abstract public function getNote() : string;

    /**
     * Возвращает время изменения заказа.
     * @return \DateTime|null
     */
    public function getModifiedDate() : ?\DateTime
    {
        return null;
    }

    public function getOriginalPrice()
    {
        return $this->getPrice();
    }

    protected function addOrderNote($note, $preface) : string
    {
        $note = trim($note);
        if ($note !== '') {
            if ($preface) {
                $note = $this->translator->trans($preface) . ': ' . $note;
            }
            $this->orderNote .= $note . "\n";
        }

        return $this->orderNote;
    }
}