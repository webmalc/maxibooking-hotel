<?php

namespace MBH\Bundle\ClientBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use MBH\Bundle\BaseBundle\Lib\Exception;
use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\ClientBundle\Lib\PaymentSystemInterface;
use Symfony\Component\HttpFoundation\Request;

use Mdb\PayPal\Ipn\Event\MessageInvalidEvent;
use Mdb\PayPal\Ipn\Event\MessageVerificationFailureEvent;
use Mdb\PayPal\Ipn\Event\MessageVerifiedEvent;
use Mdb\PayPal\Ipn\ListenerBuilder\Guzzle\ArrayListenerBuilder as ListenerBuilder;

/**
 * @ODM\EmbeddedDocument
 */
class Paypal implements PaymentSystemInterface
{
    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $paypalLogin;

    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $paypalSecretKey;

    /**
     * @inheritdoc
     */
    public function getFormData(CashDocument $cashDocument, $url = null, $checkUrl = null)
    {
        $payer = $cashDocument->getPayer();
        $createdAt = clone $cashDocument->getCreatedAt();
        $createdAt->modify('+30 minutes');

        return [
            'action' => 'https://www.sandbox.paypal.com/cgi-bin/websc',
            'testAction' => 'https://www.sandbox.paypal.com/cgi-bin/websc',
            'shopId' => $this->getPaypalLogin(),
            'total' => $cashDocument->getTotal(),
            'orderId' => $cashDocument->getId(),
            'touristId' => $cashDocument->getOrder()->getId(),
            'cardId' => $cashDocument->getOrder()->getId(),
            'url' => $url,
            'time' => 60 * 30,
            'disabled' => $createdAt <= new \DateTime(),
            'touristEmail' => $payer ? $payer->getEmail() : null,
            'touristPhone' => $payer ? $payer->getPhone(true) : null,
            'comment' => 'Order # ' . $cashDocument->getOrder()->getId() . '. CashDocument #' . $cashDocument->getId(),
            'signature' => $this->getSignature($cashDocument, $url),
        ];
    }
    /**
     * @inheritdoc
     */
    public function getSignature(CashDocument $cashDocument, $url = null)
    {

    }
    /**
     * @inheritdoc
     */
    public function checkRequest(Request $request)
    {
        $listenerBuilder = new ListenerBuilder();

        $gd = $request->request->all();
//        dump($gd);
        $listenerBuilder->setData($gd);

        $listenerBuilder->useSandbox();

        $listener = $listenerBuilder->build();

        $listener->onInvalid(function (MessageInvalidEvent $event) {
            $ipnMessage = $event->getMessage();
            dump($ipnMessage);
            dump('не действительный платеж');

        });
        $listener->onVerified(function (MessageVerifiedEvent $event) {
            $ipnMessage = $event->getMessage();
            dump(' действительный платеж');
        });
        $listener->onVerificationFailure(function (MessageVerificationFailureEvent $event) {
            $error = $event->getError();
            dump('Ошибка');
        });
        $listener->listen();


        exit();
       return $request;
    }

    /**
     * @return string
     */
    public function getPaypalLogin()
    {
        return $this->paypalLogin;
    }

    /**
     * @param string $PayPalLogin
     */
    public function setPaypalLogin(string $paypalLogin)
    {
        $this->paypalLogin = $paypalLogin;
        return $this;
    }

    /**
     * @return string
     */
    public function getPaypalSecretKey()
    {
        return $this->paypalSecretKey;
    }

    /**
     * @param string $PayPalSecretKey
     */
    public function setPaypalSecretKey(string $paypalSecretKey)
    {
        $this->paypalSecretKey = $paypalSecretKey;
        return $this;
    }

}
