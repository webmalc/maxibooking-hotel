<?php

namespace MBH\Bundle\HotelBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RoomTypeType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('fullTitle', 'text', [
                    'label' => 'Название',
                    'required' => true,
                    'group' => 'Общаяя информация',
                    'attr' => ['placeholder' => 'Комфорт плюс']
                ])
                ->add('title', 'text', [
                    'label' => 'Внутреннее название',
                    'required' => false,
                    
                    'group' => 'Общаяя информация',
                    'attr' => ['placeholder' => 'Комфорт плюс - номера в новом корпусе'],
                    'help' => 'Название для использования внутри MaxiBooking'
                ])
                ->add('description', 'textarea', [
                     'label' => 'Описание',
                     'help' => 'Описание типа номера для онлайн бронирования',
                     'required' => false,
                     'group' => 'Общаяя информация',
                     'attr' => ['class' => 'big']
                ])
                ->add('color', 'text', [
                    'label' => 'Цвет',
                    'required' => true,
                    'group' => 'Общаяя информация',
                    'attr' => ['placeholder' => '008000'],
                    'help' => 'Цвет типа номера на шахматке'
                ])
                ->add('calculationType', 'choice', [
                    'label' => 'Способ расчета',
                    'group' => 'Настройки',
                    'required' => true,
                    'multiple' => false,
                    'empty_value' => '',
                    'choices' => $options['calculationTypes']
                ])
                ->add('places', 'text', [
                    'label' => 'Основные места',
                    'group' => 'Настройки',
                    'required' => true,
                    'attr' => ['placeholder' => 'hotel', 'class' => 'spinner'],
                    'help' => 'Количество основных мест в номере'
                ])
                ->add('additionalPlaces', 'text', [
                    'label' => 'Дополнительные места',
                    'group' => 'Настройки',
                    'required' => true,
                    'attr' => ['placeholder' => 'hotel', 'class' => 'spinner'],
                    'help' => 'Количество дополнительных мест в номере'
                ])
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MBH\Bundle\HotelBundle\Document\RoomType',
            'calculationTypes' => []
        ));
    }

    public function getName()
    {
        return 'mbh_bundle_hotelbundle_room_type_type';
    }

}
