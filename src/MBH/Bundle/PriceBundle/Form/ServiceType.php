<?php

namespace MBH\Bundle\PriceBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ServiceType
 */
class ServiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fullTitle', TextType::class, [
                'label' => 'Название',
                'required' => true,
                'group' => 'Общая информация',
                'attr' => ['placeholder' => 'Сейф']
            ])
            ->add('title', TextType::class, [
                'label' => 'Внутреннее название',
                'group' => 'Общая информация',
                'required' => false,
                'attr' => ['placeholder' => 'Сейф - лето ' . date('Y')],
                'help' => 'Название для использования внутри MaxiBooking'
            ])
            ->add('international_title', TextType::class, [
                'label' => 'form.roomTypeType.international_title',
                'required' => false,
                'group' => 'Общая информация',
                //'help' => 'Международное название'
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Описание',
                'required' => false,
                'group' => 'Общая информация',
                'help' => 'Описание услуги для онлайн бронирования'
            ])
            ->add('calcType', \MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType::class, [
                'label' => 'Тип расчета',
                'group' => 'Общая информация',
                'required' => true,
                'placeholder' => '',
                'multiple' => false,
                'choices' => $options['calcTypes'],
            ])
            ->add('recalcWithPackage', CheckboxType::class, [
                'label' => 'Смещаемая?',
                'value' => true,
                'group' => 'Общая информация',
                'required' => false,
                'help' => 'Смещать ли даты услуги при изменении дат брони?',
                'attr' => ['class' => 'toggle-date'],
            ])
            ->add('includeArrival', CheckboxType::class, [
                'label' => 'Учитывать заезд?',
                'value' => true,
                'group' => 'Общая информация',
                'required' => false,
                'help' => 'Учитывать ли дату заезда брони?',
                'attr' => ['class' => 'toggle-date'],
            ])
            ->add('includeDeparture', CheckboxType::class, [
                'label' => 'Учитывать выезд?',
                'value' => true,
                'group' => 'Общая информация',
                'required' => false,
                'help' => 'Учитывать ли дату выезда брони?',
                'attr' => ['class' => 'toggle-date'],
            ])
            ->add('price', TextType::class, [
                'label' => 'Цена',
                'group' => 'Общая информация',
                'required' => false,
                'attr' => ['placeholder' => 'Услуга не используется', 'class' => 'spinner price-spinner'],
            ])
            ->add('date', CheckboxType::class, [
                'label' => 'Дата?',
                'group' => 'Настройки',
                'value' => true,
                'required' => false,
                'help' => 'Использовать ли дату при добавлении услуги к брони?'
            ])
            ->add('time', CheckboxType::class, [
                'label' => 'Время?',
                'group' => 'Настройки',
                'value' => true,
                'required' => false,
                'help' => 'Использовать ли время при добавлении услуги к брони?'
            ])
            ->add('isOnline', CheckboxType::class, [
                'label' => 'Онлайн?',
                'value' => true,
                'group' => 'Настройки',
                'required' => false,
                'help' => 'Использовать ли услугу в онлайн бронировании?'
            ])
            ->add('isEnabled', CheckboxType::class, [
                'label' => 'Включена?',
                'group' => 'Настройки',
                'value' => true,
                'required' => false,
                'help' => 'Доступна ли услуга для продажи?'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MBH\Bundle\PriceBundle\Document\Service',
            'calcTypes' => []
        ));
    }

    public function getBlockPrefix()
    {
        return 'mbh_bundle_pricebundle_service_type';
    }
}
