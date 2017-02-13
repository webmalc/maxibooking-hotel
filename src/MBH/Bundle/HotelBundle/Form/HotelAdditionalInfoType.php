<?php

namespace MBH\Bundle\HotelBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HotelAdditionalInfoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('refundableType', ChoiceType::class, [
                'required' => false,
                'label' => 'form.hotel_additional_info_type.refundable_type.label',
                'choices' => [
                    'form.hotel_additional_info_type.refundable_type.full' => 'full',
                    'form.hotel_additional_info_type.refundable_type.partial' => 'partial',
                    'form.hotel_additional_info_type.refundable_type.none' => 'none'
                ]
            ])
            ->add('cancellationPolicy', TextareaType::class, [
                'required' => false,
                'label' => 'form.hotel_additional_info_type.cancellation_policy.label'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'MBH\Bundle\HotelBundle\Document\Hotel',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'mbhhotel_bundle_hotel_additional_info_type';
    }
}
