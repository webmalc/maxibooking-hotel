<?php

namespace MBH\Bundle\OnlineBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class InviteType
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
class InviteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('arrival', 'date', [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('departure', 'date', [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('type', 'choice', [
                'expanded' => true,
                'choices' => [
                    'Однократная (600 руб.)',
                    'Двукратная (900 руб.)',
                ],
                'required' => false,
                'empty_value' => null,
            ])
            ->add('guests', 'collection', [
                'type' => new InvitedTouristType(),
                'allow_add' => true,
                'prototype' => true
            ])
            //->add('agree', 'checkbox')
        ;
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            //'cascade_validation' => true,
            //'validation_groups' => true
        ]);
    }

    public function getName()
    {
        return 'mbh_online_bundle_invite';
    }

}
