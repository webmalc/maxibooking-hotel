<?php

namespace MBH\Bundle\OnlineBundle\Form;

use MBH\Bundle\OnlineBundle\Document\Invite;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class InviteType

 */
class InviteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('arrival', new DateType(), [
                'widget' => 'single_text',
                //'format' => 'yyyy-MM-dd',
                'required' => false,
            ])
            ->add('departure', new DateType(), [
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'required' => false,
            ])
            ->add('type',  \MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType::class, [
                'expanded' => true,
                'choices' => [
                    Invite::TYPE_SINGLE => 'form.inviteType.type.single',
                    Invite::TYPE_TWICE => 'form.inviteType.type.twice',
                ],
                'required' => false,
                'placeholder' => null,
            ])
            ->add('guests', CollectionType::class, [
                'type' => new InvitedTouristType(),
                'allow_add' => true,
                'prototype' => true
            ])
            ->add('tripRoutes', CollectionType::class, [
                'type' => new TripRouteType(),
                'allow_add' => true,
                'prototype' => true
            ])
        ;
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Invite::class
        ]);
    }

    public function getBlockPrefix()
    {
        return 'mbh_online_bundle_invite';
    }

}
