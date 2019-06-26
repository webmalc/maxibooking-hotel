<?php

namespace MBH\Bundle\PackageBundle\Command;

use MBH\Bundle\BaseBundle\Document\NotificationType;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\PackageRepository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class MailerCommand extends ContainerAwareCommand
{
    /**
     * @var \Doctrine\ODM\MongoDB\DocumentManager
     */
    private $dm;

    protected function configure()
    {
        $this
            ->setName('mbh:package:mailer')
            ->setDescription('Send report mails')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $start = new \DateTime();
        $this->dm = $this->getContainer()->get('doctrine_mongodb')->getManager();
        $helper = $this->getContainer()->get('mbh.helper');
        $notifier = $this->getContainer()->get('mbh.notifier.mailer');
        $router = $this->getContainer()->get('router');
        $clientConfig = $this->getContainer()->get('mbh.client_config_manager')->fetchConfig();

        if (!$this->dm->getFilterCollection()->isEnabled('softdeleteable')) {
            $this->dm->getFilterCollection()->enable('softdeleteable');
        }

        $yesterday = new \DateTime('midnight - 1 day');
        $now = new \DateTime('midnight');
        $tomorrow = new \DateTime('midnight + 1 day');
        $dayAfterTomorrow =  new \DateTime('midnight + 2 days');

        /** @var PackageRepository $repo */
        $repo = $this->dm->getRepository('MBHPackageBundle:Package');

        //begin tomorrow report
        $packages = $repo->createQueryBuilder()
            ->field('begin')->gte($tomorrow)
            ->field('begin')->lt($dayAfterTomorrow)
            ->getQuery()
            ->execute();
        ;

        $translatedTransferCategory = $this->getContainer()->get('translator')
            ->trans('price.datafixtures.mongodb.servicedata.transfer');

        $transferCategories = $this->dm->getRepository('MBHPriceBundle:ServiceCategory')->findBy([
           '$or' => [['fullTitle' => $translatedTransferCategory], ['title' => $translatedTransferCategory]],
           'isEnabled' => true
        ]);
        $transferServices = $this->dm->getRepository('MBHPriceBundle:Service')->findBy([
                'category.id' => ['$in' => $helper->toIds($transferCategories)],
                'isEnabled' => true
            ]
        );

//        $this->sendDailyReportMail();

        $packageTransfers = $this->dm->getRepository('MBHPackageBundle:PackageService')
            ->createQueryBuilder('s')
            ->field('begin')->gte($tomorrow)
            ->field('begin')->lt($dayAfterTomorrow)
            ->field('service.id')->in($helper->toIds($transferServices))
            ->sort('service.id')
            ->getQuery()
            ->execute();

        if (count($packageTransfers) || count($packages)) {
            $message = $notifier::createMessage();
            $message
                ->setText('hide')
                ->setFrom('report')
                ->setSubject('mailer.report.arrival.subject')
                ->setType('info')
                ->setCategory('report')
                ->setAdditionalData([
                    'packages' => $packages,
                    'transfers' => $packageTransfers,
                ])
                ->setTemplate('MBHBaseBundle:Mailer:reportArrival.html.twig')
                ->setAutohide(false)
                ->setEnd(new \DateTime('+1 minute'))
                ->setMessageType(NotificationType::ARRIVAL_TYPE)
            ;
            $notifier
                ->setMessage($message)
                ->notify()
            ;
        }

        //begin tomorrow users
        if (count($packages)
            && $clientConfig->isNotificationTypeExists(NotificationType::ARRIVAL_TYPE)
        ) {
            foreach ($packages as $package) {
                $payer = $package->getOrder()->getPayer();
                if (!$payer || !$payer->getEmail()) {
                    continue;
                }

                $hotel = $package->getRoomType()->getHotel();
                $message = $notifier::createMessage();

                $message
                    ->setFrom('report')
                    ->setSubject('mailer.user.arrival.subject')
                    ->setType('info')
                    ->setCategory('user')
                    ->setHotel($hotel)
                    ->setOrder($package->getOrder())
                    ->setAdditionalData([
                        'package' => $package,
                        'fromText' => $package->getRoomType()->getHotel()
                    ])
                    ->setTemplate('MBHBaseBundle:Mailer:userArrival.html.twig')
                    ->setAutohide(false)
                    ->setEnd(new \DateTime('+1 minute'))
                    ->addRecipient($payer)
                    ->setLink('hide')
                    ->setSignature('mailer.online.user.signature')
                    ->setMessageType(NotificationType::ARRIVAL_TYPE)
                ;
                $notifier
                    ->setMessage($message)
                    ->notify()
                ;
            }
        }

        //user polls
        $packages = $repo->createQueryBuilder()
            ->field('end')->gte($yesterday)
            ->field('end')->lt($now)
            ->getQuery()
            ->execute();

        if (count($packages)
            && $clientConfig->isNotificationTypeExists(NotificationType::FEEDBACK_TYPE)
        ) {
            /** @var Package $package */
            foreach ($packages as $package) {
                $order = $package->getOrder();
                $payer = $order->getPayer();
                if (!$payer || !$payer->getEmail() || count($order->getPollQuestions())) {
                    continue;
                }

                $hotel = $package->getRoomType()->getHotel();
                $message = $notifier::createMessage();

                $link = $router->generate('online_poll_list', [
                    'id' => $order->getId(),
                    'payerId' => $order->getPayer()->getId()
                ], $router::ABSOLUTE_URL);

                if (!empty($hotel->getPollLink())) {
                    $link = $hotel->getPollLink() . '?link=' . $link;
                }

                $message
                    ->setFrom('system')
                    ->setSubject('mailer.report.user.poll.subject')
                    ->setTemplate('MBHBaseBundle:Mailer:dayAfterOfCheckOut.html.twig')
                    ->setType('info')
                    ->setCategory('notification')
                    ->setOrder($order)
                    ->setAdditionalData([
                        'prependText' => 'mailer.online.user.poll.prepend',
                        'appendText' => 'mailer.online.user.poll.append',
                        'image' => 'stars_but.png',
                        'fromText' => $order->getFirstHotel()
                    ])
                    ->setHotel($hotel)
                    ->setTemplate('MBHBaseBundle:Mailer:base.html.twig')
                    ->setAutohide(false)
                    ->setEnd(new \DateTime('+1 minute'))
                    ->addRecipient($order->getPayer())
                    ->setLink($link)
                    ->setLinkText('mailer.online.user.poll.link')
                    ->setSignature('mailer.online.user.signature')
                    ->setMessageType(NotificationType::FEEDBACK_TYPE)
                ;
                $notifier
                    ->setMessage($message)
                    ->notify()
                ;
            }
        }

        $time = $start->diff(new \DateTime());
        $output->writeln('Installing complete. Elapsed time: ' . $time->format('%H:%I:%S'));
    }
//
//    private function sendDailyReportMail()
//    {
//        $hotels = $this->dm->getRepository('MBHHotelBundle:Hotel')->findAll();
//
//        $clientConfig = $this->dm->getRepository('MBHClientBundle:ClientConfig')->fetchConfig();
//        //TODO: Сменить
//        $begin = $clientConfig->getBeginDate() ?? new \DateTime('midnight');
//        $end = (clone $begin)->modify('+45 days');
//        $report = $this->getContainer()->get('mbh.packages_daily_report_compiler')
//            ->generate($begin, $end,  $hotels, true)
//            ->setTitle($this->getContainer()->get('translator')->trans('views.report.packages_daily_report.title'));
//
//        $notifier = $this->getContainer()->get('mbh.notifier.mailer');
//        $message = $notifier::createMessage();
//        $message
//            ->setFrom('report')
//            ->setSubject('views.report.packages_daily_report.mail_title')
//            ->setType('info')
//            ->setTemplate('MBHBaseBundle:Report:report_mail.html.twig')
//            ->setAdditionalData(['report' => $report])
//            ->setAutohide(false)
//            ->setEnd(new \DateTime('+1 minute'))
//            ->setMessageType(NotificationType::DAILY_REPORT_TYPE)
//        ;
//
//        $notifier
//            ->setMessage($message)
//            ->notify()
//        ;
//    }
}
