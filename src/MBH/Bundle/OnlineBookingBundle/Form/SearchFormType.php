<?php

namespace MBH\Bundle\OnlineBookingBundle\Form;


use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Doctrine\Bundle\MongoDBBundle\Tests\Fixtures\Form\Document;
use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class SearchFormType extends AbstractType
{
    private $container;

    /**
     * SearchFormType constructor.
     * @param $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var DocumentRepository $roomTypeRepository */
        $roomTypeRepository = $this->container->get('mbh.hotel.room_type_manager')->getRepository();
        $roomTypes = $roomTypeRepository
            ->createQueryBuilder()
            ->field('isEnabled')
            ->equals(true)
            ->getQuery()
            ->execute();
        $roomTypeList = [];
        $hotelIds = [];
        /** @var RoomType $roomType */
        foreach ($roomTypes as $roomType) {
            $hotelIds[$roomType->getId()] = $roomType->getHotel()->getId();
            $roomTypeList[$roomType->getId()] = $roomType->getFullTitle();
        }

        $builder
            ->add('hotel', DocumentType::class, [
                'label' => 'Пансионат',
                'required' => false,
                'empty_value' => 'Все пансионаты',
                'class' => Hotel::class,
                'property' => 'fullTitle',
                'query_builder' => function (DocumentRepository $documentRepository) {
                    return $documentRepository->createQueryBuilder()
                        ->field('_id')->in(['56fbd22174eb5383728b4567', '5705190e74eb53461c8b4916'])
                        ->sort('fullTitle', 'DESC');
                }

            ])
            ->add('roomType', ChoiceType::class, [
                'label' => 'Тип номера',
                'required' => false,
                'empty_value' => 'Все типы номеров',
                'choices' => $roomTypeList,
                'choice_attr' => function ($roomType) use ($hotelIds) {
                    return ['data-hotel' => $hotelIds[$roomType]];
                }
            ])
            ->add('begin', DateType::class, [
                'label' => 'Заезд',
                'widget' => 'single_text',
                'required' => false,
                'format' => 'dd.MM.yyyy',
                'constraints' => [
                    new NotBlank()
                ],
                'attr' => [
                    'class' => 'input-small'
                ]
            ])
            ->add('end', DateType::class, [
                'label' => 'Выезд',
                'widget' => 'single_text',
                'required' => false,
                'format' => 'dd.MM.yyyy',
                'constraints' => [
                    new NotBlank()
                ],
                'attr' => [
                    'class' => 'input-small'
                ]
            ])
            ->add('adults', ChoiceType::class, [
                'label' => 'Взрослые',
                'choices' => array_combine(range(1,10),range(1,10)),
                'data' => 1,
                'empty_value' => false
            ])
            ->add('children', ChoiceType::class, [
                'label' => 'Дети',
                'choices' => range(0, 5),
                'attr' => ['min' => 0, 'max' => 5],
                'empty_value' => false

            ])
            ->add('children_age', CollectionType::class, [
                'label' => 'Возраст детей на момент заезда',
                'label_attr' => [
                    'class' => 'children_age_label hidden'
                ],
                'required' => false,
                'entry_type' => ChoiceType::class,
                'entry_options' => [
                    'label' => false,
                    'choices' => range(0,17),
                    'attr' => [
                        'class' => 'children_age_row'
                    ],
                    'empty_value' => false,
                    'data' => 0
                ],
                'prototype' => true,
                'allow_add' => true,
                'allow_delete' => true,
            ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults(
                [
                    'method' => Request::METHOD_GET,
                    'csrf_protection' => false
                ]
            );
    }


}