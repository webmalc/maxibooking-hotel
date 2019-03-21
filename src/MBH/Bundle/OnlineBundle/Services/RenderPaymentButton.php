<?php
/**
 * Created by PhpStorm.
 * Date: 16.11.18
 */

namespace MBH\Bundle\OnlineBundle\Services;


use Symfony\Component\Translation\TranslatorInterface;
use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\ClientBundle\Document\ClientConfig;
use MBH\Bundle\ClientBundle\Service\ClientConfigManager;
use MBH\Bundle\ClientBundle\Service\PaymentSystem\Wrapper\PaymentSystemWrapperFactory;
use MBH\Bundle\ClientBundle\Service\PaymentSystem\Wrapper\Wrapper;
use MBH\Bundle\PackageBundle\Document\Order;
use Symfony\Component\Translation\DataCollectorTranslator;
use Twig_Environment;

class RenderPaymentButton
{
    /**
     * @var Twig_Environment
     */
    private $twig;

    /**
     * @var ClientConfig
     */
    private $clientConfig;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var PaymentSystemWrapperFactory
     */
    private $paymentSystemWrapperFactory;

    /**
     * RenderForm constructor.
     * @param Twig_Environment $twig
     * @param ClientConfig $clientConfig
     * @param TranslatorInterface $translator
     * @param PaymentSystemWrapperFactory $paymentSystemWrapperFactory
     */
    public function __construct(
        Twig_Environment $twig,
        ClientConfigManager $clientConfigManager,
        TranslatorInterface $translator,
        PaymentSystemWrapperFactory $paymentSystemWrapperFactory
    ) {
        $this->twig = $twig;
        $this->clientConfig = $clientConfigManager->fetchConfig();
        $this->translator = $translator;
        $this->paymentSystemWrapperFactory = $paymentSystemWrapperFactory;
    }

    /**
     * @return Twig_Environment
     */
    public function getTwig(): Twig_Environment
    {
        return $this->twig;
    }

    /**
     * @return ClientConfig
     */
    public function getClientConfig(): ClientConfig
    {
        return $this->clientConfig;
    }

    /**
     * @return TranslatorInterface
     */
    public function getTranslator(): TranslatorInterface
    {
        return $this->translator;
    }

    /**
     * @return PaymentSystemWrapperFactory
     */
    public function getPaymentSystemWrapperFactory(): PaymentSystemWrapperFactory
    {
        return $this->paymentSystemWrapperFactory;
    }

    public function create(string $paymentSystemName, string $total,Order $order, CashDocument $cashDocument, bool $disabledScript = false): string
    {
        $doc = $this->getClientConfig()->getPaymentSystemDocByName($paymentSystemName);

        /** @var Wrapper $paymentSystem */
        $paymentSystem =
            $this->getPaymentSystemWrapperFactory()->create($doc);

        $form = $this->getTwig()->render(
            'MBHClientBundle:PaymentSystem:' . $paymentSystemName . '.html.twig',
            [
                'referer' => '*',
                'data'    => array_merge(
                    [
                        'test'       => false,
                        'currency'   => strtoupper($this->getClientConfig()->getCurrency()),
                        'buttonText' => $this->getTranslator()->trans(
                            'views.api.make_payment_for_order_id',
                            ['%total%' => number_format($total, 2)],
                            'MBHOnlineBundle'
                        ),
                        'disabledScript' => $disabledScript,
                    ],
                    $paymentSystem->getPreFormData($this->getClientConfig(), $cashDocument)
                ),
            ]
        );

        return $form;
    }
}