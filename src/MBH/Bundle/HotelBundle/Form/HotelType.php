<?php

namespace MBH\Bundle\HotelBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class HotelType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('fullTitle', 'text', [
                    'label' => 'Название',
                    'group' => 'Общаяя информация',
                    'required' => true,
                    'attr' => ['placeholder' => 'Мой отель']
                ])
                ->add('title', 'text', [
                    'label' => 'Внутреннее название',
                    'group' => 'Общаяя информация',
                    'required' => false,
                    'attr' => ['placeholder' => 'Отель'],
                    'help' => 'Название для использования внутри MaxiBooking'
                ])
                ->add('prefix', 'text', [
                    'label' => 'Прификс',
                    'group' => 'Общаяя информация',
                    'required' => false,
                    'attr' => ['placeholder' => 'HTL'],
                    'help' => 'Название для использования в документах'
                ])
                ->add('food', 'choice', [
                    'label' => 'Типы питания',
                    'group' => 'Настройки',
                    'required' => true,
                    'multiple' => true,
                    'choices' => $options['food'],
                    'help' => 'Доступные типы питания в отеле'
                ])
                ->add('saleDays', 'text', [
                    'label' => 'Количество дней продажи',
                    'group' => 'Настройки',
                    'required' => true,
                    'attr' => ['placeholder' => 'hotel', 'class' => 'spinner'],
                    'help' => 'На сколько дней открыта продажа от текущего числа'
                ])
                ->add('isDefault', 'checkbox', [
                    'label' => 'Выбран по умолчанию?',
                    'group' => 'Настройки',
                    'value' => true,
                    'required' => false,
                    'help' => 'Выбран по умолчанию при входе в MaxiBooking'
                ])
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MBH\Bundle\HotelBundle\Document\Hotel',
            'food' => []
        ));
    }

    public function getName()
    {
        return 'mbh_bundle_hotelbundle_hoteltype';
    }

}
