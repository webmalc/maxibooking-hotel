<?php

namespace MBH\Bundle\PackageBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class PackageCsvType extends  AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', CheckboxType::class, [
                'label' => 'Тип брони',
                'required' => false,
                'attr'     => array('checked'   => 'checked'),
                'group' => 'Параметры генерации CSV файла',
            ])
            ->add('numberWithPrefix', CheckboxType::class, [
                'label' => 'Название брони',
                'required' => false,
                'attr'     => array('checked'   => 'checked'),
                'group' => 'Параметры генерации CSV файла',
            ])
            ->add('dateBegin', CheckboxType::class, [
                'label' => 'С',
                'required' => false,
                'attr'     => array('checked'   => 'checked'),
                'group' => 'Параметры генерации CSV файла',
            ])
            ->add('dateEnd', CheckboxType::class, [
                'label' => 'По',
                'required' => false,
                'attr'     => array('checked'   => 'checked'),
                'group' => 'Параметры генерации CSV файла',
            ])
            ->add('tariffType', CheckboxType::class, [
                'label' => 'Тип номера',
                'required' => false,
                'attr'     => array('checked'   => 'checked'),
                'group' => 'Параметры генерации CSV файла',
            ])
            ->add('tariffAccomodation', CheckboxType::class, [
                'label' => 'Размещение',
                'required' => false,
                'attr'     => array('checked'   => 'checked'),
                'group' => 'Параметры генерации CSV файла',
            ])
            ->add('guests', CheckboxType::class, [
                'label' => 'Плательщик',
                'required' => false,
                'attr'     => array('checked'   => 'checked'),
                'group' => 'Параметры генерации CSV файла',
            ])
            ->add('Adults', CheckboxType::class, [
                'label' => 'Взрослые',
                'required' => false,
                'attr'     => array('checked'   => 'checked'),
                'group' => 'Параметры генерации CSV файла',
            ])
            ->add('Children', CheckboxType::class, [
                'label' => 'Дети',
                'required' => false,
                'attr'     => array('checked'   => 'checked'),
                'group' => 'Параметры генерации CSV файла',
            ])
            ->add('price', CheckboxType::class, [
                'label' => 'Стоимость',
                'required' => false,
                'attr'     => array('checked'   => 'checked'),
                'group' => 'Параметры генерации CSV файла',
            ])
            ->add('tariff', CheckboxType::class, [
                'label' => 'Тариф',
                'required' => false,
                'attr'     => array('checked'   => 'checked'),
                'group' => 'Параметры генерации CSV файла',
            ])
            ->add('createdAt', CheckboxType::class, [
                'label' => 'Дата создания',
                'required' => false,
                'attr'     => array('checked'   => 'checked'),
                'group' => 'Параметры генерации CSV файла',
            ])
            ->add('createdBy', CheckboxType::class, [
                'label' => 'Создал',
                'required' => false,
                'attr'     => array('checked'   => 'checked'),
                'group' => 'Параметры генерации CSV файла',
            ])
           ;

    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => null,
            ]);
    }
    public function getName()
    {
        return 'mbh_bundle_packagebundle_package_csv_type';
    }

}