<?php

namespace MBH\Bundle\HotelBundle\Form;

use Doctrine\ODM\MongoDB\DocumentRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class RoomTypeCategoryType
 */
class RoomTypeCategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fullTitle', 'text', [
                'label' => 'form.roomTypeCategory.name',
                'group' => 'form.roomTypeType.general_info',
                'required' => true,
            ])
            ->add('title', 'text', [
                'label' => 'form.roomTypeCategory.inner_name',
                'group' => 'form.roomTypeType.general_info',
                'required' => false,
            ])
            ->add('isChildPrices', 'checkbox', [
                'label' => 'form.roomTypeType.isChildPrices',
                'group' => 'form.roomTypeType.prices',
                'value' => true,
                'required' => false,
                'help' => 'form.roomTypeType.isChildPricesDesc'
            ])
            ->add('isIndividualAdditionalPrices', 'checkbox', [
                'label' => 'form.roomTypeType.isIndividualAdditionalPrices',
                'group' => 'form.roomTypeType.prices',
                'value' => true,
                'required' => false,
                'help' => 'form.roomTypeType.isIndividualAdditionalPricesDesc'
            ])
            ->add('is_enabled', 'checkbox', [
                'label' => 'form.roomTypeCategory.is_enabled',
                'group' => 'form.roomTypeType.settings',
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'MBH\Bundle\HotelBundle\Document\RoomTypeCategory',
        ]);
    }

    public function getName()
    {
        return 'mbh_hotel_room_type_category';
    }

}