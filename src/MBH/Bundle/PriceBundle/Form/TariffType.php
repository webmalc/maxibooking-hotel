<?php

namespace MBH\Bundle\PriceBundle\Form;

use MBH\Bundle\PriceBundle\Services\PromotionConditionFactory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TariffType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $conditions = PromotionConditionFactory::getAvailableConditions();

        $builder
            ->add('fullTitle', 'text', [
                'label' => 'mbhpricebundle.form.tarifftype.nazvaniye',
                'group' => 'Общая информация',
                'required' => true,
                'attr' => ['placeholder' => 'mbhpricebundle.form.tarifftype.osnovnoy']
            ])
            ->add('title', 'text', [
                'label' => 'mbhpricebundle.form.tarifftype.vnutrenneyenazvaniye',
                'group' => 'Общая информация',
                'required' => false,
                'attr' => ['placeholder' => 'Основной - лето ' . date('Y')],
                'help' => 'mbhpricebundle.form.tarifftype.nazvaniyedlyaispolʹzovaniyavnutriMaxiBooking'
            ])
            ->add('description', 'textarea', [
                'label' => 'mbhpricebundle.form.tarifftype.opisaniye',
                'group' => 'Общая информация',
                'required' => false,
                'help' => 'mbhpricebundle.form.tarifftype.opisaniyetarifadlyaonlaynbronirovaniya'
            ])
            ->add(
                'begin',
                'date',
                array(
                    'label' => 'mbhpricebundle.form.tarifftype.nachalo',
                    'group' => 'Условия и ограничения',
                    'widget' => 'single_text',
                    'format' => 'dd.MM.yyyy',
                    'help' => 'mbhpricebundle.form.tarifftype.skakogochislaispolʹzuyetsyatarif?',
                    'required' => false,
                    'attr' => array(
                        'class' => 'datepicker begin-datepicker input-small',
                        'data-date-format' => 'dd.mm.yyyy',
                        'placeholder' => 'mbhpricebundle.form.tarifftype.neogranichen'
                    ),
                )
            )
            ->add(
                'end',
                'date',
                array(
                    'label' => 'mbhpricebundle.form.tarifftype.konets',
                    'group' => 'Условия и ограничения',
                    'widget' => 'single_text',
                    'format' => 'dd.MM.yyyy',
                    'help' => 'mbhpricebundle.form.tarifftype.pokakoyechisloispolʹzuyetsyatarif?',
                    'required' => false,
                    'attr' => array(
                        'class' => 'datepicker end-datepicker input-small',
                        'data-date-format' => 'dd.mm.yyyy',
                        'placeholder' => 'mbhpricebundle.form.tarifftype.neogranichen'
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
                'label' => 'mbhpricebundle.form.tarifftype.onlayn?',
                'group' => 'Настройки',
                'value' => true,
                'required' => false,
                'help' => 'mbhpricebundle.form.tarifftype.ispolʹzovatʹlitarifvonlaynbronirovanii?'
            ])
            ->add(
                'childAge',
                'choice',
                [
                    'label' => 'mbhpricebundle.form.tarifftype.rebenokdo',
                    'group' => 'Настройки',
                    'required' => false,
                    'multiple' => false,
                    'choices' => range(0, 18),
                    'attr' => array('class' => 'input-xxs plain-html'),
                    'help' => 'mbhpricebundle.form.tarifftype.dokakogovozrastakliyentschitayetsyarebenkom?'
                ]
            )
            ->add(
                'infantAge',
                'choice',
                [
                    'label' => 'mbhpricebundle.form.tarifftype.infantdo',
                    'group' => 'Настройки',
                    'required' => false,
                    'multiple' => false,
                    'choices' => range(0, 18),
                    'attr' => array('class' => 'input-xxs plain-html'),
                    'help' => 'mbhpricebundle.form.tarifftype.dokakogovozrastakliyentschitayetsyainfantom?'
                ]
            )
            ->add('defaultForMerging', CheckboxType::class, [
                'label' => 'mbhpricebundle.form.tarifftype.ispolʹzovatʹdlyakombinirovaniya?',
                'group' => 'Настройки',
                'value' => true,
                'required' => false,
                'help' =>
                    'Использовать для комбинирования тарифов в переходных периодах?<br>
                     По-молчанию спец. тарифы комбинируются с основным тарифом'
            ])
            ->add('isEnabled', 'checkbox', [
                'label' => 'mbhpricebundle.form.tarifftype.vklyuchen?',
                'group' => 'Настройки',
                'value' => true,
                'required' => false,
                'help' => 'mbhpricebundle.form.tarifftype.ispolʹzuyetsyalitarifvpoiske?'
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
