<?php

namespace MBH\Bundle\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClientTariffType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('rooms', TextType::class, [
                'label' => 'client_tariff_type.number_of_rooms.label',
                'attr' => [
                    'class' => 'payment-system-params stripe mbh-spinner',
                    'spinner-min' => 1,
                    'spinner-max' => 50000,
                    'step' => 1,
                    'decimals' => 0
                ],
            ])
            ->add('period', ChoiceType::class, [
                'label' => 'client_tariff_type.period.label',
                'choices' => [
                    'client_tariff_type.period.one_month' => 1,
                    'client_tariff_type.period.six_month' => 6,
                    'client_tariff_type.period.twelve_month' => 12,
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {

    }

    public function getBlockPrefix()
    {
        return 'mbhuser_bundle_client_tariff_type';
    }
}
