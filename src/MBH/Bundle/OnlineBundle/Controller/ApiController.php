<?php

namespace MBH\Bundle\OnlineBundle\Controller;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\BaseBundle\Document\NotificationType;
use MBH\Bundle\BaseBundle\Lib\Exception;
use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\ClientBundle\Document\PaymentSystem\Stripe;
use MBH\Bundle\ClientBundle\Exception\BadSignaturePaymentSystemException;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\PackageBundle\Document\Order;

use MBH\Bundle\PackageBundle\Document\Package;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @Route("/api")
 */
class ApiController extends Controller
{
    /**
     * Orders xml
     * @Route("/orders/{begin}/{end}/{id}/{sign}/{type}", name="online_orders", defaults={"_format"="xml", "id"=null})
     * @Method("GET")
     * @ParamConverter("begin", options={"format": "Y-m-d"})
     * @ParamConverter("end", options={"format": "Y-m-d"})
     * @ParamConverter("hotel", class="MBH\Bundle\HotelBundle\Document\Hotel")
     * @Template()
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param Hotel $hotel
     * @param $sign
     * @param string $type
     * @return array
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function ordersAction(\DateTime $begin, \DateTime $end, Hotel $hotel, $sign, $type = 'begin')
    {
        if (empty($this->container->getParameter('mbh_modules')['online_export']) ||
            $sign != $this->container->getParameter('secret')
        ) {
            throw $this->createNotFoundException();
        }

        if (!in_array($type, ['begin', 'updatedAt', 'end', 'live'])) {
            $type = 'live';
        }

        $this->dm->getFilterCollection()->disable('softdeleteable');

        $qb = $this->dm->getRepository('MBHPackageBundle:Package')
            ->createQueryBuilder()
            ->field('roomType.id')->in($this->get('mbh.helper')->toIds($hotel->getRoomTypes()))
            ->sort('updatedAt', 'desc');;

        if ($type == 'live') {
            $qb
                ->field('begin')->lte($end)
                ->field('end')->gte($begin);
        } else {
            $qb
                ->field($type)->gte($begin)
                ->field($type)->lte($end);
        }

        return [
            'packages' => $qb->getQuery()->execute(),
        ];
    }

    /**
     * Success URL redirect
     * @Route("/success/url", name="api_success_url")
     * @Method({"POST", "GET"})
     */
    public function successUrlAction()
    {
        if (!$this->clientConfig || !$this->clientConfig->getSuccessUrl()) {
            throw $this->createNotFoundException();
        }

        return $this->redirect($this->clientConfig->getSuccessUrl());
    }

    /**
     * Fail URL redirect
     * @Route("/fail/url", name="api_fail_url")
     * @Method({"POST", "GET"})
     */
    public function failUrlAction()
    {
        if (!$this->clientConfig || !$this->clientConfig->getFailUrl()) {
            throw $this->createNotFoundException();
        }

        return $this->redirect($this->clientConfig->getFailUrl());
    }

