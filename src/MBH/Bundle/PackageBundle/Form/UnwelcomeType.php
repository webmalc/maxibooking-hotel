<?php

namespace MBH\Bundle\PackageBundle\Form;


use MBH\Bundle\PackageBundle\Document\Unwelcome;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Class UnwelcomeItem
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
class UnwelcomeType extends AbstractType
{
    public static function getCharacteristics()
    {
        return [
            'foul',
            'aggression',
            'inadequacy',
            'drunk',
            'drugs',
            'destruction',
            'materialDamage'
        ];
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $levels = [
            0 => 'Нет',
            1 => 'Незначительная',
            2 => 'Низкая',
            3 => 'Средняя',
            4 => 'Высокая',
            5 => 'Очень высокая'
        ];

        foreach($this->getCharacteristics() as $characteristic) {
            $builder->add($characteristic, 'choice', [
                'label' => 'form.unwelcomeType.'.$characteristic,
                'group' => 'form.unwelcomeType.group.common',
                'expanded' => true,
                'empty_value' => null,
                'choices' => $levels,
                'choice_label' => function($key, $value){
                    return $key == 0 ? 'Нет' : $key;
                },
                'choice_attr' => function($key, $value) {
                    return $key > 0 ? [
                        'data-toggle' => 'tooltip',
                        'data-original-title' => $value
                    ] : [];
                }
            ]);
        }

        $builder->add('comment', 'textarea', [
            'label' => 'form.unwelcomeType.comment',
            'group' => 'form.unwelcomeType.group.common',
            'attr' => ['style' => 'height:150px']
        ]);
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'MBH\Bundle\PackageBundle\Document\Unwelcome',
            'constraints' => [
                //new Length(['min' => 1 , 'max' => 3]),
                new Callback(['callback' => function(Unwelcome $unwelcome, ExecutionContextInterface $context) {
                    foreach(UnwelcomeType::getCharacteristics() as $characteristic) {
                        $value = call_user_func_array([$unwelcome, 'get'.mb_convert_case($characteristic, MB_CASE_TITLE)], []);
                        if($value) {
                           return;
                        }
                    }
                    $context->addViolation('Оцените хотя бы одну характеристику гостя');
                }])
            ],
        ]);
    }


    public function getName()
    {
        return 'mbh_package_bundle_unwelcome';
    }
}