<?php

namespace MBH\Bundle\CashBundle\Service;

use MBH\Bundle\BaseBundle\Document\NotificationType;
use MBH\Bundle\CashBundle\Document\CashDocument;
use Symfony\Component\DependencyInjection\ContainerInterface;


class Cash
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Not confirmed user cash docs
     * @return array
     */
    public function notConfirmedCashDocuments()
    {
        $result = [
            'count' => 0,
            'totalIn' => 0,
            'totalOut' => 0,
            'total' => 0,
            'docs' => []
        ];

        $ch = $this->container->get('security.authorization_checker');
        if (!$ch->isGranted('IS_AUTHENTICATED_FULLY') && !$ch->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $result;
        }

        $docs =  $this->container
            ->get('doctrine_mongodb')
            ->getRepository('MBHCashBundle:CashDocument')
            ->findBy([
                'isConfirmed' => false,
                'method' => 'cash',
                'isPaid' => true,
                'deletedAt' => null,
                'createdBy' => $this->container->get('security.token_storage')->getToken()->getUser()->getUsername()
            ])
        ;

        /** @var CashDocument $cash */
        foreach ($docs as $cash) {
            if ($cash->getOperation() == 'in') {
                $result['totalIn'] += $cash->getTotal();
            } else {
                $result['totalOut'] += $cash->getTotal();
            }
        }

        $result['count'] = count($docs);
        $result['docs'] = $docs;
        $result['total'] = $result['totalIn'] - $result['totalOut'];

        return $result;
    }

    public function sendMailAtCashDocumentConfirmation(CashDocument $cashDocument)
    {
        $notifier = $this->container->get('mbh.notifier.mailer');
        $message = $notifier::createMessage();

        $clientConfig = $this->container->get('mbh.client_config_manager')->fetchConfig();

        $localCurrency = $clientConfig->getCurrency();

        $currencyText = $this->container->getParameter('mbh.currency.data')[$localCurrency]['text'];
        $sumString = '<strong>' . $cashDocument->getTotal() . ' ' . $currencyText . '</strong>';

        $prependText = '<span style="font-size: 18px">'
           . $this->container->get('translator')->trans('mailer.order.prepend_text', ['%paymentSum%' => $sumString])
            . '</span>';


        $helper = $this->container->get('mbh.helper');
        $order = $helper->getWithoutFilter(function () use ($cashDocument) {
            return $cashDocument->getOrder();
        });
        $hotel = $helper->getWithoutFilter(function() use ($order) {
            return $order->getFirstHotel();
        });

        $message
            ->setRecipients([$order->getPayer()])
            ->setFrom('system')
            ->setType('info')
            ->setLink('hide')
            ->setCategory('tourists')
            ->setHotel($hotel)
            ->setOrder($order)
            ->setSubject('mailer.order.subject_text')
            ->setHeaderText('mailer.order.header_text')
            ->setTranslateParams([
                '%hotelName%' => $hotel->getName(),
                '%sum%' => $sumString
            ])
            ->setAdditionalData([
                'prependText' => $prependText,
                'currencyText' => $currencyText
            ])
            ->setTemplate('MBHBaseBundle:Mailer:cashDocConfirmation.html.twig')
            ->setEnd(new \DateTime('+1 minute'))
            ->setMessageType(NotificationType::CASH_DOC_CONFIRMATION_TYPE)
        ;

        $notifier
            ->setMessage($message)
            ->notify();
    }
}