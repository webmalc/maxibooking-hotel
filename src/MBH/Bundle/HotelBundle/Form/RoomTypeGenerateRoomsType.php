<?php

namespace MBH\Bundle\HotelBundle\Form;

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
        if ($entity && $entity->getHotel()->getIsHostel() && $entity->getCalculationType() == 'customPrices') {
            $hostel = true;
        } else {
            $hostel = false;
        }

        $builder
                ->add('from', 'text', [
                    'label' => ($hostel) ? 'form.roomTypeGenerateRoomsType.first_bed_number' : 'form.roomTypeGenerateRoomsType.first_room_number',
                    'required' => true,
                    'attr' => ['placeholder' => '1', 'class' => 'spinner'],
                    'constraints' => [
                        new NotBlank(),
                        new Type(['type' => 'numeric', "message" => 'form.roomTypeGenerateRoomsType.field_must_be_number'])
                    ]
                ])
                ->add('to', 'text', [
                    'label' => ($hostel) ? 'form.roomTypeGenerateRoomsType.last_bed_number' : 'form.roomTypeGenerateRoomsType.last_room_number',
                    'required' => true,
                    'attr' => ['placeholder' => '100', 'class' => 'spinner'],
                    'constraints' => [
                        new NotBlank(),
                        new Type(['type' => 'numeric', "message" => 'form.roomTypeGenerateRoomsType.field_must_be_number'])
                    ]
                ])
                ->add('prefix', 'text', [
                    'label' => 'form.roomTypeGenerateRoomsType.prefix',
                    'required' => false,
                    'data' => ($hostel) ? $entity->getName() . '/' : '',
                    'attr' => ['placeholder' => 'HTL'],
                    'help' => 'form.roomTypeGenerateRoomsType.prefix_example',
                    'constraints' => new Length(['max' => 20])
                ])
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
                [ 'constraints' => [
                        new Callback(['methods' => [[get_class($this), 'rangeValidation']]])
                    ],
                    'entity' => null
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
