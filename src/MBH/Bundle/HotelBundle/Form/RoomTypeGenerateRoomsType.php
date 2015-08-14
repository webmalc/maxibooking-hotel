<?php

namespace MBH\Bundle\HotelBundle\Form;

use Doctrine\ODM\MongoDB\DocumentRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\ExecutionContextInterface;

class RoomTypeGenerateRoomsType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $entity = $options['entity'];

        $builder
            ->add('from', 'text', [
                'label' => 'form.roomTypeGenerateRoomsType.first_room_number',
                'required' => true,
                'attr' => ['placeholder' => '1', 'class' => 'spinner'],
                'constraints' => [
                    new NotBlank(),
                    new Type(['type' => 'numeric', "message" => 'form.roomTypeGenerateRoomsType.field_must_be_number'])
                ]
            ])
            ->add('to', 'text', [
                'label' => 'form.roomTypeGenerateRoomsType.last_room_number',
                'required' => true,
                'attr' => ['placeholder' => '100', 'class' => 'spinner'],
                'constraints' => [
                    new NotBlank(),
                    new Type(['type' => 'numeric', "message" => 'form.roomTypeGenerateRoomsType.field_must_be_number'])
                ]
            ]);

        $housingOptions = [
            'label' => 'form.roomTypeGenerateRoomsType.housing',
            'required' => false,
            'class' => 'MBH\Bundle\HotelBundle\Document\Housing'
        ];
        $hotel = $options['hotel'];
        if($hotel) {
            $housingOptions['query_builder'] = function(DocumentRepository $dr) use ($hotel) {
                return $dr->createQueryBuilder()->field('hotel.id')->equals($hotel->getId());
            };
        }
        $builder
            ->add('housing', 'document', $housingOptions);

        $builder
            ->add('floor', 'text', [
                'label' => 'form.roomTypeGenerateRoomsType.floor',
                'required' => false,
                'attr' => ['placeholder' => '3'],
                'constraints' => new Length(['max' => 20])
            ])
            ->add('prefix', 'text', [
                'label' => 'form.roomTypeGenerateRoomsType.prefix',
                'required' => false,
                'data' => '',
                'attr' => ['placeholder' => 'HTL'],
                'help' => 'form.roomTypeGenerateRoomsType.prefix_example',
                'constraints' => new Length(['max' => 20])
            ]);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'constraints' => [
                    new Callback(['methods' => [[get_class($this), 'rangeValidation']]])
                ],
                'entity' => null,
                'hotel' => null
            ]
        );
    }

    public function getName()
    {
        return 'mbh_bundle_hotelbundle_room_type_generate_rooms_type';
    }

    public static function rangeValidation($data, ExecutionContextInterface $context)
    {
        if ($data['from'] >= $data['to']) {
            $context->addViolation('form.roomTypeGenerateRoomsType.first_room_number_less_last_room_number');
        }

        if ($data['to'] - $data['from'] > 500) {
            $context->addViolation('form.roomTypeGenerateRoomsType.too_many_generation_numbers');
        }
    }

}
