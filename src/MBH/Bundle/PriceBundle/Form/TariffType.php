<?php

namespace MBH\Bundle\PriceBundle\Form;

use MBH\Bundle\PriceBundle\Services\PromotionConditionFactory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TariffType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $conditions = PromotionConditionFactory::getAvailableConditions();

        $builder
            ->add('fullTitle', 'text', [
                'label' => 'Название',
                'group' => 'Общая информация',
                'required' => true,
                'attr' => ['placeholder' => 'Основной']
            ])
            ->add('title', 'text', [
                'label' => 'Внутреннее название',
                'group' => 'Общая информация',
                'required' => false,
                'attr' => ['placeholder' => 'Основной - лето ' . date('Y')],
                'help' => 'Название для использования внутри MaxiBooking'
            ])
            ->add('description', 'textarea', [
                'label' => 'Описание',
                'group' => 'Общая информация',
                'required' => false,
                'help' => 'Описание тарифа для онлайн бронирования'
            ])
            ->add(
                'begin',
                'date',
                array(
                    'label' => 'Начало',
                    'group' => 'Условия и ограничения',
                    'widget' => 'single_text',
                    'format' => 'dd.MM.yyyy',
                    'help' => 'С какого числа используется тариф?',
                    'required' => false,
                    'attr' => array(
                        'class' => 'datepicker begin-datepicker input-small',
                        'data-date-format' => 'dd.mm.yyyy',
                        'placeholder' => 'Не ограничен'
                    ),
                )
            )
            ->add(
                'end',
                'date',
                array(
                    'label' => 'Конец',
                    'group' => 'Условия и ограничения',
                    'widget' => 'single_text',
                    'format' => 'dd.MM.yyyy',
                    'help' => 'По какое число используется тариф?',
                    'required' => false,
                    'attr' => array(
                        'class' => 'datepicker end-datepicker input-small',
                        'data-date-format' => 'dd.mm.yyyy',
                        'placeholder' => 'Не ограничен'
                    ),
                )
            );
        $conditions = PromotionConditionFactory::getAvailableConditions();
        $builder
            ->add('condition', 'choice', [
                'label' => 'form.promotionType.label.condition',
                'required' => false,
                'group' => 'Условия и ограничения',
                'choices' => array_combine($conditions, $conditions),
                'choice_label' => function ($value, $label) {
                    return 'form.promotionType.choice_label.condition.' . $value;
                }
            ])
            ->add('condition_quantity', 'number', [
                'label' => 'form.promotionType.label.condition_quantity',
                'group' => 'Условия и ограничения',
                'required' => false,
                'error_bubbling' => false,
                'attr' => [
                    'class' => 'spinner',
                ],
            ])
            ->add('additional_condition', 'choice', [
                'label' => 'form.promotionType.label.add_condition',
                'required' => false,
                'group' => 'Условия и ограничения',
                'choices' => array_combine($conditions, $conditions),
                'choice_label' => function ($value, $label) {
                    return 'form.promotionType.choice_label.condition.' . $value;
                }
            ])
            ->add('additional_condition_quantity', 'number', [
                'label' => 'form.promotionType.label.condition_quantity',
                'group' => 'Условия и ограничения',
                'required' => false,
                'error_bubbling' => false,
                'attr' => [
                    'class' => 'spinner',
                ],
            ]);
        $builder
            ->add('isOnline', 'checkbox', [
                'label' => 'Онлайн?',
                'group' => 'Настройки',
                'value' => true,
                'required' => false,
                'help' => 'Использовать ли тариф в онлайн бронировании?'
            ])
            ->add(
                'childAge',
                'choice',
                [
                    'label' => 'Ребенок до',
                    'group' => 'Настройки',
                    'required' => false,
                    'multiple' => false,
                    'choices' => range(0, 18),
                    'attr' => array('class' => 'input-xxs plain-html'),
                    'help' => 'До какого возраста клиент считается ребенком?'
                ]
            )
            ->add(
                'infantAge',
                'choice',
                [
                    'label' => 'Инфант до',
                    'group' => 'Настройки',
                    'required' => false,
                    'multiple' => false,
                    'choices' => range(0, 18),
                    'attr' => array('class' => 'input-xxs plain-html'),
                    'help' => 'До какого возраста клиент считается инфантом?'
                ]
            )
            ->add('isEnabled', 'checkbox', [
                'label' => 'Включен?',
                'group' => 'Настройки',
                'value' => true,
                'required' => false,
                'help' => 'Используется ли тариф в поиске?'
            ]);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MBH\Bundle\PriceBundle\Document\Tariff'
        ));
    }

    public function getName()
    {
        return 'mbh_bundle_pricebundle_tariff_main_type';
    }

}
