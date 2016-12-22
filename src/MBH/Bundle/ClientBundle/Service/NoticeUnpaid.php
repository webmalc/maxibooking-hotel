<?php

namespace MBH\Bundle\ClientBundle\Service;

use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use MBH\Bundle\BaseBundle\Lib\Exception;
use MBH\Bundle\BaseBundle\Service\Messenger\Notifier;

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

    public function __construct(ManagerRegistry $dm, Notifier $notifier)
    {
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
        $dateUnpaid = $this->dm->getRepository('MBHClientBundle:ClientConfig')
            ->fetchConfig()
            ->getNoticeUnpaid();

        // Date. Late payments
        $deadlineDate = $currentDay->modify("-{$dateUnpaid} day");

        $unpaidOrders = $this->dm
            ->getRepository('MBHPackageBundle:Order')
            ->getUnpaidOrders($deadlineDate);

        return array_filter($unpaidOrders, function($order) {
            return array_reduce($order->getPackages()->toArray(), function(&$res, $item) {
                $price = $item->getPrice();
                $paid = $item->getPaid();
                $percentageValue = $item->allowPercentagePrice($price);

                return ($paid < $price) && ($percentageValue >= $paid);
            }, 0);
        });

    }

    /**
     * Send message to email. Notice of unpaid
     * todo add ro RabbitMQ
     *
     * @return array|bool
     */
    public function sendNotice($arrayData)
    {
        $message = $this->notifier->createMessage();

        try {
            $message
                ->setText('hello world!')
                ->setFrom('system')
                ->setSubject('Hello world')
                ->setType('info')
                ->setCategory('notification')
                ->setAutohide(false)
                ->setEnd(new \DateTime('+1 minute'));

            $this->notifier
                ->setMessage($message)
                ->notify();

        } catch (Exception $e) {
            return false;
        }
    }
}