<?php

namespace MBH\Bundle\HotelBundle\Form;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Doctrine\ODM\MongoDB\DocumentRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class RoomTypeGenerateRoomsType extends AbstractType
{

    public static function rangeValidation($data, ExecutionContextInterface $context)
    {
        if ($data['from'] >= $data['to']) {
            $context->addViolation('form.roomTypeGenerateRoomsType.first_room_number_less_last_room_number');
        }

        if ($data['to'] - $data['from'] > 500) {
            $context->addViolation('form.roomTypeGenerateRoomsType.too_many_generation_numbers');
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $entity = $options['entity'];

        $builder
            ->add('from', TextType::class, [
                'label' => 'form.roomTypeGenerateRoomsType.first_room_number',
                'required' => true,
                'attr' => ['placeholder' => '1', 'class' => 'spinner'],
                'constraints' => [
                    new NotBlank(),
                    new Type(['type' => 'numeric', "message" => 'form.roomTypeGenerateRoomsType.field_must_be_number'])
                ]
            ])
            ->add('to', TextType::class, [
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
        if ($hotel) {
            $housingOptions['query_builder'] = function (DocumentRepository $dr) use ($hotel) {
                return $dr->createQueryBuilder()->field('hotel.id')->equals($hotel->getId());
            };
        }
        $builder
            ->add('housing', DocumentType::class, $housingOptions);

        $builder
            ->add('floor', TextType::class, [
                'label' => 'form.roomTypeGenerateRoomsType.floor',
                'required' => false,
                'attr' => ['placeholder' => '3'],
                'constraints' => new Length(['max' => 20])
            ])
            ->add('prefix', TextType::class, [
                'label' => 'form.roomTypeGenerateRoomsType.prefix',
                'required' => false,
                'data' => '',
                'attr' => ['placeholder' => 'HTL'],
                'help' => 'form.roomTypeGenerateRoomsType.prefix_example',
                'constraints' => new Length(['max' => 20])
            ])
            ->add('isSmoking', CheckboxType::class, [
                'label' => 'form.roomType_generator.is_smoking.label',
                'help' => 'form.roomType.is_smoking.help',
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'constraints' => [
                    new Callback([$this, 'rangeValidation'])
                ],
                'entity' => null,
                'hotel' => null
            ]
        );
    }

    public function getBlockPrefix()
    {
        return 'mbh_bundle_hotelbundle_room_type_generate_rooms_type';
    }

}
