<?php

namespace MBH\Bundle\ChannelManagerBundle\Form;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class HomeAwayRoomsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach ($options['booking'] as $id => $name) {
            $groupName = $name . '(' . $id . ')';
            $builder->add($options['room_field_prefix'] . $id, DocumentType::class, [
                'label' => 'form.home_away_rooms_type.sync_room_type.label',
                'group' => $groupName,
                'class' => 'MBHHotelBundle:RoomType',
                'query_builder' => function (DocumentRepository $er) use ($options) {
                    $qb = $er->createQueryBuilder();
                    if ($options['hotel'] instanceof Hotel) {
                        $qb->field('hotel.id')->equals($options['hotel']->getId());
                    }
                    return $qb;
                },
                'placeholder' => '',
                'required' => false,
                'attr' => ['placeholder' => 'roomtype.placeholder']
            ]);

            $builder->add($options['rental_agreement_field_prefix'] . $id, TextareaType::class, [
                'group' => $groupName,
                'label' => 'form.home_away_rooms_type.rental_agreement.label',
                'help' => 'form.home_away_rooms_type.rental_agreement.help',
                'required' => false
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
//                'data_class' => 'MBH\Bundle\ChannelManagerBundle\Document\HomeAwayRoom',
                'constraints' => [new Callback([$this, 'check'])],
                'booking' => [],
                'hotel' => null,
                'room_field_prefix' => null,
                'rental_agreement_field_prefix' => null
            ]
        );
    }

    public function check($data, ExecutionContextInterface $context)
    {
        $ids = [];
        foreach ($data as $roomType) {
            if ($roomType && $roomType instanceof RoomType) {
                if (in_array($roomType->getId(), $ids)) {
                    $context->addViolation('roomtype.validation');
                }
                $ids[] = $roomType->getId();
            }
        };
    }

    public function getBlockPrefix()
    {
        return 'mbh_bundle_channelmanagerbundle_homeaway_rooms_type';
    }
}