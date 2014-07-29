<?php

namespace MBH\Bundle\PackageBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Date;

class PackageGuestType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('lastName', 'text', [
                    'label' => 'Фамилия',
                    'required' => true,
                    'group' => 'Добавить туриста',
                    'attr' => ['placeholder' => 'Иванов'],
                    'constraints' => [new NotBlank(), new Length([
                        'min' => 2,
                        'max' => 100,
                        'minMessage' => 'Слишком короткое имя',
                        'maxMessage' => 'Слишком длинное имя'
                    ])]
                ])
                ->add('firstName', 'text', [
                    'label' => 'Имя',
                    'required' => true,
                    'group' => 'Добавить туриста',
                    'attr' => ['placeholder' => 'Иван'],
                    'constraints' => [new NotBlank(), new Length([
                        'min' => 2,
                        'max' => 100,
                        'minMessage' => 'Слишком короткая фамилия',
                        'maxMessage' => 'Слишком длинная фамилия'
                    ])]
                ])
                ->add('patronymic', 'text', [
                    'label' => 'Отчество',
                    'required' => false,
                    'group' => 'Добавить туриста',
                    'attr' => ['placeholder' => 'Иванович'],
                    'constraints' => [new Length([
                        'min' => 2,
                        'max' => 100,
                        'minMessage' => 'Слишком короткое отчество',
                        'maxMessage' => 'Слишком длинное отчество'
                    ])]
                ])
                ->add('birthday', 'date', array(
                    'label' => 'Дата рождения',
                    'widget' => 'single_text',
                    'group' => 'Добавить туриста',
                    'format' => 'dd.MM.yyyy',
                    'required' => false,
                    'attr' => array('data-date-format' => 'dd.mm.yyyy'),
                    'constraints' => [new Date()]
                ))
                ->add('main', 'checkbox', [
                    'label' => 'Основной турист?',
                    'group' => 'Добавить туриста',
                    'value' => true,
                    'required' => false,
                ])
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
        ]);
    }

    public function getName()
    {
        return 'mbh_bundle_packagebundle_package_guest_type';
    }

}
