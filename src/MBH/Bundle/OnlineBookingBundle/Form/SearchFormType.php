<?php
/**
 * Created by Zavalyuk Alexandr (Zalex).
 * email: zalex@zalex.com.ua
 * Date: 9/26/16
 * Time: 11:27 AM
 */

namespace MBH\Bundle\OnlineBookingBundle\Form;


use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
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
        $roomTypes = $roomTypeRepository->findAll();
        $roomTypeList = [];
        $hotelIds = [];
        /** @var RoomType $roomType */
        foreach ($roomTypes as $roomType) {
            $hotelIds[$roomType->getId()] = $roomType->getHotel()->getId();
            $roomTypeList[$roomType->getId()] = $roomType->getFullTitle();
        }

        $builder
            ->add('hotel', DocumentType::class, [
                'label' => 'Отель',
                'required' => false,
                'empty_value' => '',
                'class' => Hotel::class,
                'property' => 'fullTitle'
            ])
            ->add('roomType', ChoiceType::class, [
                'label' => 'Тип номера',
                'required' => false,
                'empty_value' => '',
                'choices' => $roomTypeList,
                'choice_attr' => function ($roomType) use ($hotelIds) {
                    return ['data-hotel' => $hotelIds[$roomType]];
                }
            ])
            ->add('range', TextType::class, [
                'label' => 'Даты',
                'required' => false,
                'mapped' => false
            ])
            //TODO:Есть два разных типа, одно из них расширение, какое выбирать? надо позже решить.
            ->add('begin', DateType::class, [
                'label' => 'Заезд',
                'widget' => 'single_text',
                'required' => false,
                'format' => 'dd.MM.yyyy',
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->add('end', DateType::class, [
                'label' => 'Выезд',
                'widget' => 'single_text',
                'required' => false,
                'format' => 'dd.MM.yyyy',
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->add('adults', IntegerType::class, [
                'label' => 'Взрослые',
                'attr' => ['min' => 1],
                'data' => 1,
            ])
            ->add('children', IntegerType::class, [
                'label' => 'Дети',
                'attr' => ['min' => 0, 'max' => 5],
                'required' => false
            ])
            ->add('children_age', CollectionType::class, [
                'label' => 'Возраста детей',
                'required' => false,
                'type' => 'integer',
                'prototype' => true,
                'allow_add' => true,
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