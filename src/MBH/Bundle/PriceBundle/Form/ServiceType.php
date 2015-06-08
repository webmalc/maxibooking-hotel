<?php

namespace MBH\Bundle\PriceBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ServiceType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        
        $builder
                ->add('fullTitle', 'text', [
                    'label' => 'Название',
                    'required' => true,
                    'group' => 'Общая информация',
                    'attr' => ['placeholder' => 'Сейф']
                ])
                ->add('title', 'text', [
                    'label' => 'Внутреннее название',
                    'group' => 'Общая информация',
                    'required' => false,
                    'attr' => ['placeholder' => 'Сейф - лето ' . date('Y')],
                    'help' => 'Название для использования внутри MaxiBooking'
                ])
                ->add('description', 'textarea', [
                    'label' => 'Описание',
                    'required' => false,
                    'group' => 'Общая информация',
                    'help' => 'Описание услуги для онлайн бронирования'
                ])
                ->add('calcType', 'choice', [
                    'label' => 'Тип расчета',
                    'group' => 'Общая информация',
                    'required' => true,
                    'empty_value' => '',
                    'multiple' => false,
                    'choices' => $options['calcTypes'],
                ])
                ->add('price', 'text', [
                    'label' => 'Цена',
                    'group' => 'Общая информация',
                    'required' => false,
                    'attr' => ['placeholder' => 'Услуга не используется', 'class' => 'spinner price-spinner'],
                ])
                ->add('date', 'checkbox', [
                    'label' => 'Дата?',
                    'group' => 'Настройки',
                    'value' => true,
                    'required' => false,
                    'help' => 'Использовать ли дату при добавлении услуги к брони?'
                ])
                ->add('time', 'checkbox', [
                    'label' => 'Время?',
                    'group' => 'Настройки',
                    'value' => true,
                    'required' => false,
                    'help' => 'Использовать ли время при добавлении услуги к брони?'
                ])
                ->add('isOnline', 'checkbox', [
                    'label' => 'Онлайн?',
                    'value' => true,
                    'group' => 'Настройки',
                    'required' => false,
                    'help' => 'Использовать ли услугу в онлайн бронировании?'
                ])
                ->add('isEnabled', 'checkbox', [
                    'label' => 'Включена?',
                    'group' => 'Настройки',
                    'value' => true,
                    'required' => false,
                    'help' => 'Доступна ли услуга для продажи?'
                ])
        ;
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
