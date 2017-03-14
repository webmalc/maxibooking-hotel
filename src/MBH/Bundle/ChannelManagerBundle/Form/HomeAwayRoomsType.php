<?php

namespace MBH\Bundle\ChannelManagerBundle\Form;

use MBH\Bundle\ChannelManagerBundle\Document\HomeAwayConfig;
use MBH\Bundle\ChannelManagerBundle\Document\HomeAwayRoom;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HomeAwayRoomsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('rooms', CollectionType::class, [
            'entry_type' => HomeAwayRoom::class,
            'group' => false,
            'entry_options' => [
                'rooms' => $builder->getData()->getRooms()
            ]
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => HomeAwayConfig::class,
                'roomTypes' => null
            ]
        );
    }

    public function getBlockPrefix()
    {
        return 'mbh_bundle_channelmanagerbundle_homeaway_rooms_type';
    }
}