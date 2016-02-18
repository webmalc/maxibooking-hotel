<?php

namespace MBH\Bundle\OnlineBundle\Form;

use MBH\Bundle\OnlineBundle\Document\Invite;
use Symfony\Component\Form\AbstractType;
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
            ->add('type', 'choice', [
                'expanded' => true,
                'choices' => [
                    Invite::TYPE_SINGLE => 'form.inviteType.type.single',
                    Invite::TYPE_TWICE => 'form.inviteType.type.twice',
                ],
                'required' => false,
                'empty_value' => null,
            ])
            ->add('guests', 'collection', [
                'type' => new InvitedTouristType(),
                'allow_add' => true,
                'prototype' => true
            ])
            ->add('tripRoutes', 'collection', [
                'type' => new TripRouteType(),
                'allow_add' => true,
                'prototype' => true
            ])
            //->add('agree', 'checkbox')
        ;
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Invite::class
        ]);
    }

    public function getName()
    {
        return 'mbh_online_bundle_invite';
    }

}
