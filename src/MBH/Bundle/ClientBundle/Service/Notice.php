<?php

namespace MBH\Bundle\ClientBundle\Service;

use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use MBH\Bundle\BaseBundle\Lib\Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Notice
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
     * @var \Swift_Mailer
     */
    protected $mailer;

    /**
     * @var array
     */
    protected  $params;

    public function __construct(ContainerInterface $container)
    {
        $this->mailer = $container->get('mailer');
        $this->container = $container;
        $this->params = $container->getParameter('mbh.mailer');
        $this->dm = $container->get('doctrine_mongodb')->getManager();
    }

    /**
     * Orders are expected to notice
     * Returned packageId, orderId and price
     *
     * @return array
     */
    public function unpaidOrder()
    {
        $order = [];

        $currentDay = new \DateTime(); // Current day

        // Number of days after which a payment is considered overdue
        $dateUnpaid = $this->dm->getRepository('MBHClientBundle:ClientConfig')
            ->fetchConfig()
            ->getNoticeUnpaid();

        // Date. Late payments
        $deadlineDate = $currentDay->sub(new \DateInterval("P{$dateUnpaid}D"));

        $unpaidOrders = $this->dm
            ->getRepository('MBHPackageBundle:Order')
            ->getUnpaidOrders($deadlineDate);

        foreach ($unpaidOrders as $unpaidOrder) {
            $orderId = $unpaidOrder->getId(); // Order Id
            $packageByOrderId = $this->dm->getRepository('MBHPackageBundle:Package')->findOneBy(['order.id' => $orderId]); // Package by order Id
            $packageId = $packageByOrderId->getId(); // Package Id
            $tariffId = $packageByOrderId->getTariff()->getId(); // Tariff id
            $tariffById = $this->dm->getRepository('MBHPriceBundle:Tariff')->findOneBy(['id' => $tariffId]); // Search tariff
            $percent = $tariffById->getMinPerPrepay(); // Minimum prepayment percentage

            $price = $unpaidOrder->getPrice();
            $paid = $unpaidOrder->getPaid();

            if(($paid < $price) && ($percent > 0)) {
                $percentageValue = $price * $percent / 100;

                if($percentageValue >= $paid) {
                    $order[] = ['orderId' => $orderId, 'packageId' => $packageId, 'price' => $price]; // Unpaid orders
                }
            }
        }

        return $order;

    }

    /**
     * Send message to email. Notice of unpaid
     * todo Add to Cron
     *
     * @return array|bool
     */
    public function sendNotice()
    {
        $unpaidOrder = $this->unpaidOrder();

        foreach ($unpaidOrder as $itemOrder) {
            $data[] = [
                'url' => "/package/order/{$itemOrder['orderId']}/cash/{$itemOrder['packageId']}",
                'packageId' => $itemOrder['packageId'],
            ];
        }

        try {
            $message = \Swift_Message::newInstance()
                ->setSubject('Брони ожидают оплаты')
                ->setFrom($this->params['fromMail'])
                ->setTo('a.bobkov@maxi-booking.ru') // todo Specify the address of the recipient
                ->setBody(
                    $this->container->get('twig')->render(
                        'MBHClientBundle:Mailer:notice.html.twig',
                        ['arrayData' => $data]
                    ),
                    'text/html'
                );
            $this->mailer->send($message);

            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}