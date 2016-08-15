<?php

namespace MBH\Bundle\PriceBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class ServiceType
 */
class ServiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fullTitle', 'text', [
                'label' => 'mbhpricebundle.form.servicetype.nazvaniye',
                'required' => true,
                'group' => 'Общая информация',
                'attr' => ['placeholder' => 'mbhpricebundle.form.servicetype.seyf']
            ])
            ->add('title', 'text', [
                'label' => 'mbhpricebundle.form.servicetype.vnutrenneyenazvaniye',
                'group' => 'Общая информация',
                'required' => false,
                'attr' => ['placeholder' => 'Сейф - лето ' . date('Y')],
                'help' => 'Название для использования внутри MaxiBooking'
            ])
            ->add('international_title', 'text', [
                'label' => 'form.roomTypeType.international_title',
                'required' => false,
                'group' => 'Общая информация',
                //'help' => 'mbhpricebundle.form.servicetype.mezhdunarodnoyenazvaniye'
            ])
            ->add('description', 'textarea', [
                'label' => 'mbhpricebundle.form.servicetype.opisaniye',
                'required' => false,
                'group' => 'Общая информация',
                'help' => 'mbhpricebundle.form.servicetype.opisaniyeuslugidlyaonlaynbronirovaniya'
            ])
            ->add('calcType', 'choice', [
                'label' => 'mbhpricebundle.form.servicetype.tiprascheta',
                'group' => 'Общая информация',
                'required' => true,
                'empty_value' => '',
                'multiple' => false,
                'choices' => $options['calcTypes'],
            ])
            ->add('price', 'text', [
                'label' => 'mbhpricebundle.form.servicetype.tsena',
                'group' => 'Общая информация',
                'required' => false,
                'attr' => ['placeholder' => 'mbhpricebundle.form.servicetype.usluganeispolʹzuyetsya', 'class' => 'spinner price-spinner'],
            ])
            ->add('date', 'checkbox', [
                'label' => 'mbhpricebundle.form.servicetype.data?',
                'group' => 'Настройки',
                'value' => true,
                'required' => false,
                'help' => 'mbhpricebundle.form.servicetype.ispolʹzovatʹlidatupridobavleniiuslugikbroni?'
            ])
            ->add('time', 'checkbox', [
                'label' => 'mbhpricebundle.form.servicetype.vremya?',
                'group' => 'Настройки',
                'value' => true,
                'required' => false,
                'help' => 'mbhpricebundle.form.servicetype.ispolʹzovatʹlivremyapridobavleniiuslugikbroni?'
            ])
            ->add('isOnline', 'checkbox', [
                'label' => 'mbhpricebundle.form.servicetype.onlayn?',
                'value' => true,
                'group' => 'Настройки',
                'required' => false,
                'help' => 'mbhpricebundle.form.servicetype.ispolʹzovatʹliusluguvonlaynbronirovanii?'
            ])
            ->add('isEnabled', 'checkbox', [
                'label' => 'mbhpricebundle.form.servicetype.vklyuchena?',
                'group' => 'Настройки',
                'value' => true,
                'required' => false,
                'help' => 'mbhpricebundle.form.servicetype.dostupnaliuslugadlyaprodazhi?'
            ]);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MBH\Bundle\PriceBundle\Document\Service',
            'calcTypes' => []
        ));
    }

    public function getName()
    {
        return 'mbh_bundle_pricebundle_service_type';
    }

}
