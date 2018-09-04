<?php

namespace MBH\Bundle\ChannelManagerBundle\Form;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\HotelBundle\Document\Hotel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class RoomsType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach ($options['booking'] as $name => $label) {

            $builder->add($name, DocumentType::class, [
                'label' => $label . ' (ID: ' . $name . ')',
                'class' => 'MBHHotelBundle:RoomType',
                'query_builder' => function(DocumentRepository $er) use($options) {
                    $qb = $er->createQueryBuilder();
                    if ($options['hotel'] instanceof Hotel) {
                        $qb
                            ->field('hotel.id')->equals($options['hotel']->getId())
                            ->field('isEnabled')->equals(true)
                        ;
                    }
                    return $qb;
                },
                'placeholder' => '',
                'required' => false,
                'attr' => ['placeholder' => 'roomtype.placeholder'],
                'group' => isset($options['groupName']) ? $options['groupName'] : ''
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'constraints' => [new Callback([$this, 'check'])],
                'booking' => [],
                'hotel' => null,
            ]
        );
    }

    public function check($data, ExecutionContextInterface $context)
    {
        $ids = [];
        $notMappedRoomsId = [];
        if (empty($data)) {
            $context->addViolation('validator.rooms_type.empty_rooms_list');
        }

        foreach($data as $cmRoomId => $roomType) {
            if ($roomType && in_array($roomType->getId(), $ids)) {
                $context->addViolation('roomtype.validation');
            }
            if ($roomType) {
                $ids[] = $roomType->getId();
            }
            if (is_null($roomType)) {
                $notMappedRoomsId[] = $cmRoomId;
            }
        };

        if (!empty($notMappedRoomsId)) {
            $context->addViolation('roomtype.validation.not_all_rooms_synced', ['%ids%' => join(', ', $notMappedRoomsId)]);
        }
    }

    public function getBlockPrefix()
    {
        return 'mbh_bundle_channelmanagerbundle_rooms_type';
    }

}
