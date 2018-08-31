<?php
/**
 * Created by PhpStorm.
 * Date: 24.08.18
 */

namespace MBH\Bundle\ClientBundle\Form\PaymentSystem;


use MBH\Bundle\ClientBundle\Document\PaymentSystem\Payanyway;
use MBH\Bundle\ClientBundle\Lib\PaymentSystemDocument;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class PayAnyWayType extends PaymentSystemType
{
    public static function getSourceDocument(): PaymentSystemDocument
    {
        return new Payanyway();
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'payanywayMntId',
                TextType::class,
                $this->addCommonAttributes(['label' => 'form.clientPaymentSystemType.extended_account_number'])
            )
            ->add(
                'payanywayKey',
                TextType::class,
                $this->addCommonAttributes(['label' => 'form.clientPaymentSystemType.data_integrity_code'])
            );
    }
}