<?php


namespace MBH\Bundle\SearchBundle\Form;


use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\SearchBundle\Document\SearchConditions;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchConditionsType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     * @throws \Symfony\Component\Validator\Exception\MissingOptionsException
     * @throws \Symfony\Component\Validator\Exception\InvalidOptionsException
     * @throws \Symfony\Component\Validator\Exception\ConstraintDefinitionException
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'begin',
                DateType::class,
                [
                    'format' => 'dd.MM.yyyy',
                    'widget' => 'single_text',
                ]
            )
            ->add(
                'end',
                DateType::class,
                [
                    'format' => 'dd.MM.yyyy',
                    'widget' => 'single_text',
                ]
            )
            ->add('adults', NumberType::class)
            ->add('children', NumberType::class)
            ->add(
                'roomTypes',
                DocumentType::class,
                [
                    'class' => RoomType::class,
                    'required' => false,
                    'multiple' => true,

                ]
            )
            ->add(
                'hotels',
                DocumentType::class,
                [
                    'class' => Hotel::class,
                    'required' => false,
                    'multiple' => true
                ]
            )
            ->add(
                'tariffs',
                DocumentType::class,
                [
                    'class' => Tariff::class,
                    'required' => false,
                    'multiple' => true,
                ]
            )
            ->add(
                'additionalBegin',
                IntegerType::class,
                [
                    'required' => false,
                ]
            )
            ->add(
                'additionalEnd',
                IntegerType::class,
                [
                    'required' => false,
                ]
            )
            ->add(
                'childrenAges',
                CollectionType::class,
                [
                    'required' => false,
                    'entry_type' => ChoiceType::class,
                    'entry_options' => [
                        'label' => false,
                        'placeholder' => false,
                        'choices' => range(0, 13),
                        'empty_data' => 12,
                        'compound' => false,
                    ],
                    'prototype' => false,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'by_reference' => false,
                ]
            )
            ->add(
                'isOnline',
                CheckboxType::class,
                [
                    'required' => false,
                ]
            );
    }

    /**
     * @param OptionsResolver $resolver
     * @throws \Symfony\Component\OptionsResolver\Exception\AccessException
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults(
                [
                    'data_class' => SearchConditions::class,
                    'csrf_protection' => false,
                ]
            );
    }

}