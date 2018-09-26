<?php

namespace MBH\Bundle\PackageBundle\Form;

use MBH\Bundle\BaseBundle\Form\LanguageType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TouristType
 */
class TouristType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('lastName', TextType::class, [
                'label' => 'form.touristType.surname',
                'group' => 'form.touristType.general_info',
                'required' => true,
                'attr' => ['placeholder' => 'form.touristType.placeholder_surname']
            ])
            ->add('firstName', TextType::class, [
                'label' => 'form.touristType.name',
                'group' => 'form.touristType.general_info',
                'required' => true,
                'attr' => ['placeholder' => 'form.touristType.placeholder_name']
            ])
            ->add('patronymic', TextType::class, [
                'label' => 'form.touristType.second_name',
                'group' => 'form.touristType.general_info',
                'required' => false,
                'attr' => ['placeholder' => 'form.touristType.placeholder_second_name']
            ])
            ->add('birthday', DateType::class, array(
                'label' => 'form.touristType.birth_date',
                'group' => 'form.touristType.general_info',
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'required' => false,
                'attr' => array('data-date-format' => 'dd.mm.yyyy', 'class' => 'input-small'),
            ))
            ->add('sex',  \MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType::class, [
                'label' => 'form.touristType.gender',
                'group' => 'form.touristType.general_info',
                'required' => false,
                'multiple' => false,
                'placeholder' => '',
                'choices' => $options['genders']
            ])
            ->add('communication_language', LanguageType::class, [
                'label' => 'form.touristType.communication_language',
                'group' => 'form.touristType.general_info',
                //'expanded' => true
            ])
            ->add('inn', TextType::class, [
                'label' => 'form.tourist.inn.label',
                'group' => 'form.touristType.general_info',
                'required' => false,
                'attr' => ['class' => 'inn'],
                'translation_domain' => 'individual'
            ])
            ->add('phone', TextType::class, [
                'label' => 'form.touristType.phone',
                'group' => 'form.touristType.contact_info',
                'required' => false,
                'attr' => ['placeholder' => 'form.touristType.placeholder_phone']
            ])
            ->add('mobilePhone', TextType::class, [
                'label' => 'form.touristType.mobile_phone',
                'group' => 'form.touristType.contact_info',
                'required' => false,
                'attr' => ['placeholder' => 'form.touristType.placeholder_phone']
            ])
            ->add('messenger', TextType::class, [
                'label' => 'form.touristType.messenger',
                'attr' => ['placeholder' => 'form.touristType.placeholder_messenger'],
                'group' => 'form.touristType.contact_info',
                'required' => false,
            ])
            ->add('email', TextType::class, [
                'label' => 'form.touristType.email',
                'group' => 'form.touristType.contact_info',
                'required' => false,
                'attr' => ['placeholder' => 'form.touristType.placeholder_email']
            ])
            ->add('note', TextareaType::class, [
                'label' => 'form.touristType.note',
                'group' => 'form.touristType.contact_info',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'MBH\Bundle\PackageBundle\Document\Tourist',
            'genders' => [],
            'languages' => []
        ]);
    }


    public function getBlockPrefix()
    {
        return 'mbh_bundle_packagebundle_touristtype';
    }

}
