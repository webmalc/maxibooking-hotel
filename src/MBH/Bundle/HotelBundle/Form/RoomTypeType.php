<?php

namespace MBH\Bundle\HotelBundle\Form;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\BaseBundle\Form\FacilitiesType;
use MBH\Bundle\BaseBundle\Form\FormWithMultiLangFields;
use MBH\Bundle\BaseBundle\Form\MultiLanguagesType;
use MBH\Bundle\ClientBundle\Service\ClientConfigManager;
use MBH\Bundle\HotelBundle\Document\RoomViewType;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class RoomTypeType
 */
class RoomTypeType extends FormWithMultiLangFields
{
    /** @var  Translator $translator */
    private $translator;

    public function __construct(Translator $translator, ClientConfigManager $clientConfigManager)
    {
        $this->translator = $translator;
        parent::__construct($clientConfigManager);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['useRoomTypeCategory']) {
            $hotel = $options['hotel'];
            $builder
                ->add('category', DocumentType::class, [
                    'label' => 'form.roomTypeType.category',
                    'group' => 'form.roomTypeType.general_info',
                    'required' => true,
                    'placeholder' => '',
                    'class' => 'MBHHotelBundle:RoomTypeCategory',
                    'query_builder' => function (DocumentRepository $repository) use ($hotel) {
                        $qb = $repository->createQueryBuilder();
                        if ($hotel) {
                            $qb->field('hotel.id')->equals($hotel->getID());
                        }
                        return $qb;
                    }
                ]);
        }

        $builder = $this->addMultiLangField($builder, TextType::class, 'fullTitle', [
            'group' => 'form.roomTypeType.general_info',
            'attr' => ['placeholder' => 'form.roomTypeType.comfort_plus'],
            'label' => 'form.roomTypeType.name',
        ]);

        $builder
            ->add('title', TextType::class, [
                'label' => 'form.roomTypeType.inner_name',
                'required' => false,
                'group' => 'form.roomTypeType.general_info',
                'attr' => ['placeholder' => 'form.roomTypeType.comport_plus_rooms_in_new_housing'],
                'help' => 'form.roomTypeType.inner_name.help'
            ])
            ->add('internationalTitle', TextType::class, [
                'label' => 'form.roomTypeType.international_title',
                'required' => false,
                'group' => 'form.roomTypeType.general_info',
            ])
        ;
        $builder = $this->addMultiLangField($builder, TextareaType::class, 'description', [
            'attr' => ['class' => 'big roomTypeTypeEditor tinymce'],
            'label' => 'form.roomTypeType.description',
            'group' => 'form.roomTypeType.general_info',
            'required' => false,
        ]);

        $builder
            ->add('color', TextType::class, [
                'label' => 'form.roomTypeType.color',
                'required' => true,
                'group' => 'form.roomTypeType.general_info',
                'attr' => ['placeholder' => '008000'],
                'addon' => 'fa fa-eyedropper',
                'help' => 'form.roomTypeType.chess_room_type_color'
            ])
            ->add('roomSpace', TextType::class, [
                'label' => 'form.roomTypeType.room_space',
                'required' => false,
                'group' => 'form.roomTypeType.general_info',
            ])
            ->add('facilities', FacilitiesType::class, [
                //'label' => 'form.roomTypeType.is_included',
                'group' => 'form.roomTypeType.general_info',
                'required' => false
            ])
            ->add('roomViewsTypes', DocumentType::class, [
                'choice_label' => function ($value) {
                    return $this->translator->trans($value);
                },
                'label' => 'form.roomType.room_view_types.label',
                'group' => 'form.roomTypeType.general_info',
                'required' => false,
                'query_builder' => function (DocumentRepository $documentRepository) {
                    return $documentRepository->createQueryBuilder();
                },
                'class' => RoomViewType::class,
                'multiple' => 'true',
            ])
            ->add('isSmoking', CheckboxType::class, [
                'label' => 'form.hotelType.isSmoking.label',
                'group' => 'form.roomTypeType.general_info',
                'value' => true,
                'required' => false,
                'help' => 'form.hotelType.isSmoking.help'
            ])
            ->add('isHostel', CheckboxType::class, [
                'label' => 'form.hotelType.hostel',
                'group' => 'form.roomTypeType.places',
                'value' => true,
                'required' => false,
                'help' => 'form.hotelType.hostel_hotel_or_not'
            ])
            ->add('places', TextType::class, [
                'label' => 'form.roomTypeType.main_places',
                'group' => 'form.roomTypeType.places',
                'required' => true,
                'attr' => ['class' => 'spinner room-type-places'],
                'help' => 'form.roomTypeType.room_main_places_amount'
            ])
            ->add('additionalPlaces', TextType::class, [
                'label' => 'form.roomTypeType.additional_places',
                'group' => 'form.roomTypeType.places',
                'required' => true,
                'attr' => ['class' => 'spinner room-type-places'],
                'help' => 'form.roomTypeType.room_additional_places_amount'
            ])
            ->add('maxInfants', TextType::class, [
                'label' => 'form.roomTypeType.max_infants',
                'group' => 'form.roomTypeType.places',
                'required' => true,
                'attr' => ['placeholder' => 'hotel', 'class' => 'spinner room-type-places'],
                'help' => 'form.roomTypeType.room_additional_places_amount'
            ])
        ;

        if (!$options['useRoomTypeCategory']) {
            $builder
                ->add('isChildPrices', CheckboxType::class, [
                    'label' => 'form.roomTypeType.isChildPrices',
                    'group' => 'form.roomTypeType.prices',
                    'value' => true,
                    'required' => false,
                    'help' => 'form.roomTypeType.isChildPricesDesc'
                ])
                ->add('isIndividualAdditionalPrices', CheckboxType::class, [
                    'label' => 'form.roomTypeType.isIndividualAdditionalPrices',
                    'group' => 'form.roomTypeType.prices',
                    'value' => true,
                    'required' => false,
                    'help' => 'form.roomTypeType.isIndividualAdditionalPricesDesc'
                ])
                ->add('isSinglePlacement', CheckboxType::class, [
                    'label' => 'form.roomTypeType.isSinglePlacement',
                    'group' => 'form.roomTypeType.prices',
                    'value' => true,
                    'required' => false,
                    'help' => 'form.roomTypeType.isSinglePlacementDesc'
                ]);
        }

        $builder
            ->add('isEnabled', CheckboxType::class, [
                'label' => 'form.roomTypeType.is_included',
                'group' => 'form.roomTypeType.settings',
                'value' => true,
                'required' => false,
                'help' => 'form.roomTypeType.is_room_included_in_search'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'MBH\Bundle\HotelBundle\Document\RoomType',
            'imageUrl' => null,
            'deleteImageUrl' => null,
            'useRoomTypeCategory' => false,
            'hotel' => null,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'mbh_bundle_hotelbundle_room_type_type';
    }
}
