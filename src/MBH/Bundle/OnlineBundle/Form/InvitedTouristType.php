<?php

namespace MBH\Bundle\OnlineBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class InvitedTouristType
 * @package MBH\Bundle\OnlineBundle\Form
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
class InvitedTouristType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', 'text', [
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->add('lastName', 'text', [
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->add('sex', 'choice', [
                'expanded' => true,
                'choices' => [
                    'Господин',
                    'Госпожа'
                ],
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->add('birthday', 'date', [
                'widget' => 'single_text',
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->add('birthplace', 'date', [
                'widget' => 'single_text',
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->add('passport', 'text', [
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->add('expiry', 'date', [
                'widget' => 'single_text',
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->add('citizenship', 'text', [
                'constraints' => [
                    new NotBlank()
                ],
            ]);
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            //'compound' => true
        ]);
    }

    public function getName()
    {
        return 'mbh_online_bundle_invited_tourist';
    }

}
