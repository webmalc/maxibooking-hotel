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
                        'value' => true,
                        'required' => false,
                        'help' => 'Использовать ли поле "Количестов гостей" в онлайн форме?'
                    ]
                )
            ;
        }
        $builder
            ->add(
                'paymentTypes',
                'choice',
                [
                    'choices' => $options['paymentTypes'],
                    'label' => 'Типы оплаты',
                    'multiple' => true,
                    'help' => 'Типы оплаты при бронировании с помощью онлайн формы'
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
