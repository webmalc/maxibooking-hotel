<?php

namespace MBH\Bundle\PackageBundle\Form;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PriceBundle\Lib\SpecialFilter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use MBH\Bundle\PriceBundle\Lib\TariffFilter;

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
                'label' => 'mbhpackagebundle.form.packagemaintype.zayezd',
                'group' => 'mbhpackagebundle.form.packagemaintype.zaezd_viezd',
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
                'label' => 'mbhpackagebundle.form.packagemaintype.viezd',
                'group' => 'mbhpackagebundle.form.packagemaintype.zaezd_viezd',
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
                'label' => 'mbhpackagebundle.form.packagemaintype.prinuditelnoe_bronirovanie',
                'required' => false,
                'group' => 'mbhpackagebundle.form.packagemaintype.zaezd_viezd',
                'help' => 'mbhpackagebundle.form.packagemaintype.ignorirovat_uslovia_i_ogranichenia_pri_poiske_dostupnogo_nomera'
            ])
            ->add('roomType', DocumentType::class, [
                'label' => 'mbhpackagebundle.form.packagemaintype.tip.nomera',
                'class' => 'MBHHotelBundle:RoomType',
                'group' => 'mbhpackagebundle.form.packagemaintype.number',
                'query_builder' => function (DocumentRepository $dr) use ($options) {
                    return $dr->createQueryBuilder('q')
                        ->field('hotel.id')->equals($options['hotel']->getId())
                        ->field('deletedAt')->equals(null)
                        ->sort(['fullTitle' => 'asc', 'title' => 'asc']);
                },
                'required' => true
            ]);
        if ($options['virtualRooms']) {
            $builder
            ->add('virtualRoom', DocumentType::class, [
                'label' => 'mbhpackagebundle.form.packagemaintype.virtualnii_nomer',
                'class' => 'MBHHotelBundle:Room',
                'group' => 'mbhpackagebundle.form.packagemaintype.number',
                'query_builder' => function (DocumentRepository $dr) use ($package) {
                    return $dr->getVirtualRoomsForPackageQB($package);
                },
                'required' => false
            ]);
        }
            $builder
            ->add('adults', \MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType::class, [
                'label' => 'mbhpackagebundle.form.packagemaintype.vzroslykh',
                'group' => 'mbhpackagebundle.form.packagemaintype.number',
                'required' => true,
                'group' => 'mbhpackagebundle.form.packagemaintype.number',
                'multiple' => false,
                'choices' => range(0, 12),
                'attr' => array('class' => 'input-xxs plain-html'),
            ])
            ->add('children', \MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType::class, [
                'label' => 'mbhpackagebundle.form.packagemaintype.detey',
                'group' => 'mbhpackagebundle.form.packagemaintype.number',
                'required' => true,
                'group' => 'mbhpackagebundle.form.packagemaintype.number',
                'multiple' => false,
                'choices' => range(0, 10),
                'attr' => array('class' => 'input-xxs plain-html'),
            ])
            ->add('isSmoking', CheckboxType::class, [
                'label' => 'mbhpackagebundle.form.packagemaintype.kurashii',
                'required' => false,
                'group' => 'mbhpackagebundle.form.packagemaintype.number',
            ]);

        if ($options['promotion']) {
            if ($package && $package->getPromotion() && !in_array($package->getPromotion(), $options['promotions'])) {
                $options['promotions'][] = $package->getPromotion();
            }
            if (count($options['promotions'])) {
                $builder
                    ->add('promotion', DocumentType::class, [
                        'label' => 'form.packageMainType.promotion',
                        'class' => 'MBH\Bundle\PriceBundle\Document\Promotion',
                        'required' => false,
                        'group' => 'mbhpackagebundle.form.packagemaintype.akcia',
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
            'query_builder' => function (DocumentRepository $dr) use ($package, $options) {
                $filter = new TariffFilter();
                $filter->setHotel($options['hotel'])
                    ->setBegin(new \DateTime())
                ;
                return $dr->getFilteredQueryBuilder($filter)
                    ->field('deletedAt')
                    ->equals(null);
            },
            'group' => 'Цена',
        ]);
        if (!$package->getTotalOverwrite() && $options['price']) {
            $builder->add('price', TextType::class, [
                'label' => 'form.packageMainType.price',
                'required' => true,
                'group' => 'mbhpackagebundle.form.packagemaintype.cena',
                'error_bubbling' => true,
                'property_path' => 'packagePrice',
                'attr' => [
                    'class' => 'price-spinner'
                ],
            ]);
        }

        if ($options['discount']) {
            $builder
                ->add('discount', TextType::class, [
                    'label' => 'form.packageMainType.discount',
                    'required' => false,
                    'group' => 'mbhpackagebundle.form.packagemaintype.skidka'
                ])
                ->add('isPercentDiscount', CheckboxType::class, [
                    'label' => 'form.packageMainType.isPercentDiscount',
                    'required' => false,
                    'group' => 'mbhpackagebundle.form.packagemaintype.skidka'
                ]);
        }
        if ($options['special']) {
            $builder
                ->add('special', DocumentType::class, [
                    'group' => 'mbhpackagebundle.form.packagemaintype.specpredlozhenie',
                    'label' => 'form.packageMainType.special',
                    'class' => 'MBH\Bundle\PriceBundle\Document\Special',
                    'required' => false,
                    'group' => 'mbhpackagebundle.form.packagemaintype.specpredlozhenie',
                    'query_builder' => function (DocumentRepository $dr) use ($package) {
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
                'label' => 'mbhpackagebundle.form.packagemaintype.nomer.broni',
                'group' => 'mbhpackagebundle.form.packagemaintype.information',
                'required' => true,
            ])
            ->add('note', TextareaType::class, [
                'label' => 'form.packageMainType.comment',
                'group' => 'mbhpackagebundle.form.packagemaintype.information',
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
                    'label' => 'mbhpackagebundle.form.packagemaintype.povrezhdena',
                    'required' => false,
                    'group' => 'mbhpackagebundle.form.packagemaintype.information',
                    'help' => 'mbhpackagebundle.form.packagemaintype.bron_s_povrezhdennoi_informaciei'
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
