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
    protected $noteMessages = [];

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
    abstract public function isHandleAsNew(?Order $order) : bool ;

    /**
     * Обрабатывать ли данный заказ как измененный?
     * @param Order $order
     * @return bool
     */
    abstract public function isHandleAsModified(?Order $order) : bool ;

    /**
     * Обрабатывать ли данный заказ как законченный
     * @param Order $order
     * @return bool
     */
    abstract public function isHandleAsCancelled(?Order $order) : bool;

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

    protected function addProblemMessage($transIdentifier, $params = [])
    {
        return $this->addMessage('problems', $transIdentifier, $params);
    }

    protected function addNotifyMessage($transIdentifier, $params = [])
    {
        return $this->addMessage('notifications', $transIdentifier, $params);
    }

    private function addMessage($status, $transIdentifier, $params)
    {
        $this->noteMessages[$status] = [
            'identifier' => $transIdentifier,
            'params' => $params
        ];

        return $this;
    }

    public function getPackageAndOrderMessages()
    {
        $messages = array_slice($this->noteMessages, 0);
        foreach ($this->getPackagesData() as $packageInfo) {
            $packageMessages = $packageInfo->getMessages();
            if (count($packageMessages) > 0) {
                if (count($packageMessages['problems']) > 0) {
                    $messages['problems'][] = $packageMessages['problems'];
                }
                if (count($packageMessages['notifications'])) {
                    $messages['notifications'][] = $packageMessages['notifications'];
                }
            }
        }

        return $messages;
    }

    public function getMessages() : array
    {
        return $this->noteMessages;
    }

    protected function addOrderNote($note, $preface = null) : string
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