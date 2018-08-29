<?php
/**
 * Created by PhpStorm.
 * Date: 27.08.18
 */

namespace MBH\Bundle\ClientBundle\Form\PaymentSystem;


use MBH\Bundle\ClientBundle\Document\Stripe;
use MBH\Bundle\ClientBundle\Lib\PaymentSystemDocument;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class StripeType extends PaymentSystemType
{
    public static function getSourceDocument(): PaymentSystemDocument
    {
        return new Stripe();
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'publishableToken',
                TextType::class,
                $this->addCommonAttributes(
                    [
                        'label' => 'form.clientPaymentSystemType.stripe_pub_token.label',
                    ]
                )
            )
            ->add(
                'secretKey',
                TextType::class,
                $this->addCommonAttributes(
                    [
                        'label' => 'form.clientPaymentSystemType.stripe_secret_key.label',
                    ]
                )
            )
            ->add(
                'commissionInPercents',
                TextType::class,
                $this->addCommonAttributes(
                    [
                        'label' => 'form.clientPaymentSystemType.stripe_commission.label',
                        'attr'  => [
                            'class'       => 'mbh-spinner',
                            'spinner-max' => 100,
                            'step'        => 0.05,
                            'decimals'    => 2,
                        ],
                    ]
                )
            );
    }
}