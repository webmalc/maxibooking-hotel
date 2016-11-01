<?php

namespace MBH\Bundle\PriceBundle\Form;

use MBH\Bundle\PriceBundle\Lib\PaymentType;
use MBH\Bundle\PriceBundle\Services\PromotionConditionFactory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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
            ->add('defaultForMerging', CheckboxType::class, [
                'label' => 'Использовать для комбинирования?',
                'group' => 'Настройки',
                'value' => true,
                'required' => false,
                'help' =>
                    'Использовать для комбинирования тарифов в переходных периодах?<br>
                     По-молчанию спец. тарифы комбинируются с основным тарифом'
            ])
            ->add('paymentType', ChoiceType::class, [
                'label' => 'Процент для первой оплаты',
                'group' => 'Настройки',
                'required' => false,
                'help' => 'Поле для выбора и формирования первой оплаты в онлайне при оплате брони по частям',
                'choices' => array_combine(array_keys(PaymentType::getPercentChoices()), array_keys(PaymentType::getPercentChoices())),
                'choice_label' => function ($value) {
                    return PaymentType::PAYMENT_TYPE_LIST[$value]['description'];
                }
            ])
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
