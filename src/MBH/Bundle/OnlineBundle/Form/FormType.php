<?php

namespace MBH\Bundle\OnlineBundle\Form;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use MBH\Bundle\BaseBundle\Form\LanguageType;
use MBH\Bundle\OnlineBundle\Document\FormConfig;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

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
                    'required' => true,
                    'multiple' => true,
                    'attr' => ['placeholder' => 'form.formType.hotels_placeholder'],
                    'help' => 'form.formType.hotels_desc'
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
                    'help' => 'form.formType.use_online_form'
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
            )
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
            ->add(
                'language',
                LanguageType::class,
                [
                    'group' => 'form.formType.parameters',
                    'label' => 'form.formType.language',
                    'required' => true,
                ]
            )
            ->add(
                'paymentTypes',
                ChoiceType::class,
                [
                    'group' => 'form.formType.payment',
                    'choices' => $options['paymentTypes'],
                    'label' => 'form.formType.payment_type',
                    'multiple' => true,
                    'help' => 'form.formType.reservation_payment_types_with_online_form'
                ]
            )
            ->add(
                'css',
                ChoiceType::class,
                [
                    'label' => 'form.formType.css',
                    'group' => 'form.formType.design',
                    'required' => true,
                    'choices' => FormConfig::getCssList(),
                    'help' => 'form.formType.css_help'
                ]
            )
            ->add(
                'style',
                TextareaType::class,
                [
                    'label' => 'form.formType.style',
                    'group' => 'form.formType.design',
                    'required' => false,
                    'help' => 'form.formType.style_help'
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
