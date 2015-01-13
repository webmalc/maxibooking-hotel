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
                    'label' => 'Включен?',
                    'value' => true,
                    'required' => false,
                    'help' => 'Использовать ли в Channel manager?'
                ]
            )
            ->add(
                'login',
                'text',
                [
                    'label' => 'Логин',
                    'required' => true,
                    'attr' => ['placeholder' => 'login'],
                    'help' => 'Логин для доступа к API Oktogo.ru'
                ]
            )
            ->add(
                'password',
                'text',
                [
                    'label' => 'Пароль',
                    'required' => true,
                    'attr' => ['placeholder' => 'password'],
                    'help' => 'Пароль для доступа к API Oktogo.ru'
                ]
            )
            ->add(
                'username',
                'text',
                [
                    'label' => 'Имя пользователя',
                    'required' => true,
                    'attr' => ['placeholder' => 'username'],
                    'help' => 'Имя пользователя в Oktogo.ru'
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
