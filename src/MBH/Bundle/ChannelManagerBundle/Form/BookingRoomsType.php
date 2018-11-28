<?php

namespace MBH\Bundle\ChannelManagerBundle\Form;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\ChannelManagerBundle\Document\BookingRoom;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class BookingRoomsType extends AbstractType
{
    const SINGLE_PRICES_FIELD_PREFIX = 'single_prices';
    const ROOM_TYPE_FIELD_PREFIX = 'room';

    private $helper;

    public function __construct(Helper $helper) {
        $this->helper = $helper;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Hotel $hotel */
        $hotel = $options['hotel'];
        $bookingConfig = $hotel->getBookingConfig();

        foreach ($options['booking'] as $roomId => $label) {
            $bookingRoom = $bookingConfig->getRoomById($roomId);
            $groupName = $label . ' (ID: ' . $roomId . ')';
            $builder
                ->add(self::SINGLE_PRICES_FIELD_PREFIX . $roomId, CheckboxType::class, [
                    'group' => $groupName,
                    'label' => 'booking_rooms_type.single_prices.label',
                    'required' => false,
                    'data' => $bookingRoom instanceOf BookingRoom ? $bookingRoom->isUploadSinglePrices() : true,
                    'help' => 'booking_rooms_type.single_prices.help'
                ])
                ->add(self::ROOM_TYPE_FIELD_PREFIX . $roomId, DocumentType::class, [
                    'label' => 'booking_rooms_type.room_type.label',
                    'class' => 'MBHHotelBundle:RoomType',
                    'query_builder' => function (DocumentRepository $repository) use ($hotel) {
                        $qb = $repository->createQueryBuilder();
                        $qb
                            ->field('isEnabled')->equals(true)
                            ->field('hotel.id')->equals($hotel->getId())
                        ;
                        return $qb;
                    },
                    'required' => false,
                    'attr' => ['placeholder' => 'roomtype.placeholder'],
                    'group' => $groupName,
                    'data' => $bookingRoom ? $bookingRoom->getRoomType() : null
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

        /** @var RoomType $roomType */
        foreach ($data as $key => $roomType) {
            if ($this->helper->startsWith($key, self::ROOM_TYPE_FIELD_PREFIX)) {
                if ($roomType && in_array($roomType->getId(), $ids)) {
                    $context->addViolation('roomtype.validation');
                }
                if ($roomType) {
                    $ids[] = $roomType->getId();
                }
                if (is_null($roomType)) {
                    $notMappedRoomsId[] = substr($key, strlen(self::ROOM_TYPE_FIELD_PREFIX));
                }
            }
        };

        if (!empty($notMappedRoomsId)) {
            $context->addViolation('roomtype.validation.not_all_rooms_synced', ['%ids%' => join(', ', $notMappedRoomsId)]);
        }
    }

    public function getBlockPrefix()
    {
        return 'mbh_bundle_channelmanagerbundle_booking_rooms_type';
    }
}
