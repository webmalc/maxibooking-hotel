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
                    'label' => 'Название',
                    'required' => true,
                    'attr' => ['placeholder' => '27']
                ])
                ->add('title', 'text', [
                    'label' => 'Внутреннее название',
                    'required' => false,
                    'attr' => ['placeholder' => '27 (c ремонтом)'],
                    'help' => 'Название для использования внутри MaxiBooking'
                ])
        ;

        if (!$options['isNew']) {
            $builder->add('roomType', 'document', [
                    'label' => 'Тип номера',
                    'class' => 'MBHHotelBundle:RoomType',
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
