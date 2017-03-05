<?php

namespace MBH\Bundle\ChannelManagerBundle\Form;

use MBH\Bundle\BaseBundle\Service\Currency;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class OktogoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'enabled',
                CheckboxType::class,
                [
                    'label' => 'form.oktogoType.is_included',
                    'value' => true,
                    'required' => false,
                    'help' => 'form.oktogoType.should_we_use_in_channel_manager'
                ]
            )
            ->add(
                'hotelId',
                TextType::class,
                [
                    'label' => 'form.oktogoType.username',
                    'required' => true,
                    'attr' => ['placeholder' => 'username'],
                    'help' => 'form.oktogoType.oktogo_ru_username'
                ]
            );
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
