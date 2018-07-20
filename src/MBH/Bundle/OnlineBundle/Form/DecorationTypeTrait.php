<?php
/**
 * Created by PhpStorm.
 * Date: 31.05.18
 */

namespace MBH\Bundle\OnlineBundle\Form;


use MBH\Bundle\OnlineBundle\Lib\DecorationCommonData;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

trait DecorationTypeTrait
{
    public function isFullWidth(FormBuilderInterface $builder): FormBuilderInterface
    {
        return $builder->create('isFullWidth', CheckboxType::class, [
            'group'    => 'form.formType.css',
            'label'    => 'form.formType.frame_width.is_full_width.label',
            'required' => false,
            'help'     => 'form.formType.frame_width.is_full_width.help',
        ]);
    }

    public function frameWidth(FormBuilderInterface $builder): FormBuilderInterface
    {
        return $builder->create('frameWidth', IntegerType::class, [
            'label'    => 'form.formType.frame_width.label',
            'group'    => 'form.formType.css',
            'required' => true,
            'help'     => 'form.formType.frame_width.help',
        ]);
    }

    public function frameHeight(FormBuilderInterface $builder): FormBuilderInterface
    {
        return $builder->create('frameHeight', IntegerType::class, [
            'label'    => 'form.formType.frame_height.label',
            'group'    => 'form.formType.css',
            'required' => true,
            'help'     => 'form.formType.frame_height.help',
        ]);
    }

    public function css(FormBuilderInterface $builder): FormBuilderInterface
    {
        return $builder->create('css',
            TextareaType::class,
            [
                'group'    => 'form.formType.css',
                'label'    => 'form.formType.css_label',
                'required' => false,
                'help'     => 'form.formType.css_help',
                'attr'     => ['rows' => 60],
            ]);
    }

    public function isHorizontal(FormBuilderInterface $builder): FormBuilderInterface
    {
        return $builder->create('isHorizontal',
            CheckboxType::class,
            [
                'group'    => 'form.formType.css',
                'label'    => 'form.formType.is_horizontal.label',
                'required' => false,
                'help'     => 'form.formType.is_horizontal.help',
            ]);
    }

    public function cssLibraries(FormBuilderInterface $builder): FormBuilderInterface
    {
        return $builder->create('cssLibraries',
            ChoiceType::class,
            [
                'group'       => 'form.formType.css',
                'choices'     => DecorationCommonData::getCssLibrariesList(),
                'required'    => false,
                'label'       => 'form.formType.label.css_libraries',
                'help'        => 'form.formType.help.css_libraries',
                'choice_attr' => function ($value) {
                    return [
                        'title' => $value,
                    ];
                },
                'multiple'    => true,
            ]);
    }

    public function theme(FormBuilderInterface $builder): FormBuilderInterface
    {
        return $builder->create(
            'theme',
            ChoiceType::class,
            [
                'group'    => 'form.formType.css',
                'choices'  => DecorationCommonData::getThemes(),
                'required' => false,
                'label'    => 'form.formType.theme_label',
                'help'     => 'https://bootswatch.com',
            ]
        );
    }
}