<?php
/**
 * Created by PhpStorm.
 * Date: 03.09.18
 */

namespace MBH\Bundle\ClientBundle\Lib\PaymentSystem;


use MBH\Bundle\CashBundle\Document\CashDocument;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DescriptionGenerator
{

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * DescriptionForInvoice constructor.
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function generate(CashDocument $cashDocument): string
    {
        $trans = $this->container->get('translator');
        $hotel = $cashDocument->getHotel();
        // т.к. генерация происходит из онлайн формы, те ордер новый и пакеджы с одной датой
        $packages = iterator_to_array($cashDocument->getOrder()->getPackages());

        if (count($packages) > 1) {
            $descSuffix = $trans->trans('payment.receipt.common_description.packages');
        } else {
            $descSuffix = $trans->trans('payment.receipt.common_description.package');
        }

        $descSuffix .= implode(' ,', $packages) . '.';

        $desc = $trans->trans('payment.receipt.common_description.hotel_name_and_date') . $descSuffix;

        return sprintf(
                $desc,
                $hotel,
                $packages[0]->getBegin()->format('d.m.Y'),
                $packages[0]->getEnd()->format('d.m.Y')
            );
    }
}