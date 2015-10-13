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
                'required' => true,
            ])
            ->add('title', 'text', [
                'label' => 'form.roomTypeCategory.inner_name',
                'required' => false,
            ])
            ->add('is_enabled', 'checkbox', [
                'label' => 'form.roomTypeCategory.is_enabled',
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