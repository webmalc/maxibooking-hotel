<?php

namespace MBH\Bundle\ClientBundle\Form;

use MBH\Bundle\ClientBundle\Document\ClientConfig;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PaymentSystemsUrlsType extends AbstractType
{
    public const FORM_NAME = 'mbhclient_bundle_payment_systems_urls_type';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('successUrl', TextType::class,
            [
                'label' => 'form.clientPaymentSystemType.successUrl',
                'help' => 'form.clientPaymentSystemType.successUrlDesc',
                'group' => 'form.clientPaymentSystemType.payment_system_group_links',
                'required' => false,
            ]
        )
            ->add('failUrl', TextType::class,
                [
                    'label' => 'form.clientPaymentSystemType.failUrl',
                    'help' => 'form.clientPaymentSystemType.failUrlDesc',
                    'group' => 'form.clientPaymentSystemType.payment_system_group_links',
                    'required' => false,
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ClientConfig::class
        ]);
    }

    public function getBlockPrefix()
    {
        return self::FORM_NAME;
    }
}
