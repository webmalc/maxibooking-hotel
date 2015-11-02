<?php

namespace MBH\Bundle\OnlineBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FormType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'enabled',
                'checkbox',
                [
                    'label' => 'form.formType.is_turned_on',
                    'group' => 'form.formType.parameters',
                    'value' => true,
                    'required' => false,
                    'help' =>  'form.formType.use_online_form'
                ]
            )
            ->add(
                'roomTypes',
                'checkbox',
                [
                    'label' => 'form.formType.room_types',
                    'group' => 'form.formType.parameters',
                    'value' => true,
                    'required' => false,
                    'help' => 'form.formType.should_we_use_room_type_field_in_online_form'
                ]
            );
            $builder
                ->add(
                    'tourists',
                    'checkbox',
                    [
                        'label' => 'form.formType.are_there_guests',
                        'group' => 'form.formType.parameters',
                        'value' => true,
                        'required' => false,
                        'help' => 'form.formType.should_we_use_guests_amount_field_in_online_form'
                    ]
                )
            ;
        $builder
            ->add(
                'nights',
                'checkbox',
                [
                    'label' => 'form.formType.should_we_use_nights_field',
                    'group' => 'form.formType.parameters',
                    'value' => true,
                    'required' => false,
                    'help' => 'form.formType.should_we_use_check_in_date_or_check_in_and_check_out_date'
                ]
            )
            ->add(
                'paymentTypes',
                'choice',
                [
                    'group' => 'form.formType.payment',
                    'choices' => $options['paymentTypes'],
                    'label' => 'form.formType.payment_type',
                    'multiple' => true,
                    'help' => 'form.formType.reservation_payment_types_with_online_form'
                ]
            )
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'MBH\Bundle\OnlineBundle\Document\FormConfig',
                'paymentTypes' => []
            )
        );
    }

    public function getName()
    {
        return 'mbh_bundle_onlinebundle_form_type';
    }

}
