<?php

namespace MBH\Bundle\ChannelManagerBundle\Form;

use MBH\Bundle\ChannelManagerBundle\Document\HomeAwayRoom;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HomeAwayRoomTypeForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var HomeAwayRoom $homeAwayRoom */
        $homeAwayRoom = $options['roomTypes'][$builder->getName()];
        $roomType = $homeAwayRoom->getRoomType();

        $builder
            ->add('rentalAgreement', TextareaType::class, [
                'group' => $roomType->getName()
            ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'roomTypes' => null,
                'data_class' => HomeAwayRoom::class
            ]);
    }

    public function getBlockPrefix()
    {
        return 'mbhchannel_manager_bundle_trip_advisor_room_type';
    }
}
