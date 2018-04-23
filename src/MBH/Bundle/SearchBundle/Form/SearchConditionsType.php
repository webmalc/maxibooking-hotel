<?php


namespace MBH\Bundle\SearchBundle\Form;


use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\SearchBundle\Document\SearchConditions;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;

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
                'tariffs',
                DocumentType::class,
                [
                    'class' => Tariff::class,
                    'required' => false,
                    'multiple' => true
                ]
            )
            ->add(
                'additionalBefore',
                IntegerType::class
            )
            ->add(
                'additionalAfter',
                IntegerType::class
            )
            ->add(
                'childrenAges',
                HiddenType::class,
                [
                    'required' => false,
                    'constraints' => [
                        new Callback([
                            'callback' => [$this, 'chackArrayOfInteger'],

                        ])
                    ]
                ]
            )

        ;
    }

    public function chackArrayOfInteger($data)
    {
        /** TODO: Create a validator */
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