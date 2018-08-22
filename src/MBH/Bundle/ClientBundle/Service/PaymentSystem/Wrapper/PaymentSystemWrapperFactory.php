<?php
/**
 * Created by PhpStorm.
 * Date: 21.08.18
 */

namespace MBH\Bundle\ClientBundle\Service\PaymentSystem\Wrapper;

use MBH\Bundle\ClientBundle\Lib\PaymentSystemCommonDocument;
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
     * @param PaymentSystemCommonDocument $doc
     * @return PaymentSystemInterface
     */
    public function create(PaymentSystemCommonDocument $doc): PaymentSystemInterface
    {
        $name = $doc::name();

        /** @var CommonWrapper $instance */
        $instance = $this->container->get('MBH\Bundle\ClientBundle\Service\PaymentSystem\Wrapper\\' . $doc::name());
        $instance->setPaymentSystemDocument($doc);

        return $instance;
    }
}