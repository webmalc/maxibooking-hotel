<?php

namespace MBH\Bundle\OnlineBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
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
            ->add('include', CheckboxType::class, []);
    }


    public function configureOptions(OptionsResolver $resolver)
    {

    }

    public function getBlockPrefix()
    {
        return 'mbh_online_bundle_settings_invite';
    }

}