    /**
     * Results js
     * @Route("/order/check/{paymentSystemName}", name="online_form_check_order")
     * @Method({"POST", "GET"})
     * @Template()
     * @param Request $request
     * @param $paymentSystemName
     * @return Response
     */
    public function checkOrderAction(Request $request, $paymentSystemName)
    {
        /** @var DocumentManager $dm */
        $dm = $this->get('doctrine_mongodb')->getManager();
        $clientConfig = $this->clientConfig;
        $logger = $this->get('mbh.online.logger');
        $logText = $this->generateLogText($request);

        if (!$clientConfig) {
            $logger->info('FAIL. '.$logText.' .Not found config');
            throw $this->createNotFoundException();
        }

        $doc = $clientConfig->getPaymentSystemDocByName($paymentSystemName);
        $paymentSystem =
            $this
                ->container
                ->get('MBH\Bundle\ClientBundle\Service\PaymentSystem\Wrapper\PaymentSystemWrapperFactory')
                ->create($doc);

        $holder = $paymentSystem->checkRequest($request, $clientConfig);

        if ($holder->hasInterimResponse()) {
            $logger->info('OK. ' . $logText . ' . Interim Response');

            return $holder->getInterimResponse();
        }

        if (!$holder->isSuccess()) {
            $logger->info('FAIL. '.$logText.' .Bad signature');
            $holder->getIndividualErrorResponse();
            throw new BadSignaturePaymentSystemException();
        }

        //save cashDocument
        $cashDocument = $dm->getRepository('MBHCashBundle:CashDocument')->find($holder->getDoc());

        if ($cashDocument && !$cashDocument->getIsPaid()) {
            $cashDocument->setIsPaid(true);
            $dm->persist($cashDocument);
            $dm->flush();
            //save commission
            if ($holder->getCommission() !== null && is_numeric($holder->getCommission())) {
                $commission = clone $cashDocument;
                $commissionTotal = (float)$holder->getCommission();
                if ($holder->getCommissionPercent()) {
                    $commissionTotal = $commissionTotal * $cashDocument->getTotal();
                }
                $commission->setTotal($commissionTotal)
                    ->setOperation('fee');
                $dm->persist($commission);
                $dm->flush();
            }
        }

        //send notifications
        /** @var Order $order */
        $order = $cashDocument->getOrder();
        $package = $order->getPackages()[0];
        $params = [
            '%cash%' => $cashDocument->getTotal(),
            '%order%' => $order->getId(),
            '%payer%' => $order->getPayer() ? $order->getPayer()->getName() : '-',
        ];

        $notifier = $this->get('mbh.notifier');
        $message = $notifier::createMessage();
        $message
            ->setText('mailer.online.payment.backend')
            ->setFrom('online')
            ->setSubject('mailer.online.payment.subject')
            ->setTranslateParams($params)
            ->setType('success')
            ->setCategory('notification')
            ->setHotel($cashDocument->getHotel())
            ->setAutohide(false)
            ->setEnd(new \DateTime('+10 minute'))
            ->setLink(
                $this->generateUrl('package_order_edit', ['id' => $order->getId(), 'packageId' => $package->getId()], UrlGeneratorInterface::ABSOLUTE_URL)
            )
            ->setLinkText('mailer.to_order')
            ->setMessageType(NotificationType::ONLINE_PAYMENT_CONFIRM_TYPE);

        //send to backend
        $notifier
            ->setMessage($message)
            ->notify();

        //send to user
        if ($order && $order->getPayer() && $order->getPayer()->getEmail()) {
            $message
                ->addRecipient($order->getPayer())
                ->setText('mailer.online.payment.user')
                ->setLink('hide')
                ->setLinkText(null)
                ->setTranslateParams($params)
                ->setAdditionalData(
                    [
                        'fromText' => $order->getFirstHotel(),
                    ]
                )
                ->setMessageType(NotificationType::ONLINE_PAYMENT_CONFIRM_TYPE);
            $this->get('mbh.notifier.mailer')
                ->setMessage($message)
                ->notify();
        }

        $logger->info('OK. '.$logText);

        return $holder->getIndividualSuccessResponse($this) ?? new Response($holder->getText());
    }

    private function generateLogText(Request $request): string
    {
        $text = [];
        $text[] = sprintf(
            '\MBH\Bundle\OnlineBundle\Controller::checkOrderAction. Get request from IP %s.',
            $request->getClientIp()
        );

        $generateText = function (string $method, array $array): string {
            return sprintf(
                '%s data: %s. Keys: %s',
                $method,
                implode('; ',$array),
                implode('; ', array_keys($array))
            );
        };

        if ($request->query->count() > 0) {
            $text[] = $generateText('Get', $request->query->getIterator()->getArrayCopy());
        };

        if ($request->request->count() > 0) {
            $text[] = $generateText('Post', $request->request->getIterator()->getArrayCopy());
        }

        return implode(';', $text);
    }

    /**
     * @Route("/payment/generate-invoice/{id}", name="generate_invoice")
     * @param Package $package
     * @return Response
     * @throws Exception
     */
    public function generateInvoiceAction(Package $package)
    {
        $content =  $this->get('mbh.template_formatter')
            ->generateDocumentTemplate($this->clientConfig->getInvoice()->getInvoiceDocument(), $package, $this->getUser());

        return new Response($content, 200, [
            'Content-Type' => 'application/pdf'
        ]);
    }

    /**
     * @Template()
     * @Route("/payment/stripe/{id}", name="stripe_payment_page")
     * @param CashDocument $cashDocument
     * @return array
     */
    public function showStripePaymentPageAction(CashDocument $cashDocument)
    {
        $checkUrl = $this->generateUrl('successful_payment', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $formData = $this->clientConfig->getFormData($cashDocument, Stripe::NAME, $checkUrl);

        return [
            'data' => $formData,
            'isOrderPaid' => $cashDocument->getIsPaid(),
            'currency' => $this->getParameter('locale.currency')
        ];
    }

    /**
     * @Template("@MBHUser/Profile/paymentResultPage.html.twig")
     * @Route("/payment/success", name="successful_payment")
     */
    public function showSuccessfulPaymentPageAction()
    {
        return ['success' => true];
    }

    /**
     * @Template("@MBHUser/Profile/paymentResultPage.html.twig")
     * @Route("/payment/fail", name="fail_payment")
     */
    public function showFailPaymentPageAction()
    {
        return ['success' => false];
    }
}
