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
                    'label' => 'Фамилия',
                    'group' => 'Общаяя информация',
                    'required' => true,
                    'attr' => ['placeholder' => 'Иванов']
                ])
                ->add('firstName', 'text', [
                    'label' => 'Имя',
                    'group' => 'Общаяя информация',
                    'required' => true,
                    'attr' => ['placeholder' => 'Иван']
                ])
                ->add('patronymic', 'text', [
                    'label' => 'Отчество',
                    'group' => 'Общаяя информация',
                    'required' => false,
                    'attr' => ['placeholder' => 'Иванович']
                ])
                ->add('birthday', 'date', array(
                    'label' => 'Дата рождения',
                    'group' => 'Общаяя информация',
                    'widget' => 'single_text',
                    'format' => 'dd.MM.yyyy',
                    'required' => false,
                    'attr' => array('data-date-format' => 'dd.mm.yyyy'),
                ))
                ->add('sex', 'choice', [
                    'label' => 'Пол',
                    'group' => 'Общаяя информация',
                    'required' => false,
                    'multiple' => false,
                    'empty_value' => '',
                    'choices' => $options['genders']
                ])
                ->add('address', 'textarea', [
                    'label' => 'Адрес',
                    'group' => 'Контактная информация',
                    'required' => false,
                    'attr' => ['placeholder' => 'г. Москва, пр-кт Мира, д.6']
                ])
                ->add('document', 'textarea', [
                    'label' => 'Документ',
                    'group' => 'Контактная информация',
                    'required' => false,
                    'attr' => ['placeholder' => 'Паспорт: 4545№345678, выдан 28 сентября 2002 г. 2 отделением милиции Г. Москвы']
                ])
                ->add('phone', 'text', [
                    'label' => 'Телефон',
                    'group' => 'Контактная информация',
                    'required' => false,
                    'attr' => ['placeholder' => '+7(925)3456512']
                ])
                ->add('email', 'text', [
                    'label' => 'E-mail',
                    'group' => 'Контактная информация',
                    'required' => false,
                    'attr' => ['placeholder' => 'mail@exapmple.com']
                ])
                ->add('note', 'textarea', [
                    'label' => 'Примечание',
                    'group' => 'Контактная информация',
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
