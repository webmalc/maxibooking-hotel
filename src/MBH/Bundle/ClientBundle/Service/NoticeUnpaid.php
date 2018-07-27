<?php

namespace MBH\Bundle\ClientBundle\Service;

use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use MBH\Bundle\BaseBundle\Document\NotificationType;
use MBH\Bundle\BaseBundle\Lib\Exception;
use MBH\Bundle\BaseBundle\Service\Messenger\Notifier;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\Package;
use Symfony\Component\DependencyInjection\ContainerInterface;

class NoticeUnpaid
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @var ManagerRegistry
     */
    protected $dm;

    /**
     * @var Notifier
     */
    protected $notifier;

    public function __construct(ContainerInterface $container, ManagerRegistry $dm, Notifier $notifier)
    {
        $this->container = $container;
        $this->notifier = $notifier;
        $this->dm = $dm->getManager();
    }

    /**
     * Orders are expected to notice
     *
     * @return array
     */
    public function unpaidOrder()
    {
        $currentDay = new \DateTime('midnight'); // Current day

        // Number of days after which a payment is considered overdue
        $dateUnpaid = $this->container
            ->get('mbh.client_config_manager')
            ->fetchConfig()
            ->getNoticeUnpaid();

        // Date. Late payments
        $deadlineDate = $currentDay->modify("-{$dateUnpaid} day");

        $unpaidOrders = $this->dm
            ->getRepository('MBHPackageBundle:Order')
            ->getUnpaidOrders($deadlineDate);

        return array_filter($unpaidOrders, function($order) {
            /** @var Order $order  */
            return array_reduce($order->getPackages()->toArray(), function(&$res, $item) {
                /** @var Package $item */
                $price = $item->getPrice();
                $paid = $item->getPaid();
                $percentageValue = $item->allowPercentagePrice($price);

                return ($paid < $price) && ($percentageValue >= $paid);
            }, 0);
        });

    }

    /**
     * Send message to email. Notice of unpaid
     *
     * @return array|bool
     */
    public function sendNotice($arrayData = null)
    {

        if (!$arrayData) {
            $arrayData = $this->unpaidOrder();
        }

        $message = $this->notifier->createMessage();

        if(!empty($arrayData)) {
            try {
                $message
                    ->setFrom('system')
                    ->setSubject('mailer.notice.unpaid.order.list')
                    ->setText('mailer.notice.unpaid.order.list')
                    ->setType('info')
                    ->setCategory('notification')
                    ->setAutohide(false)
                    ->setTemplate('MBHClientBundle:Mailer:notice.html.twig')
                    ->setAdditionalData([
                        'orders' => $arrayData
                    ])
                    ->setEnd(new \DateTime('+1 minute'))
                    ->setMessageType(NotificationType::UNPAID_TYPE)
                ;


                $this->notifier
                    ->setMessage($message)
                    ->notify();

            } catch (Exception $e) {
                return false;
            }
        }
    }
}