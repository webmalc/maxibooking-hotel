<?php

namespace MBH\Bundle\PriceBundle\Form;

use MBH\Bundle\PriceBundle\Services\PromotionConditionFactory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TariffType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $conditions = PromotionConditionFactory::getAvailableConditions();

        $builder
            ->add('fullTitle', TextType::class, [
                'label' => 'Название',
                'group' => 'Общая информация',
                'required' => true,
                'attr' => ['placeholder' => 'Основной']
            ])
            ->add('title', TextType::class, [
                'label' => 'Внутреннее название',
                'group' => 'Общая информация',
                'required' => false,
                'attr' => ['placeholder' => 'Основной - лето ' . date('Y')],
                'help' => 'Название для использования внутри MaxiBooking'
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Описание',
                'group' => 'Общая информация',
                'required' => false,
                'help' => 'Описание тарифа для онлайн бронирования'
            ])
            ->add(
                'begin',
                DateType::class,
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
                DateType::class,
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
            ->add('condition',  \MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType::class, [
                'label' => 'form.promotionType.label.condition',
                'required' => false,
                'group' => 'Условия и ограничения',
                'choices' => array_combine($conditions, $conditions),
                'choice_label' => function ($value, $label) {
                    return 'form.promotionType.choice_label.condition.' . $value;
                }
            ])
            ->add('condition_quantity', NumberType::class, [
                'label' => 'form.promotionType.label.condition_quantity',
                'group' => 'Условия и ограничения',
                'required' => false,
                'error_bubbling' => false,
                'attr' => [
                    'class' => 'spinner',
                ],
            ])
            ->add('additional_condition',  \MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType::class, [
                'label' => 'form.promotionType.label.add_condition',
                'required' => false,
                'group' => 'Условия и ограничения',
                'choices' => array_combine($conditions, $conditions),
                'choice_label' => function ($value, $label) {
                    return 'form.promotionType.choice_label.condition.' . $value;
                }
            ])
            ->add('additional_condition_quantity', NumberType::class, [
                'label' => 'form.promotionType.label.condition_quantity',
                'group' => 'Условия и ограничения',
                'required' => false,
                'error_bubbling' => false,
                'attr' => [
                    'class' => 'spinner',
                ],
            ]);
        $builder
            ->add('isOnline', CheckboxType::class, [
                'label' => 'Онлайн?',
                'group' => 'configuration',
                'value' => true,
                'required' => false,
                'help' => 'Использовать ли тариф в онлайн бронировании?'
            ])
            ->add(
                'childAge',  \MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType::class,
                [
                    'label' => 'Ребенок до',
                    'group' => 'configuration',
                    'required' => false,
                    'multiple' => false,
                    'choices' => range(0, 18),
                    'attr' => array('class' => 'input-xxs plain-html'),
                    'help' => 'До какого возраста клиент считается ребенком?'
                ]
            )
            ->add(
                'infantAge',  \MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType::class,
                [
                    'label' => 'Инфант до',
                    'group' => 'configuration',
                    'required' => false,
                    'multiple' => false,
                    'choices' => range(0, 18),
                    'attr' => array('class' => 'input-xxs plain-html'),
                    'help' => 'До какого возраста клиент считается инфантом?'
                ]
            )
            ->add('defaultForMerging', CheckboxType::class, [
                'label' => 'Использовать для комбинирования?',
                'group' => 'configuration',
                'value' => true,
                'required' => false,
                'help' =>
                    'Использовать для комбинирования тарифов в переходных периодах?<br>
                     По-молчанию спец. тарифы комбинируются с основным тарифом'
            ])
            ->add('paymentType', ChoiceType::class, [
                'label' => 'Процент для первой оплаты',
                'group' => 'configuration',
                'required' => false,
                'help' => 'Поле для выбора и формирования первой оплаты в онлайне при оплате брони по частям',
                'choices' => array_combine(array_keys(PaymentType::getPercentChoices()), array_keys(PaymentType::getPercentChoices())),
                'choice_label' => function ($value) {
                    return PaymentType::PAYMENT_TYPE_LIST[$value]['description'];
                }
            ])
            ->add('position', NumberType::class, [
                'label' => 'position',
                'help' => 'position.help',
                'group' => 'configuration',
                'required' => true,
                'attr' => [
                    'class' => 'spinner-0',
                ],
            ])
            ->add('isEnabled', CheckboxType::class, [
                'label' => 'Включен?',
                'group' => 'configuration',
                'value' => true,
                'required' => false,
                'help' => 'Используется ли тариф в поиске?'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MBH\Bundle\PriceBundle\Document\Tariff'
        ));
    }

    public function getBlockPrefix()
    {
        return 'mbh_bundle_pricebundle_tariff_main_type';
    }

}
