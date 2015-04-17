<?php

namespace MBH\Bundle\ChannelManagerBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Range;

class RoomType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $config = $options['entity'];
        $roomTypes = $config->getHotel()->getRoomTypes();
        $roomEntities = $config->getRooms();
        $ids = [];

        foreach ($roomEntities as $room) {
            $ids[$room->getRoomType()->getId()] = $room->getRoomId();
        }

        foreach ($roomTypes as $roomType) {

            (isset($ids[$roomType->getId()])) ? $data = $ids[$roomType->getId()] : $data = null;

            $builder
                ->add($roomType->getId(), 'text', [
                        'label' => $roomType->getName(),
                        'required' => false,
                        'attr' => [
                            'placeholder' => 'form.roomType.room_type_id <'. $roomType->getName() .'> form.roomType.in_service_settings',
                        ],
                        'data' => $data
                    ])
            ;
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'entity' => false
        ));
    }

    public function getName()
    {
        return 'mbh_bundle_channelmanagerbundle_room_type';
    }

}
