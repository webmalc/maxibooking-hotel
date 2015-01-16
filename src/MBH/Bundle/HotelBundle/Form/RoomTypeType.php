<?php

namespace MBH\Bundle\HotelBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Image;

class RoomTypeType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $fileText = 'Изображние типа номера для онлайн бронирования';

        if($options['imageUrl']) {
            $fileText  = '<a class="fancybox" href="/' . $options['imageUrl'] . '"><i class="fa fa-image"></i> ' . $fileText . '</a>';

            if ($options['deleteImageUrl']) {
                $fileText  .= ' <br> <a href="'. $options['deleteImageUrl'] .'" class="text-danger"><i class="fa fa-trash"></i> Удалить изображение</a>';
            }
        }

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
                ->add('imageFile', 'file', [
                    'label' => 'Изображение',
                    'required' => false,
                    'mapped' => false,
                    'group' => 'Общаяя информация',
                    'help' => $fileText,
                    'constraints' => [new Image()]
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
                ->add('isEnabled', 'checkbox', [
                    'label' => 'Включен?',
                    'group' => 'Настройки',
                    'value' => true,
                    'required' => false,
                    'help' => 'Используется ли тип номера в поиске?'
                ])
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MBH\Bundle\HotelBundle\Document\RoomType',
            'calculationTypes' => [],
            'imageUrl' => null,
            'deleteImageUrl' => null
        ));
    }

    public function getName()
    {
        return 'mbh_bundle_hotelbundle_room_type_type';
    }

}
