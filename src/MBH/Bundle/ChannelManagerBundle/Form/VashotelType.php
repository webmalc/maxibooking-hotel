<?php

namespace MBH\Bundle\ChannelManagerBundle\Form;

use MBH\Bundle\ChannelManagerBundle\Document\VashotelConfig;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VashotelType extends ChannelManagerConfigType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add(
                'password',
                TextType::class,
                [
                    'label' => 'form.vashotelType.password_label',
                    'required' => true,
                    'attr' => ['placeholder' => 'form.vashotelType.password'],
                    'help' => 'form.vashotelType.password_help'
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
                'data_class' => VashotelConfig::class,
                'channelManagerName' => 'VashHotel.ru'
            ]
        );
    }

    public function getBlockPrefix()
    {
        return 'mbh_bundle_channelmanagerbundle_vashotel_type';
    }

}
