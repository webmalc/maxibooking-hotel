<?php

namespace MBH\Bundle\PriceBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TariffMainType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $days = [
            1 => 'Понедельник',
            2 => 'Вторник',
            3 => 'Среда',
            4 => 'Четверг',
            5 => 'Пятница',
            6 => 'Суббота',
            7 => 'Воскресенье',
        ];

        $builder
                ->add('fullTitle', 'text', [
                    'label' => 'Название',
                    'group' => 'Общаяя информация',
                    'required' => true,
                    'attr' => ['placeholder' => 'Основной']
                ])
                ->add('title', 'text', [
                    'label' => 'Внутреннее название',
                    'group' => 'Общаяя информация',
                    'required' => false,
                    'attr' => ['placeholder' => 'Основной - лето ' . date('Y')],
                    'help' => 'Название для использования внутри MaxiBooking'
                ])
                ->add('description', 'textarea', [
                    'label' => 'Описание',
                    'group' => 'Общаяя информация',
                    'required' => false,
                    'help' => 'Описание тарифа для онлайн бронирования'
                ])
                ->add('begin', 'date', array(
                    'label' => 'Начало',
                    'group' => 'Общаяя информация',
                    'widget' => 'single_text',
                    'format' => 'dd.MM.yyyy',
                    'required' => true,
                    'attr' => array('class' => 'datepicker begin-datepiker', 'data-date-format' => 'dd.mm.yyyy'),
                    'help' => 'Дата начала действия тарифа'
                ))
                ->add('end', 'date', array(
                    'label' => 'Конец',
                    'group' => 'Общаяя информация',
                    'widget' => 'single_text',
                    'format' => 'dd.MM.yyyy',
                    'required' => true,
                    'attr' => array('class' => 'datepicker end-datepiker', 'data-date-format' => 'dd.mm.yyyy'),
                    'help' => 'Дата конца действия тарифа'
                ))
                ->add('weekDays', 'choice', [
                    'label' => 'Дни недели',
                    'group' => 'Общаяя информация',
                    'required' => false,
                    'multiple' => true,
                    'choices' => $days,
                    'help' => 'Дни в которые тариф активен. Если дни не выбраны, то тариф активен всю неделю.'
                ])
                ->add('minPackageDuration', 'text', [
                    'label' => 'Минимальная длина брони',
                    'group' => 'Настройки',
                    'required' => false,
                    'attr' => ['placeholder' => 'Используются настройки отеля', 'class' => 'spinner'],
                    'help' => 'Минимальная продолжительность брони в ночах'
                ])
                ->add('maxPackageDuration', 'text', [
                    'label' => 'Максимальная длина брони',
                    'group' => 'Настройки',
                    'required' => false,
                    'attr' => ['placeholder' => 'Используются настройки отеля', 'class' => 'spinner'],
                    'help' => 'Максимальная продолжительность брони в ночах'
                ])
                ->add('isOnline', 'checkbox', [
                    'label' => 'Онлайн?',
                    'group' => 'Настройки',
                    'value' => true,
                    'required' => false,
                    'help' => 'Использовать ли тариф в онлайн бронировании?'
                ])
                ->add('isDefault', 'checkbox', [
                    'label' => 'Основной тариф?',
                    'group' => 'Настройки',
                    'value' => true,
                    'required' => false,
                ])
                ->add('type', 'choice', [
                    'label' => 'Тип тарифа',
                    'group' => 'Настройки',
                    'required' => false,
                    'multiple' => false,
                    'empty_value' => '',
                    'choices' => $options['types']
                ])
                ->add('rate', 'text', [
                    'label' => 'Скидка/наценка',
                    'group' => 'Настройки',
                    'required' => true,
                    'attr' => ['placeholder' => '20', 'class' => 'spinner'],
                    'help' => 'Процент от цен основного тарифа в этом периоде'
                ])
                ->add('isEnabled', 'checkbox', [
                    'label' => 'Включен?',
                    'group' => 'Настройки',
                    'value' => true,
                    'required' => false,
                    'help' => 'Используется ли тариф в поиске?'
                ])

        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MBH\Bundle\PriceBundle\Document\Tariff',
            'types' => []
        ));
    }

    public function getName()
    {
        return 'mbh_bundle_pricebundle_tariff_main_type';
    }

}
