<?php

namespace MBH\Bundle\PackageBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TouristType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('lastName', 'text', [
                'label' => 'form.touristType.surname',
                'group' => 'form.touristType.general_info',
                'required' => true,
                'attr' => ['placeholder' => 'form.touristType.placeholder_surname']
            ])
            ->add('firstName', 'text', [
                'label' => 'form.touristType.name',
                'group' => 'form.touristType.general_info',
                'required' => true,
                'attr' => ['placeholder' => 'form.touristType.placeholder_name']
            ])
            ->add('patronymic', 'text', [
                'label' => 'form.touristType.second_name',
                'group' => 'form.touristType.general_info',
                'required' => false,
                'attr' => ['placeholder' => 'form.touristType.placeholder_second_name']
            ])
            ->add('birthday', 'date', array(
                'label' => 'form.touristType.birth_date',
                'group' => 'form.touristType.general_info',
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'required' => false,
                'attr' => array('data-date-format' => 'dd.mm.yyyy', 'class' => 'input-small datepicker'),
            ))
            ->add('sex', 'choice', [
                'label' => 'form.touristType.gender',
                'group' => 'form.touristType.general_info',
                'required' => false,
                'multiple' => false,
                'empty_value' => '',
                'choices' => $options['genders']
            ])
            ->add('phone', 'text', [
                'label' => 'form.touristType.phone',
                'group' => 'form.touristType.contact_info',
                'required' => false,
                'attr' => ['placeholder' => 'form.touristType.placeholder_phone']
            ])
            ->add('mobilePhone', 'text', [
                'label' => 'form.touristType.mobile_phone',
                'group' => 'form.touristType.contact_info',
                'required' => false,
                'attr' => ['placeholder' => 'form.touristType.placeholder_phone']
            ])
            ->add('messenger', 'text', [
                'label' => 'form.touristType.messenger',
                'attr' => ['placeholder' => 'form.touristType.placeholder_messenger'],
                'group' => 'form.touristType.contact_info',
                'required' => false,
            ])
            ->add('email', 'text', [
                'label' => 'form.touristType.email',
                'group' => 'form.touristType.contact_info',
                'required' => false,
                'attr' => ['placeholder' => 'form.touristType.placeholder_email']
            ])
            ->add('note', 'textarea', [
                'label' => 'form.touristType.note',
                'group' => 'form.touristType.contact_info',
                'required' => false,
            ])
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MBH\Bundle\PackageBundle\Document\Tourist',
            'genders' => []
        ));
    }

    public function getName()
    {
        return 'mbh_bundle_packagebundle_touristtype';
    }

}
