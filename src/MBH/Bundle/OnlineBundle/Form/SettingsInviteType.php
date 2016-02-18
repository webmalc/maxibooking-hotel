<?php

namespace MBH\Bundle\OnlineBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class InviteType

 */
class SettingsInviteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('include', 'checkbox', []);
    }


    public function configureOptions(OptionsResolver $resolver)
    {

    }

    public function getName()
    {
        return 'mbh_online_bundle_settings_invite';
    }

}
