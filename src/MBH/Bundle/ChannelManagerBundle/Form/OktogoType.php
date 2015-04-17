<?php

namespace MBH\Bundle\ChannelManagerBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class OktogoType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'enabled',
                'checkbox',
                [
                    'label' => 'form.oktogoType.is_included',
                    'value' => true,
                    'required' => false,
                    'help' => 'form.oktogoType.should_we_use_in_channel_manager'
                ]
            )
            ->add(
                'login',
                'text',
                [
                    'label' => 'form.oktogoType.login',
                    'required' => true,
                    'attr' => ['placeholder' => 'login'],
                    'help' => 'form.oktogoType.oktogo_api_login_access'
                ]
            )
            ->add(
                'password',
                'text',
                [
                    'label' => 'form.oktogoType.password',
                    'required' => true,
                    'attr' => ['placeholder' => 'password'],
                    'help' => 'form.oktogoType.oktogo_api_password_access'
                ]
            )
            ->add(
                'username',
                'text',
                [
                    'label' => 'form.oktogoType.username',
                    'required' => true,
                    'attr' => ['placeholder' => 'username'],
                    'help' => 'form.oktogoType.oktogo_ru_username'
                ]
            )
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'MBH\Bundle\ChannelManagerBundle\Document\OktogoConfig',
            )
        );
    }

    public function getName()
    {
        return 'mbh_bundle_channelmanagerbundle_oktogo_type';
    }

}
