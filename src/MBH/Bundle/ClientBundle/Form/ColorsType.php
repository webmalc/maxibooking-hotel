<?php

namespace MBH\Bundle\ClientBundle\Form;

use MBH\Bundle\ClientBundle\Document\ColorsConfig;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ColorsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('successColor', TextType::class, [
                'label' => 'form.colors_type.success_color.label',
                'required' => true,
                'group' => 'form.colors_type.group.packages',
                'addon' => 'fa fa-eyedropper',
                'help' => 'form.colors_type.success_color.help',
                'attr' => [
                    'class' => 'color-picker'
                ]
            ])
            ->add('warningColor', TextType::class, [
                'label' => 'form.colors_type.warning_color.label',
                'required' => true,
                'group' => 'form.colors_type.group.packages',
                'attr' => [
                    'placeholder' => '008000',
                    'class' => 'color-picker'
                ],
                'addon' => 'fa fa-eyedropper',
                'help' => 'form.colors_type.warning_color.help'
            ])
            ->add('dangerColor', TextType::class, [
                'label' => 'form.colors_type.danger_color.label',
                'required' => true,
                'group' => 'form.colors_type.group.packages',
                'attr' => [
                    'placeholder' => '008000',
                    'class' => 'color-picker'
                ],
                'addon' => 'fa fa-eyedropper',
                'help' => 'form.colors_type.danger_color.help'
            ])
            ->add('leftRoomsPositiveColor', TextType::class, [
                'label' => 'form.colors_type.left_rooms_positive.label',
                'required' => true,
                'group' => 'form.colors_type.group.chessboard',
                'attr' => [
                    'placeholder' => '008000',
                    'class' => 'color-picker'
                ],
                'addon' => 'fa fa-eyedropper',
                'help' => 'form.colors_type.left_rooms_positive.help'
            ])
            ->add('leftRoomsZeroColor', TextType::class, [
                'label' => 'form.colors_type.left_rooms_zero.label',
                'required' => true,
                'group' => 'form.colors_type.group.chessboard',
                'attr' => [
                    'placeholder' => '008000',
                    'class' => 'color-picker'
                ],
                'addon' => 'fa fa-eyedropper',
                'help' => 'form.colors_type.left_rooms_positive.help'
            ])
            ->add('leftRoomsNegativeColor', TextType::class, [
                'label' => 'form.colors_type.left_rooms_negative.label',
                'required' => true,
                'group' => 'form.colors_type.group.chessboard',
                'attr' => [
                    'placeholder' => '008000',
                    'class' => 'color-picker'
                ],
                'addon' => 'fa fa-eyedropper',
                'help' => 'form.colors_type.left_rooms_negative.help'
            ])
            ->add('unplacedColor', TextType::class, [
                'label' => 'form.colors_type.unplaced_color.label',
                'required' => true,
                'group' => 'form.colors_type.group.chessboard',
                'attr' => [
                    'placeholder' => '008000',
                    'class' => 'color-picker'
                ],
                'addon' => 'fa fa-eyedropper',
                'help' => 'form.colors_type.unplaced_color.help'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ColorsConfig::class
        ]);
    }

    public function getBlockPrefix()
    {
        return 'mbhclient_bundle_colors_type';
    }
}
