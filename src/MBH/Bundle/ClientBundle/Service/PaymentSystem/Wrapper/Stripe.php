<?php
/**
 * Created by PhpStorm.
 * Date: 22.08.18
 */

namespace MBH\Bundle\ClientBundle\Service\PaymentSystem\Wrapper;

use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\ClientBundle\Document\ClientConfig;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\CheckResultHolder;
use Stripe\Charge;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class Stripe
 * @package MBH\Bundle\ClientBundle\Service\PaymentSystem\Wrapper
 *
 * @property \MBH\Bundle\ClientBundle\Document\Stripe $entity
 */
class Stripe extends Wrapper
{
    /**
     * @param CashDocument $cashDocument
     * @param string $url
     * @param string $checkUrl
     * @return array
     */
    public function getFormData(CashDocument $cashDocument, $url = null, $checkUrl = null)
    {
        return [
            'token'       => $this->entity->getPublishableToken(),
            'amount'      => $cashDocument->getTotal(),
            'email'       => $cashDocument->getPayer()->getEmail(),
            'returnUrl'   => $url,
            'description' => '',
            'signature'   => $this->getSignature($cashDocument, $url),
            'orderId'     => $cashDocument->getId(),
        ];
    }

    /**
     * @param CashDocument $cashDocument
     * @param string $url
     * @return string
     */
    public function getSignature(CashDocument $cashDocument, $url = null)
    {
        return $this->calcSignature([
            'secretKey' => $this->entity->getSecretKey(),
            'pubToken'  => $this->entity->getPublishableToken(),
            'amount'    => $cashDocument->getTotal(),
            'email'     => $cashDocument->getPayer()->getEmail(),
            'orderId'   => $cashDocument->getId(),
        ]);
    }

    /**
     * @param array $data
     * @return string
     */
    private function calcSignature(array $data)
    {
        return strtolower(md5(implode('::', $data)));
    }

    /**
     * @param Request $request
     * @return array|bool
     */
    public function checkRequest(Request $request, ClientConfig $clientConfig): CheckResultHolder
    {
        $requestSignature = $request->get('signature');

        $orderId = $request->get('orderId');
        $signature = $this->calcSignature([
            'secretKey' => $this->entity->getSecretKey(),
            'pubToken'  => $this->entity->getPublishableToken(),
            'amount'    => $request->get('amount'),
            'email'     => $request->get('email'),
            'orderId'   => $request->get('orderId'),
        ]);

        $holder = new CheckResultHolder();

        if ($requestSignature != $signature) {
            return $holder;
        }

        $holder->parseData([
            'doc'               => $orderId,
            'commission'        => $this->entity->getCommissionInPercents(),
            'commissionPercent' => true,
        ]);

        $holder->setIndividualSuccessResponse(function (Controller $controller) {
            return $controller->redirectToRoute('successful_payment');
        });

        \Stripe\Stripe::setApiKey($clientConfig->getStripe()->getSecretKey());

        $charge = Charge::create([
            "amount"      => $request->request->get('amount') * 100,
            "currency"    => $request->request->get('currency'),
            "description" => "Charge for order #" . $holder->getDoc(),
            "source"      => $request->get('stripeToken'),
        ]);
        if ($charge->status !== 'succeeded') {
            $holder->setIndividualErrorResponse(
                function () {
                    throw new BadRequestHttpException('Stripe charge is not successful');
                }
            );

            return $holder;
        }

        return $holder;
    }
}