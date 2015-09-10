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
            ->add('type', 'choice', [
                'expanded' => true,
                'choices' => [
                    'Однократная (600 руб.)',
                    'Двукратная (900 руб.)',
                ]
            ])
            ->add('arrival', 'date', [
                'widget' => 'single_text'
            ])
            ->add('departure', 'date', [
                'widget' => 'single_text'
            ])
            ->add('reservation', 'choice', [
                'expanded' => true,
                'choices' => [
                    1,2,3,4,5
                ]
            ])
            //->add('city', 'text')
            ->add('firstName', 'text')
            ->add('lastName', 'text')
            ->add('sex', 'choice', [
                'expanded' => true,
                'choices' => [
                    'Господин', 'Госпожа'
                ]
            ])
            ->add('birthday', 'date', [
                'widget' => 'single_text'
            ])
            ->add('birthplace', 'date', [
                'widget' => 'single_text'
            ])
            ->add('passport', 'text')
            ->add('expiry', 'date', [
                'widget' => 'single_text'
            ])
            ->add('citizenship', 'text')
            //->add('agree', 'checkbox')
        ;
    }


    public function configureOptions(OptionsResolver $resolver)
    {

    }

    public function getName()
    {
        return 'mbh_online_bundle_invite';
    }

}
