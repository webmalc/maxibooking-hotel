<?php

namespace MBH\Bundle\PackageBundle\Form;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType;
use MBH\Bundle\HotelBundle\Document\RoomRepository;
use MBH\Bundle\HotelBundle\Document\RoomTypeRepository;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PriceBundle\Document\SpecialRepository;
use MBH\Bundle\PriceBundle\Document\TariffRepository;
use MBH\Bundle\PriceBundle\Lib\SpecialFilter;
use MBH\Bundle\PriceBundle\Lib\TariffFilter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class PackageMainType
 */
class PackageMainType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Package $package */
        $package = $options['package'];

        $builder
            ->add('begin', DateType::class, [
                'label' => 'form.packageMainType.begin',
                'group' => 'form.packageMainType.begin_end_group',
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'required' => true,
                'error_bubbling' => true,
                'attr' => array(
                    'class' => 'datepicker begin-datepicker input-small',
                    'data-date-format' => 'dd.mm.yyyy'
                )
            ])
            ->add('end', DateType::class, [
                'label' => 'form.packageMainType.end',
                'group' => 'form.packageMainType.begin_end_group',
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'required' => true,
                'error_bubbling' => true,
                'attr' => array(
                    'class' => 'datepicker end-datepicker input-small',
                    'data-date-format' => 'dd.mm.yyyy'
                )
            ])
            ->add('isForceBooking', CheckboxType::class, [
                'label' => 'form.packageMainType.is_force_booking',
                'required' => false,
                'group' => 'form.packageMainType.begin_end_group',
                'help' => 'form.packageMainType.is_force_booking.help'
            ])
            ->add('roomType', DocumentType::class, [
                'label' => 'form.packageMainType.room_type',
                'class' => 'MBHHotelBundle:RoomType',
                'group' => 'form.packageMainType.room_group',
                'query_builder' => function (RoomTypeRepository $dr) use ($options) {
                    return $dr->createQueryBuilder()
                        ->field('hotel.id')->equals($options['hotel']->getId())
                        ->field('deletedAt')->equals(null)
                        ->sort(['fullTitle' => 'asc', 'title' => 'asc']);
                },
                'required' => true
            ]);
            if ($options['virtualRooms']) {
                $builder
                    ->add('virtualRoom', DocumentType::class, [
                        'label' => 'form.packageMainType.virtual_room',
                        'class' => 'MBHHotelBundle:Room',
                        'group' => 'form.packageMainType.room_group',
                        'query_builder' => function (RoomRepository $dr) use ($package) {
                            return $dr->getVirtualRoomsForPackageQB($package);
                        },
                        'required' => false
                    ]);
            }
            $builder
            ->add('adults',  InvertChoiceType::class, [
                'label' => 'form.packageMainType.adults',
                'group' => 'form.packageMainType.room_group',
                'required' => true,
                'multiple' => false,
                'choices' => range(0, 12),
                'attr' => array('class' => 'input-xxs plain-html'),
            ])
            ->add('children',  InvertChoiceType::class, [
                'label' => 'form.packageMainType.children',
                'group' => 'form.packageMainType.room_group',
                'required' => true,
                'multiple' => false,
                'choices' => range(0, 10),
                'attr' => array('class' => 'input-xxs plain-html'),
            ])
            ->add('isSmoking', CheckboxType::class, [
                'label' => 'form.packageMainType.is_smoking',
                'required' => false,
                'group' => 'form.packageMainType.room_group',
            ]);

        if ($options['promotion']) {
            if($package && $package->getPromotion() && !in_array($package->getPromotion(), $options['promotions'])) {
                $options['promotions'][] = $package->getPromotion();
            }
            if (count($options['promotions'])) {
                $builder
                    ->add('promotion', DocumentType::class, [
                        'label' => 'form.packageMainType.promotion',
                        'class' => 'MBH\Bundle\PriceBundle\Document\Promotion',
                        'required' => false,
                        'group' => 'form.packageMainType.promotion_group',
                        'choices' => $options['promotions']
                    ]);
            }
        }
        $builder->add('tariff', DocumentType::class, [
            'label' => 'form.packageMainType.tariff',
            'class' => 'MBH\Bundle\PriceBundle\Document\Tariff',
            'required' => false,
            'data' => null,
            'mapped' => false,
            'query_builder' => function (TariffRepository $dr) use ($package, $options) {
                $filter = new TariffFilter();
                $filter->setHotel($options['hotel'])
                    ->setBegin(new \DateTime())
                ;
                return $dr->getFilteredQueryBuilder($filter)
                    ->field('deletedAt')
                    ->equals(null);
            },
            'group' => 'mbhpackagebundle.form.packagemaintype.cena',
        ]);
        if (!$package->getTotalOverwrite() && $options['price']) {
            $builder->add('price', TextType::class, [
                'label' => 'form.packageMainType.price',
                'required' => true,
                'group' => 'form.packageMainType.price_group',
                'error_bubbling' => true,
                'property_path' => 'packagePrice',
                'attr' => [
                    'class' => 'price-spinner'
                ],
            ]);
        }

        if($options['discount']) {
            $builder
                ->add('discount', TextType::class, [
                    'label' => 'form.packageMainType.discount',
                    'required' => false,
                    'group' => 'form.packageMainType.discount_group'
                ])
                ->add('isPercentDiscount', CheckboxType::class, [
                    'label' => 'form.packageMainType.isPercentDiscount',
                    'required' => false,
                    'group' => 'form.packageMainType.discount_group'
                ]);
        }
        if($options['special']) {
            $builder
                ->add('special', DocumentType::class, [
                    'group' => 'form.packageMainType.special_group',
                    'label' => 'form.packageMainType.special',
                    'class' => 'MBH\Bundle\PriceBundle\Document\Special',
                    'required' => false,
                    'query_builder' => function (SpecialRepository $dr) use ($package) {
                        $filter = new SpecialFilter();
                        $filter->setHotel($package->getHotel())
                            ->setTariff($package->getTariff())
                            ->setRemain(1)
                            ->setExcludeSpecial($package->getSpecial())
                        ;
                        return $dr->getFilteredQueryBuilder($filter);
                    },
                ]);
        }
        $builder
            ->add('numberWithPrefix', TextType::class, [
                'label' => 'form.packageMainType.package_number',
                'group' => 'form.packageMainType.information_group',
                'required' => true,
            ])
            ->add('note', TextareaType::class, [
                'label' => 'form.packageMainType.comment',
                'group' => 'form.packageMainType.information_group',
                'required' => false,
            ]);
        if ($package->isDeleted()) {
            $builder
                ->add('deleteReason', DocumentType::class, [
                    'empty_data'  => null,
                    'required'    => false,
                    'label' => 'modal.form.delete.reasons.reason',
                    'group' => 'modal.form.delete.delete_reason_package',
                    'class' => 'MBH\Bundle\PackageBundle\Document\DeleteReason',
                    'query_builder' => function (DocumentRepository $dr) use ($options) {
                        return $dr->createQueryBuilder()
                            ->field('deletedAt')->exists(false)
                            ->sort(['isDefault' => 'desc']);
                    },
                ]);
        }
        if ($options['corrupted']) {
            $builder
                ->add('corrupted', CheckboxType::class, [
                    'label' => 'form.packageMainType.is_corrupted',
                    'required' => false,
                    'group' => 'form.packageMainType.information_group',
                    'help' => 'form.packageMainType.is_corrupted.help'
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'MBH\Bundle\PackageBundle\Document\Package',
            'discount' => false,
            'special' => false,
            'hotel' => null,
            'corrupted' => false,
            'promotion' => false,
            'promotions' => [],
            'package' => null,
            'price' => false,
            'virtualRooms'=> false
         ]);
    }


    public function getBlockPrefix()
    {
        return 'mbh_bundle_packagebundle_package_main_type';
    }
}
