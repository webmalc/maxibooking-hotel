<?php

namespace MBH\Bundle\OnlineBookingBundle\Form;


use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Doctrine\Bundle\MongoDBBundle\Tests\Fixtures\Form\Document;
use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Document\SpecialRepository;
use MBH\Bundle\PriceBundle\Lib\SpecialFilter;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use MBH\Bundle\OnlineBookingBundle\Lib\OnlineSearchFormData;

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
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'hotel',
                DocumentType::class,
                [
                    'label' => 'Пансионат',
                    'required' => false,
                    'placeholder' => 'Все пансионаты',
                    'class' => Hotel::class,
                    'choice_label' => 'fullTitle',
                    'attr' => [
                        'class' => 'dropdown',

                    ],
                    'query_builder' => static function (DocumentRepository $documentRepository) {
                        return $documentRepository->createQueryBuilder()
                            ->field('_id')->in(['56fbd22174eb5383728b4567', '5705190e74eb53461c8b4916'])
                            ->sort('fullTitle', 'DESC');
                    },

                ]
            )
            ->add(
                'begin',
                DateType::class,
                [
                    'label' => 'Дата заезда',
                    'widget' => 'single_text',
                    'required' => false,
                    'format' => 'dd.MM.yyyy',
                    'constraints' => [
                        new NotBlank(),
                    ],
                    'attr' => [
                        'class' => 'tcal',
                    ],
                ]
            )
            ->add(
                'end',
                DateType::class,
                [
                    'label' => 'Дата выезда',
                    'widget' => 'single_text',
                    'required' => false,
                    'format' => 'dd.MM.yyyy',
                    'constraints' => [
                        new NotBlank(),
                    ],
                    'attr' => [
                        'class' => 'tcal',
                    ],
                ]
            )
            ->add(
                'adults',
                ChoiceType::class,
                [
                    'label' => 'Взрослые',
                    'choices' => array_combine(range(1, 6), range(1, 6)),
                    'data' => 1,
                    'placeholder' => false,
                    'attr' => [
                        'class' => 'dropdown',

                    ],
                ]
            )
            ->add(
                'children',
                ChoiceType::class,
                [
                    'label' => 'Дети',
                    'choices' => range(0, 5),
                    'attr' => [
                        'min' => 0,
                        'max' => 5,
                        'class' => 'dropdown',
                    ],

                ]
            )
            ->add(
                'children_age',
                CollectionType::class,
                [
                    'label' => 'Возраст детей на момент заезда',
                    'label_attr' => [
                        'class' => 'children_age_label hidden',
                    ],
                    'required' => false,
                    'entry_type' => ChoiceType::class,
                    'entry_options' => [
                        'label' => false,
                        'placeholder' => false,
                        'choices' => range(0, 13),
                        'attr' => [
                            /*'class' => 'children_age_row dropdown'*/
                            'class' => 'children_age_row',
                        ],
                        'data' => 12,
                    ],
                    'prototype' => true,
                    'allow_add' => true,
                    'allow_delete' => true,
                ]
            )
            ->add(
                'roomType',
                DocumentType::class,
                [
                    'class' => RoomType::class,
                    'required' => false,
                ]
            )
            ->add(
                'special',
                DocumentType::class,
                [
                    'class' => 'MBHPriceBundle:Special',
                    'required' => false,
                    'query_builder' => static function (DocumentRepository $documentRepository) {
                        $specialsFilter = new SpecialFilter();
                        $specialsFilter->setRemain(1);
                        $begin = new \DateTime('now midnight');
                        $specialsFilter->setBegin($begin);
                        /** @var SpecialRepository $documentRepository */
                        $qb = $documentRepository->getFilteredQueryBuilder($specialsFilter);
                        $qb->addAnd($qb->expr()->field('begin')->gt(new \DateTime('midnight')));
                        $qb->field('prices')->exists(true);

                        return $qb;
                    },
                ]
            )
            ->add(
                'addDates',
                CheckboxType::class,
                [
                    'required' => false,
                    'value' => 'true',
                ]
            )
            ->addEventListener(
                FormEvents::PRE_SUBMIT,
                function (FormEvent $event) use ($options) {
                    $this->forceFilterChildrenAge($event, $options);
                }

            );

    }


    /**
     * If infants more than one, force increase children age to infant_age + 1
     * @param FormEvent $event
     * @param $options
     */
    private function forceFilterChildrenAge(FormEvent $event, $options): void
    {

        $data = $event->getData();
        if (($data['children'] ?? 0) > 1) {
            $children_age = $data['children_age'] ?? null;
            if (is_array($children_age)) {
                $isWasInfant = false;
                foreach ($data['children_age'] as $index => $age) {
                    if ((int)$age <= (int)$options['infant_age']) {
                        if (!$isWasInfant) {
                            $isWasInfant = true;
                        } else {
                            $age = (int)$options['infant_age'] + 1;
                            $data['children_age'][$index] = $age;
                        }
                    }
                }

            }
        }
        $event->setData($data);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults(
                [
                    'method' => Request::METHOD_GET,
                    'csrf_protection' => false,
                    'attr' => [
                        'class' => 'booking-form',
                    ],
                    'data_class' => OnlineSearchFormData::class,
                    'infant_age' => 2,
                ]
            );
    }

    public function getBlockPrefix(): string
    {
        return 'search_form';
    }


}