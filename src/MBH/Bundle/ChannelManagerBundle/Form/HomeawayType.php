<?php

namespace MBH\Bundle\ChannelManagerBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HomeawayType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'isEnabled',
                CheckboxType::class,
                [
                    'label' => 'form.channel_manager_config_type.is_included',
                    'value' => true,
                    'required' => false,
                    'help' => 'form.channel_manager_config_type.should_we_use_in_channel_manager'
                ]
            );

    }

    public function configureOptions(OptionsResolver $resolver)
    {

    }

    public function getBlockPrefix()
    {
        return 'mbh_bundle_channelmanagerbundle_homeaway_type';
    }
}
