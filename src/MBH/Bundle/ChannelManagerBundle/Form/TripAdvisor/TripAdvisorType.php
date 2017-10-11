<?php

namespace MBH\Bundle\ChannelManagerBundle\Form\TripAdvisor;

use MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TripAdvisorType extends AbstractType
{
    private $parkingTypes;

    public function __construct($parkingTypes) {
        $this->parkingTypes = $parkingTypes;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'isEnabled', CheckboxType::class, [
                    'label' => 'form.trip_advisor_type.is_included',
                    'value' => true,
                    'required' => false,
                    'help' => 'form.trip_advisor_type.should_we_use_in_channel_manager'
                ]
            )
            ->add(
                'hotelId', TextType::class, [
                    'label' => 'form.trip_advisor_type.hotel_id',
                    'required' => true,
                    'help' => 'form.trip_advisor_type.hotel_id_in_trip_advisor',
                ]
            )
            ->add('locale', ChoiceType::class, [
                'label' => 'form.trip_advisor_type.language.label',
                'choice_label' => function($label) {
                    return 'language.'.$label;
                },
                'choices' => $options['languages']
            ])
            ->add('hotelUrl', TextType::class, [
                'label' => 'form.trip_advisor_type.hotel_url.label',
                'help' => 'form.trip_advisor_type.hotel_url.help'
            ])
            ->add('paymentPolicy', TextareaType::class, [
                'label' => 'form.trip_advisor_type.hotel_payment_policy.label',
                'help' => 'form.trip_advisor_type.hotel_payment_policy.help',
                'attr' => [
                    'placeholder' => 'form.trip_advisor_type.hotel_payment_policy.placeholder'
                ]
            ])
            ->add('termsAndConditions', TextareaType::class, [
                'label' => 'form.trip_advisor_type.terms_and_confitions.label',
                'help' => 'form.trip_advisor_type.terms_and_confitions.help'
            ])
            ->add('child_policy', TextareaType::class, [
                'label' => 'form.trip_advisor_type.child_policy.label',
                'attr' => [
                    'placeholder' => 'form.trip_advisor_type.child_policy.placeholder'
                ],
                'required' => false
            ])
            ->add('paymentType', InvertChoiceType::class, [
                'label' => 'form.trip_advisor_type.payment_type.label',
                'choices' => $options['payment_types']
            ])
            ->add('parkingTypes', InvertChoiceType::class, [
                'choices' => $this->parkingTypes,
                'multiple' => true
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'MBH\Bundle\ChannelManagerBundle\Document\TripAdvisorConfig',
                'hotel' => null,
                'languages' => [],
                'payment_types' => []
            )
        );
    }

    public function getBlockPrefix()
    {
        return 'mbh_bundle_channelmanagerbundle_trip_advisor_type';
    }

}
