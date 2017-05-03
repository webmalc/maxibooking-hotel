<?php

namespace MBH\Bundle\OnlineBundle\Form;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FormType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'hotels',
                DocumentType::class,
                [
                    'label' => 'form.formType.hotels',
                    'class' => 'MBH\Bundle\HotelBundle\Document\Hotel',
                    'group' => 'form.formType.parameters',
                    'required' => false,
                    'multiple' => true,
                    'attr' => ['placeholder' => 'form.formType.hotels_placeholder'],
                    'help' =>  'form.formType.hotels_desc'
                ]
            )
            ->add(
                'enabled',
                CheckboxType::class,
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
                CheckboxType::class,
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
                    CheckboxType::class,
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
                CheckboxType::class,
                [
                    'label' => 'form.formType.should_we_use_nights_field',
                    'group' => 'form.formType.parameters',
                    'value' => true,
                    'required' => false,
                    'help' => 'form.formType.should_we_use_check_in_date_or_check_in_and_check_out_date'
                ]
            )
            ->add('isDisplayChildrenAges', CheckboxType::class, [
                'label' => 'form.formType.used_children_ages.label',
                'group' => 'form.formType.parameters',
                'value' => true,
                'required' => false,
                'help' => 'form.formType.used_children_ages.help'
            ])
            ->add(
                'paymentTypes',  \MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType::class,
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

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'MBH\Bundle\OnlineBundle\Document\FormConfig',
                'paymentTypes' => []
            )
        );
    }

    public function getBlockPrefix()
    {
        return 'mbh_bundle_onlinebundle_form_type';
    }

}
