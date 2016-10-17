<?php

namespace MBH\Bundle\ChannelManagerBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AirbnbType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('isEnabled', CheckboxType::class, [
                    'label' => 'form.airbnb.isEnabled',
                    'value' => true,
                    'required' => false,
                    'help' => 'form.airbnb.should_we_use_in_channel_manager'
                ]
            )
            ->add('email', TextType::class, [
                'mapped' => false,
                'label' => 'form.airbnb.email.label',
                'required' => true,
                'help' => 'form.airbnb.email.help',
                'attr' => [
                    'placeholder' => 'form.airbnb.email.placeholder'
                ]
            ])
            ->add('password', TextType::class, [
                    'mapped' => false,
                    'label' => 'form.airbnb.password.label',
                    'required' => true,
                    'attr' => [
                        'placeholder' => 'form.airbnb.password.placeholder'
                    ],
                    'help' => 'form.airbnb.password.help'
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MBH\Bundle\ChannelManagerBundle\Document\AirbnbConfig',
        ));
    }

    public function getName()
    {
        return 'mbhchannel_manager_bundle_airbnb_type';
    }
}
