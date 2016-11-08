<?php

namespace MBH\Bundle\ChannelManagerBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OstrovokType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'isEnabled',
                CheckboxType::class,
                [
                    'label' => 'form.ostrovokType.in_included',
                    'value' => true,
                    'required' => false,
                    'help' => 'form.ostrovokType.should_we_use_in_channel_manager'
                ]
            )
            ->add(
                'hotelId',
                TextType::class,
                [
                    'label' => 'form.ostrovokType.hotel_id',
                    'required' => true,
                    'attr' => ['placeholder' => '1567'],
                    'help' => 'form.ostrovokType.ostrovok_ru_settings_hotel_id'
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'MBH\Bundle\ChannelManagerBundle\Document\OstrovokConfig',
            )
        );
    }

    public function getBlockPrefix()
    {
        return 'mbh_bundle_channelmanagerbundle_ostrovok_type';
    }

}
