<?php

namespace MBH\Bundle\ChannelManagerBundle\Form\TripAdvisor;

use MBH\Bundle\ChannelManagerBundle\Document\TripAdvisorConfig;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TripAdvisorRoomTypesForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('rooms', CollectionType::class, [
                'entry_type' => TripAdvisorRoomTypeForm::class,
                'entry_options' => [
                    'hotel' => $options['hotel'],
                    'roomTypes' => $builder->getData()->getRooms(),
                    'requiredFieldsErrors' => $options['requiredFieldsErrors']
                ],
                'group' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => TripAdvisorConfig::class,
                'hotel' => null,
                'requiredFieldsErrors' => null
            ]);
    }

    public function getBlockPrefix()
    {
        return 'mbhchannel_manager_bundle_trip_advisor_room_types_form';
    }
}
