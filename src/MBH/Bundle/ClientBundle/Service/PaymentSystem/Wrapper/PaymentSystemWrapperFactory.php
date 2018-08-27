<?php
/**
 * Created by PhpStorm.
 * Date: 21.08.18
 */

namespace MBH\Bundle\ClientBundle\Service\PaymentSystem\Wrapper;

use MBH\Bundle\ClientBundle\Lib\PaymentSystemDocument;
use MBH\Bundle\ClientBundle\Lib\PaymentSystemInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


class PaymentSystemWrapperFactory
{
    protected $container;

    /**
     * Common constructor.
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param PaymentSystemDocument $doc
     * @return PaymentSystemInterface
     */
    public function create(PaymentSystemDocument $doc): PaymentSystemInterface
    {
        /** @var Wrapper $instance */
        $instance = $this->container->get('MBH\Bundle\ClientBundle\Service\PaymentSystem\Wrapper\\' . $doc::fileClassName());
        $instance->setPaymentSystemDocument($doc);

        return $instance;
    }
}