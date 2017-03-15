<?php

namespace MBH\Bundle\ChannelManagerBundle\Form;

use MBH\Bundle\ChannelManagerBundle\Document\HomeAwayConfig;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HomeAwayRoomsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('rooms', CollectionType::class, [
            'entry_type' => HomeAwayRoomTypeForm::class,
            'group' => false,
            'entry_options' => [
                'roomTypes' => $builder->getData()->getRooms(),
                'warnings' => $options['warnings']
            ]
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => HomeAwayConfig::class,
                'warnings' => null
            ]
        );
    }

    public function getBlockPrefix()
    {
        return 'mbh_bundle_channelmanagerbundle_homeaway_rooms_type';
    }
}