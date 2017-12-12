<?php

namespace MBH\Bundle\PackageBundle\Form;


use MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType;
use MBH\Bundle\PackageBundle\Document\Unwelcome;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Class UnwelcomeItem
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
            0 => 'form.unwelcomeType.levels.no',
            1 => 'form.unwelcomeType.levels.minor',
            2 => 'form.unwelcomeType.levels.low',
            3 => 'form.unwelcomeType.levels.middle',
            4 => 'form.unwelcomeType.levels.high',
            5 => 'form.unwelcomeType.levels.very_high'
        ];

        foreach($this->getCharacteristics() as $characteristic) {
            $builder->add($characteristic, InvertChoiceType::class, [
                'label' => 'form.unwelcomeType.'.$characteristic,
                'group' => 'form.unwelcomeType.group.common',
                'expanded' => true,
                'placeholder' => null,
                'choices' => $levels,
                'choice_label' => function($key){
                    return $key == 0 ? 'form.unwelcomeType.levels.no' : $key;
                },
                'choice_attr' => function($key, $value) {
                    return $key > 0 ? [
                        'data-toggle' => 'tooltip',
                        'data-original-title' => $value
                    ] : [];
                }
            ]);
        }

        $builder->add('comment', TextareaType::class, [
            'label' => 'form.unwelcomeType.comment',
            'group' => 'form.unwelcomeType.group.common',
            'attr' => ['style' => 'height:150px'],
            'help' => 'form.unwelcomeType.comment.help'
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
                    $context->addViolation('validator.document.Unwelcome.need_feel_at_least_one_characteristic');
                }])
            ],
        ]);
    }


    public function getBlockPrefix()
    {
        return 'mbh_package_bundle_unwelcome';
    }
}