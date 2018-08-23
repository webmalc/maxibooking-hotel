<?php

namespace MBH\Bundle\HotelBundle\Form\RoomTypeFlow;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use MBH\Bundle\HotelBundle\Document\RoomTypeRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RoomTypeFlowType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        switch ($options['flow_step']) {
            case 1:
                $builder
                    ->add(
                        'roomTypes',
                        DocumentType::class,
                        [
                            'label' => 'room_type_flow_type.room_type.label',
                            'required' => true,
                            'class' => 'MBHHotelBundle:RoomType',
                            'query_builder' => function (RoomTypeRepository $dr) use ($options) {
                                return $dr->fetchQueryBuilder($options['hotel']);
                            },
                        ]
                    );
//            case 2:
//                $builder
//                    ->add('descr')
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults(
                [
                    'hotel' => null,
                ]
            );
    }

    public function getBlockPrefix()
    {
        return 'mbhhotel_bundle_room_type_flow';
    }
}
