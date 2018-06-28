<?php

namespace MBH\Bundle\ChannelManagerBundle\Form;

use MBH\Bundle\ChannelManagerBundle\Document\HundredOneHotelsConfig;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HundredOneHotelType extends ChannelManagerConfigType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('apiKey', TextType::class, [
                    'label' => 'form.hundredOneHotels.api_key.label',
                    'required' => true,
                    'attr' => [
                        'placeholder' => 'form.hundredOneHotels.api_key.placeholder'
                    ],
                    'help' => 'form.hundredIneHotels.api_key.help'
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => HundredOneHotelsConfig::class,
            'channelManagerName' => '101hotels.ru'
        ]);
    }

    public function getBlockPrefix()
    {
        return 'mbh_bundle_channelmanagerbundle_hundred_one_hotels_type';
    }
}