<?php

namespace MBH\Bundle\HotelBundle\Form;

use Doctrine\ODM\MongoDB\DocumentRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RoomType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('fullTitle', 'text', [
                    'label' => 'form.roomType.name',
                    'required' => true,
                    'group' => 'form.roomType.general_info',
                    'attr' => ['placeholder' => '27']
                ])
                ->add('title', 'text', [
                    'label' => 'form.roomType.inner_name',
                    'required' => false,
                    'group' => 'form.roomType.general_info',
                    'attr' => ['placeholder' => 'form.roomType.placeholder_27_with_repair'],
                    'help' => 'form.roomType.maxibooking_inner_name'
                ])
                ->add('isEnabled', 'checkbox', [
                    'label' => 'form.roomType.is_included',
                    'group' => 'form.roomType.settings',
                    'value' => true,
                    'required' => false,
                    'help' => 'form.roomType.is_room_included_in_search'
                ])
            ;

        if (!$options['isNew']) {
            $builder->add('roomType', 'document', [
                    'label' => 'form.roomType.room_type',
                    'class' => 'MBHHotelBundle:RoomType',
                    'group' => 'form.roomType.general_info',
                    'query_builder' => function(DocumentRepository $dr) use ($options) {
                        return $dr->createQueryBuilder('q')
                            ->field('hotel.id')->equals($options['hotelId'])
                            ->sort(['fullTitle' => 'asc', 'title' => 'asc'])
                            ;
                    },
                    'required' => true
            ]);
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MBH\Bundle\HotelBundle\Document\Room',
            'isNew' => true,
            'hotelId' => null
        ));
    }

    public function getName()
    {
        return 'mbh_bundle_hotelbundle_room_type';
    }

}
