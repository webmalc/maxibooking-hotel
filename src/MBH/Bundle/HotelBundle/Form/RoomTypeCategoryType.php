<?php

namespace MBH\Bundle\HotelBundle\Form;

use Doctrine\ODM\MongoDB\DocumentRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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
            ->add('fullTitle', TextType::class, [
                'label' => 'form.roomTypeCategory.name',
                'group' => 'form.roomTypeType.general_info',
                'required' => true,
            ])
            ->add('title', TextType::class, [
                'label' => 'form.roomTypeCategory.inner_name',
                'group' => 'form.roomTypeType.general_info',
                'required' => false,
            ])
            ->add('description', TextareaType::class, [
                'label' => 'form.roomTypeCategory.desc',
                'help' => 'form.roomTypeCategory.desc_help',
                'required' => false,
                'group' => 'form.roomTypeType.general_info',
                'attr' => ['class' => 'big roomTypeTypeEditor tinymce']
            ])
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
            ->add('is_enabled', CheckboxType::class, [
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

    public function getBlockPrefix()
    {
        return 'mbh_hotel_room_type_category';
    }

}