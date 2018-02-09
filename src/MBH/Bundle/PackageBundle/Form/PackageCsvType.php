<?php

namespace MBH\Bundle\PackageBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class PackageCsvType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', CheckboxType::class, [
                'label' => 'csv.form.type',
                'required' => false,
                'attr' => array('checked' => 'checked'),
                'group' => 'csv.form.group',
            ])
            ->add('typeOrder', CheckboxType::class, [
                'label' => 'csv.type.order.type',
                'required' => false,
                'attr' => array('checked' => 'checked'),
                'group' => 'csv.form.group',
            ])
            ->add('orderSource', CheckboxType::class, [
                'label' => 'csv.type.order.source',
                'required' => false,
                'attr' => array('checked' => 'checked'),
                'group' => 'csv.form.group',
            ])
            ->add('numberWithPrefix', CheckboxType::class, [
                'label' => 'csv.form.numberWithPrefix',
                'required' => false,
                'attr' => array('checked' => 'checked'),
                'group' => 'csv.form.group',
            ])
            ->add('dateBegin', CheckboxType::class, [
                'label' => 'csv.form.dateBegin',
                'required' => false,
                'attr' => array('checked' => 'checked'),
                'group' => 'csv.form.group',
            ])
            ->add('dateEnd', CheckboxType::class, [
                'label' => 'csv.form.dateEnd',
                'required' => false,
                'attr' => array('checked' => 'checked'),
                'group' => 'csv.form.group',
            ])
            ->add('tariffType', CheckboxType::class, [
                'label' => 'csv.form.tariffType',
                'required' => false,
                'attr' => array('checked' => 'checked'),
                'group' => 'csv.form.group',
            ])
            ->add('tariffAccomodation', CheckboxType::class, [
                'label' => 'csv.form.tariffAccomodation',
                'required' => false,
                'attr' => array('checked' => 'checked'),
                'group' => 'csv.form.group',
            ])
            ->add('guests', CheckboxType::class, [
                'label' => 'csv.form.guests',
                'required' => false,
                'attr' => array('checked' => 'checked'),
                'group' => 'csv.form.group',
            ])
            ->add('adults', CheckboxType::class, [
                'label' => 'csv.form.adults',
                'required' => false,
                'attr' => array('checked' => 'checked'),
                'group' => 'csv.form.group',
            ])
            ->add('children', CheckboxType::class, [
                'label' => 'csv.form.children',
                'required' => false,
                'attr' => array('checked' => 'checked'),
                'group' => 'csv.form.group',
            ])
            ->add('countNight', CheckboxType::class, [
                'label' => 'mbhpackagebundle.form.packagecsvtype.kol.vo.nochey',
                'required' => false,
                'attr' => array('checked' => 'checked'),
                'group' => 'csv.form.group',
            ])
            ->add('countPersons', CheckboxType::class, [
                'label' => 'mbhpackagebundle.form.packagecsvtype.kol.vo.chelovek',
                'required' => false,
                'attr' => array('checked' => 'checked'),
                'group' => 'csv.form.group',
            ])
            ->add('price', CheckboxType::class, [
                'label' => 'csv.form.price',
                'required' => false,
                'attr' => array('checked' => 'checked'),
                'group' => 'csv.form.group',
            ])
            ->add('paids', CheckboxType::class, [
                'label' => 'csv.form.paids',
                'required' => false,
                'attr' => array('checked' => 'checked'),
                'group' => 'csv.form.group',
            ])
            ->add('rest', CheckboxType::class, [
                'label' => 'csv.form.rest',
                'required' => false,
                'attr' => array('checked' => 'checked'),
                'group' => 'csv.form.group',
            ])
            ->add('tariff', CheckboxType::class, [
                'label' => 'csv.form.tariff',
                'required' => false,
                'attr' => array('checked' => 'checked'),
                'group' => 'csv.form.group',
            ])
            ->add('createdAt', CheckboxType::class, [
                'label' => 'csv.form.createdAt',
                'required' => false,
                'attr' => array('checked' => 'checked'),
                'group' => 'csv.form.group',
            ])
            ->add('createdBy', CheckboxType::class, [
                'label' => 'csv.form.createdBy',
                'required' => false,
                'attr' => array('checked' => 'checked'),
                'group' => 'csv.form.group',
            ])
            ->add('roomType', HiddenType::class, [
                'required' => false,
            ])
            ->add('status', HiddenType::class, [
                'required' => false,
            ])
            ->add('deleted', HiddenType::class, [
                'required' => false,
            ])
            ->add('begin', HiddenType::class, [
                'required' => false,
            ])
            ->add('end', HiddenType::class, [
                'required' => false,
            ])
            ->add('dates', HiddenType::class, [
                'required' => false,
            ])
            ->add('paid', HiddenType::class, [
                'required' => false,
            ])
            ->add('confirmed', HiddenType::class, [
                'required' => false,
            ])
            ->add('query', HiddenType::class, [
                'required' => false
            ])
            ->add('source', HiddenType::class, [
                'required' => false
            ])
            ->add('quick_link', HiddenType::class, [
                'required' => false,
            ]);

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => null,
            ]);
    }

    public function getBlockPrefix()
    {
        return 'mbh_bundle_packagebundle_package_csv_type';
    }

}