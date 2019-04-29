<?php


namespace MBH\Bundle\OnlineBookingBundle\Service;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Service\Messenger\Notifier;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\OnlineBookingBundle\Lib\OnlineNotifyRecipient;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PriceBundle\Document\Special;
use MBH\Bundle\PriceBundle\Document\Tariff;
use Monolog\Logger;
use Symfony\Component\Translation\Translator;

/**
 * Class OnlineBookingNotificator
 * @package MBH\Bundle\OnlineBookingBundle\Service
 */
class OnlineBookingNotificator
{
    /** @var Logger */
    private $logger;

    /** @var Notifier */
    private $notifier;

    /** @var Notifier */
    private $mailer;

    /** @var Translator */
    private $translator;

    /** @var array */
    private $arrivalLinks;

    /** @var DocumentManager */
    private $dm;

    /** @var string */
    private $managerEmail;

    /**
     * OnlineBookingNotificator constructor.
     * @param Logger $logger
     * @param Notifier $notifier
     * @param Notifier $mailer
     * @param Translator $translator
     * @param array $arrivalLinks
     * @param string $managerEmail
     * @param DocumentManager $dm
     */
    public function __construct(
        Logger $logger,
        Notifier $notifier,
        Notifier $mailer,
        Translator $translator,
        array $arrivalLinks,
        string $managerEmail,
        DocumentManager $dm
    ) {
        $this->logger = $logger;
        $this->notifier = $notifier;
        $this->mailer = $mailer;
        $this->translator = $translator;
        $this->arrivalLinks = $arrivalLinks;
        $this->managerEmail = $managerEmail;
        $this->dm = $dm;
    }


    /**
     * @param Order $order
     * @throws \Exception
     */
    public function newOrderNotify(Order $order): void
    {

        $this->logger->info('New order created!', [$order]);


        //backend
        $message = $this->notifier::createMessage();
        $hotel = $order->getPackages()[0]->getRoomType()->getHotel();

        $message
            ->setText($this->translator->trans('mailer.online.backend.text', ['%orderID%', $order->getId()]))
            ->setFrom('online_form')
            ->setSubject('mailer.online.backend.subject')
            ->setType('info')
            ->setCategory('notification')
            ->setOrder($order)
            ->setHotel($hotel)
            ->setTemplate('MBHBaseBundle:Mailer:order.html.twig')
            ->setAutohide(false)
            ->setEnd(new \DateTime('+1 minute'));

        $this->notifier
            ->setMessage($message)
            ->notify();

        //user
        $payer = $order->getPayer();
        if ($payer && $payer->getEmail()) {
            $message = $this->mailer::createMessage();
            $message
                ->setFrom('online_form')
                ->setSubject('mailer.online.user.subject')
                ->setType('info')
                ->setCategory('notification')
                ->setOrder($order)
                ->setAdditionalData(
                    [
                        'prependText' => 'mailer.online.user.prepend',
                        'appendText' => 'mailer.online.user.append',
                        'fromText' => $hotel->getName(),
                    ]
                )
                ->setHotel($hotel)
                ->setTemplate('MBHBaseBundle:Mailer:order.html.twig')
                ->setAutohide(false)
                ->setEnd(new \DateTime('+1 minute'))
                ->addRecipient($payer)
                ->setLink('hide')
                ->setSignature('mailer.online.user.signature');


            if (!empty($this->arrivalLinks['map'])) {
                $message->setLink($this->arrivalLinks['map'])
                    ->setLinkText($this->translator->trans('mailer.online.user.map'));
            }

            $this->mailer
                ->setMessage($message)
                ->notify();
        }


    }

    /**
     * @param array $data
     * @throws \Doctrine\ODM\MongoDB\LockException
     * @throws \Doctrine\ODM\MongoDB\Mapping\MappingException
     * @throws \Exception
     */
    public function reservationNotify(array $data): void
    {
        $this->logger->info('New RESERVE from azovsky.ru', $data);
        $message = $this->notifier::createMessage();

        $roomType = $this->dm->find(RoomType::class, $data['packages'][0]['roomType']);
        $tariff = $this->dm->find(Tariff::class, $data['packages'][0]['tariff']);
        $hotel = $roomType->getHotel();

        $special = null;
        if ($specialId = $data['special'] ?? null) {
            $special = $this->dm->find(Special::class, $specialId);
        }

        $recipient = new OnlineNotifyRecipient();
        $recipient->setEmail($this->managerEmail);
        $managerTemplate = $special ? 'MBHOnlineBookingBundle:Mailer:special.reservation.html.twig' : 'MBHOnlineBookingBundle:Mailer:reservation.html.twig';

        $message
            ->setRecipients([$recipient])
            ->setSubject('mailer.online.backend.reservation.subject')
            ->setText('mailer.online.backend.reservation.text')
            ->setFrom('online_form')
            ->setType('info')
            ->setCategory('notification')
            ->setAdditionalData(
                [
                    'roomType' => $roomType,
                    'tariff' => $tariff,
                    'begin' => $data['packages'][0]['begin'],
                    'end' => $data['packages'][0]['end'],
                    'client' => $data['tourist'],
                    'total' => $data['total'],
                    'package' => $data['packages'][0],
                    'special' => $special ?? null,

                ]
            )
            ->setHotel($hotel)
            ->setTemplate($managerTemplate)
            ->setAutohide(false)
            ->setEnd(new \DateTime('+1 minute'));

        /*if ($special) {
            $link = $this->specialLinkCreate($data);
            $message->setLink($link);
        }*/

        $this->notifier
            ->setMessage($message)
            ->notify();

        if ($data['tourist']['email']) {
            $clientTemplate = $special ? 'MBHOnlineBookingBundle:Mailer:special.client.reservation.html.twig' : 'MBHOnlineBookingBundle:Mailer:reservation.client.html.twig';
            $tourist = $data['tourist'];
            $recipient = new OnlineNotifyRecipient();
            $recipient
                ->setName($tourist['firstName'].' '.$tourist['lastName'])
                ->setEmail($tourist['email']);
            $message
                ->setRecipients([$recipient])
                ->setSubject('mailer.online.backend.reservation.client.subject')
                ->setText('mailer.online.backend.reservation.client.text')
                ->setTemplate($clientTemplate)
                ->addAdditionalData(
                    [
                        'hideLink' => true,
                    ]
                );
            $this->mailer
                ->setMessage($message)
                ->notify();
        }
    }
}