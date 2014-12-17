<?php

namespace MBH\Bundle\OnlineBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FormType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'enabled',
                'checkbox',
                [
                    'label' => 'Включена?',
                    'group' => 'Параметры',
                    'value' => true,
                    'required' => false,
                    'help' => 'Использовать ли онлайн форму?'
                ]
            )
            ->add(
                'roomTypes',
                'checkbox',
                [
                    'label' => 'Типы номеров?',
                    'group' => 'Параметры',
                    'value' => true,
                    'required' => false,
                    'help' => 'Использовать ли поле "Тип номера" в онлайн форме?'
                ]
            );
        if (!$options['isHostel']) {
            $builder
                ->add(
                    'tourists',
                    'checkbox',
                    [
                        'label' => 'Гости?',
                        'group' => 'Параметры',
                        'value' => true,
                        'required' => false,
                        'help' => 'Использовать ли поле "Количестов гостей" в онлайн форме?'
                    ]
                )
            ;
        }
        $builder
            ->add(
                'nights',
                'checkbox',
                [
                    'label' => 'Поле "ночи"?',
                    'group' => 'Параметры',
                    'value' => true,
                    'required' => false,
                    'help' => 'Использовать ли поле дату заезда и количестов ночей или дату заезда/выезда?'
                ]
            )
            ->add(
                'paymentTypes',
                'choice',
                [
                    'group' => 'Оплата',
                    'choices' => $options['paymentTypes'],
                    'label' => 'Типы оплаты',
                    'multiple' => true,
                    'help' => 'Типы оплаты при бронировании с помощью онлайн формы'
                ]
            )
            ->add(
                'robokassaMerchantLogin',
                'text',
                [
                    'group' => 'Сервис ROBOKASSA',
                    'label' => 'Логин магазина',
                    'required' => false,
                    'attr' => ['class' => 'robokassa-params']
                ]
            )
            ->add(
                'robokassaMerchantPass1',
                'text',
                [
                    'group' => 'Сервис ROBOKASSA',
                    'label' => 'Пароль 1',
                    'required' => false,
                    'attr' => ['class' => 'robokassa-params']
                ]
            )
            ->add(
                'robokassaMerchantPass2',
                'text',
                [
                    'group' => 'Сервис ROBOKASSA',
                    'label' => 'Пароль 2',
                    'required' => false,
                    'attr' => ['class' => 'robokassa-params']
                ]
            )
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'MBH\Bundle\OnlineBundle\Document\FormConfig',
                'paymentTypes' => [],
                'isHostel' => false
            )
        );
    }

    public function getName()
    {
        return 'mbh_bundle_onlinebundle_form_type';
    }

}
