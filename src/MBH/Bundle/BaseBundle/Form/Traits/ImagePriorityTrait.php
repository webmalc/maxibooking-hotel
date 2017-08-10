<?php


namespace MBH\Bundle\BaseBundle\Form\Traits;


use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Range;

trait ImagePriorityTrait
{
    public function addPriorityType(FormBuilderInterface $builder)
    {
        $builder
            ->add(
                'priority',
                IntegerType::class,
                [
                    'label' => 'form.image.priority.label',
                    'attr' => [
                        'size' => 5,
                        'step' => 10,
                        'min' => -1000,
                        'max' => 1000,
                    ],
                    'help' => 'form.image.priority.help',
                    'constraints' => [
                        new Range(
                            [
                                'min' => -1000,
                                'max' => 1000,
                                'minMessage' => 'validator.form.image.min.error',
                                'maxMessage' => 'validator.form.image.max.error',
                                'invalidMessage' => 'validator.form.image.type.error'
                            ]
                        ),
                    ],
                    'data' => 0
                ]
            );
    }
}