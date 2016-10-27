<?php

namespace MBH\Bundle\ChannelManagerBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ExpediaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //TODO: Изменить подписи
        $builder
            ->add('isEnabled', CheckboxType::class, [
                    'label' => 'form.expedia.isEnabled',
                    'value' => true,
                    'required' => false,
                    'help' => 'form.expedia.should_we_use_in_channel_manager'
                ]
            )
            ->add('username', TextType::class, [
                'label' => 'form.expedia.username.label',
                'required' => true,
                'help' => 'form.expedia.username.help',
                'attr' => [
                    'placeholder' => 'form.expedia.username.placeholder'
                ]
            ])
            ->add('password', TextType::class, [
                    'label' => 'form.expedia.password.label',
                    'required' => true,
                    'attr' => [
                        'placeholder' => 'form.expedia.password.placeholder'
                    ],
                    'help' => 'form.expedia.password.help'
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MBH\Bundle\ChannelManagerBundle\Document\ExpediaConfig',
        ));
    }

    public function getName()
    {
        return 'mbhchannel_manager_bundle_expedia_type';
    }
}
