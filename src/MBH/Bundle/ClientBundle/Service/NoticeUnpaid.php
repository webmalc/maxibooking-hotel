<?php

namespace MBH\Bundle\ClientBundle\Service;

use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use MBH\Bundle\BaseBundle\Lib\Exception;
use MBH\Bundle\BaseBundle\Service\Messenger\Notifier;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Translation\DataCollectorTranslator;

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

    /**
     * @var DataCollectorTranslator
     */
    protected $translator;

    public function __construct(ContainerInterface $container, ManagerRegistry $dm, Notifier $notifier, DataCollectorTranslator $translator)
    {
        $this->container = $container;
        $this->notifier = $notifier;
        $this->translator = $translator;
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
     * Get unpaid order array of next element: (order.paid, order.price, order.id, package.id, tourist.phone, tourist.mobilePhone)
     *
     * @param $arrayData NoticeUnpaid unpaidOrder
     * @return array
     */
    public function getUnpaidOrderArray($arrayData)
    {

        $packages = $this->dm->getRepository('MBHPackageBundle:Package')->findAll();
        $unpaidOrderArray = [];

        foreach ($packages as $package) {

            $orderId = $package->getOrder()->getId();

            if(isset($arrayData[$orderId])) {
                $unpaidOrderArray[] = [
                    'orderId' => $arrayData[$orderId]->getId(),
                    'packageId' => $package->getId(),
                    'numberWithPrefix' => $package->getNumberWithPrefix(),
                    'begin' => $package->getBegin(),
                    'end' => $package->getEnd(),
                    'price' => $arrayData[$orderId]->getPrice ?? $arrayData[$orderId]->getPrice(),
                    'paid' => $arrayData[$orderId]->getPaid(),
                    'packageCreatedAt' => $package->getCreatedAt(),
                    'phone' => !is_null($package->getPayer()) ? $package->getPayer()->getPhone() : "",
                    'mobilePhone' => !is_null($package->getPayer()) ? !empty($package->getPayer()->getMobilePhone()) : "",
                ];
            }

        }
        return $unpaidOrderArray;
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
                        'orders' => $this->getUnpaidOrderArray($arrayData)
                    ])
                    ->setEnd(new \DateTime('+1 minute'));


                $this->notifier
                    ->setMessage($message)
                    ->notify();

            } catch (Exception $e) {
                return false;
            }
        }
    }
}