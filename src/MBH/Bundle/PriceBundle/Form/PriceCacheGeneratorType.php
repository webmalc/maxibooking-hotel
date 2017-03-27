<?php

namespace MBH\Bundle\PriceBundle\Form;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Doctrine\ODM\MongoDB\DocumentRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Class PriceCacheGeneratorType
 */
class PriceCacheGeneratorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $isIndividualAdditionalPrices = 0;

        if ($options['hotel']) {
            foreach ($options['hotel']->getRoomTypes() as $roomType) {
                if ($options['useCategories']) {
                    if ($roomType->getCategory() && $roomType->getCategory()->getIsIndividualAdditionalPrices() && $roomType->getAdditionalPlaces() > $isIndividualAdditionalPrices) {
                        $isIndividualAdditionalPrices = $roomType->getAdditionalPlaces();
                    }
                } else {
                    if ($roomType->getIsIndividualAdditionalPrices()) {
                        if ($roomType->getAdditionalPlaces() > $isIndividualAdditionalPrices) {
                            $isIndividualAdditionalPrices = $roomType->getAdditionalPlaces();
                        }
                    }
                }
            }
        }

        $repo = $options['useCategories'] ? 'MBHHotelBundle:RoomTypeCategory' : 'MBHHotelBundle:RoomType';

        $pricePlaceHolder = 'mbhpricebundle.form.pricecachegeneratortype.change_sum_or_percent';

        $builder
            ->add('begin', DateType::class, array(
                'label' => 'mbhpricebundle.form.pricecachegeneratortype.nachalo.perioda',
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'group' => 'mbhpricebundle.form.pricecachegeneratortype.settings',
                'data' => new \DateTime('midnight'),
                'required' => true,
                'attr' => [
                    'class' => 'datepicker begin-datepicker input-remember',
                    'data-date-format' => 'dd.mm.yyyy'
                ],
                'constraints' => [new NotBlank(), new Date()],
            ))
            ->add('end', DateType::class, array(
                'label' => 'mbhpricebundle.form.pricecachegeneratortype.konets.perioda',
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'group' => 'mbhpricebundle.form.pricecachegeneratortype.settings',
                'required' => true,
                'attr' => [
                    'class' => 'datepicker end-datepicker input-remember',
                    'data-date-format' => 'dd.mm.yyyy'
                ],
                'constraints' => [new NotBlank(), new Date()],
            ))
            ->add('weekdays',  \MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType::class, [
                'label' => 'mbhpricebundle.form.pricecachegeneratortype.dni.nedeli',
                'required' => false,
                'group' => 'mbhpricebundle.form.pricecachegeneratortype.settings',
                'multiple' => true,
                'choices' => $options['weekdays'],
                'help' => 'mbhpricebundle.form.pricecachegeneratortype.dni.nedeli.dlya.kotorykh.budet.proizvedena.generatsiya.nalichiya.mest',
                'attr' => array('placeholder' => 'mbhpricebundle.form.pricecachegeneratortype.vse.dni.nedeli'),
            ])
            ->add('roomTypes', DocumentType::class, [
                'label' => 'mbhpricebundle.form.pricecachegeneratortype.tipy.nomerov',
                'required' => true,
                'group' => 'mbhpricebundle.form.pricecachegeneratortype.settings',
                'multiple' => true,
                'class' => $repo,
                'query_builder' => function (DocumentRepository $dr) use ($options) {
                    return $dr->fetchQueryBuilder($options['hotel']);
                },
                'help' => 'mbhpricebundle.form.pricecachegeneratortype.tipy.nomerov.dlya.kotorykh.budet.proizvedena.generatsiya.tsen',
                'attr' => array('placeholder' => $options['hotel'] . ': mbhpricebundle.form.pricecachegeneratortype.vse.tipy.nomerov', 'class' => 'select-all'),
            ])
            ->add('tariffs', DocumentType::class, [
                'label' => 'mbhpricebundle.form.pricecachegeneratortype.tarify',
                'required' => true,
                'group' => 'mbhpricebundle.form.pricecachegeneratortype.settings',
                'multiple' => true,
                'class' => 'MBHPriceBundle:Tariff',
                'query_builder' => function (DocumentRepository $dr) use ($options) {
                    return $dr->fetchChildTariffsQuery($options['hotel'], 'prices');
                },
                'help' => 'mbhpricebundle.form.pricecachegeneratortype.tarify.dlya.kotorykh.budet.proizvedena.generatsiya.tsen',
                'attr' => array('placeholder' => $options['hotel'] . ': mbhpricebundle.form.pricecachegeneratortype.vse.tarify', 'class' => 'select-all'),
            ])
            ->add('price', TextType::class, [
                'label' => 'mbhpricebundle.form.pricecachegeneratortype.tsena',
                'group' => 'mbhpricebundle.form.pricecachegeneratortype.price',
                'required' => true,
                'attr' => [
                    'class' => 'spinner--1f delete-prices',
                    'placeholder' => $pricePlaceHolder
                ],
                'constraints' => [
                    new Range(['min' => -1, 'minMessage' => 'mbhpricebundle.form.pricecachegeneratortype.price_cant_be_less_minus_one']),
                    new NotBlank()
                ],
            ])
            ->add('isPersonPrice', CheckboxType::class, [
                'label' => 'mbhpricebundle.form.pricecachegeneratortype.price_for_people',
                'group' => 'mbhpricebundle.form.pricecachegeneratortype.price',
                'value' => true,
                'required' => false,
                'help' => 'mbhpricebundle.form.pricecachegeneratortype.price_for_people_or_number'
            ])
            ->add('singlePrice', HiddenType::class, [
                'required' => false,
                'attr' => ['class' => 'hidden-price'],
                'constraints' => [
                    new Range(['min' => 0, 'minMessage' => 'mbhpricebundle.form.pricecachegeneratortype.price_can_not_be_less_than_zero'])
                ],
            ])
            ->add('singlePriceFake', TextType::class, [
                'label' => 'mbhpricebundle.form.pricecachegeneratortype.price_for_single_accommodation',
                'group' => 'mbhpricebundle.form.pricecachegeneratortype.price',
                'attr' => [
                    'class' => 'text-price',
                    'placeholder' => $pricePlaceHolder
                ],
                'required' => false,
                'help' => 'mbhpricebundle.form.pricecachegeneratortype.price_for_single_room_bookings'
            ])
            ->add('additionalPrice', HiddenType::class, [
                'required' => false,
                'attr' => ['class' => 'hidden-price'],
                'constraints' => [
                    new Range(['min' => 0, 'minMessage' => 'mbhpricebundle.form.pricecachegeneratortype.price_can_not_be_less_than_zero'])
                ],
            ])
            ->add('additionalPriceFake', TextType::class, [
                'label' => 'mbhpricebundle.form.pricecachegeneratortype.price_one_place_accomodation',
                'group' => 'mbhpricebundle.form.pricecachegeneratortype.price',
                'attr' => [
                    'class' => 'text-price',
                    'placeholder' => $pricePlaceHolder
                ],
                'required' => false,
                'help' => 'mbhpricebundle.form.pricecachegeneratortype.price_for_single_room_bookings'
            ])
            ->add('childPrice', HiddenType::class, [
                'required' => false,
                'attr' => ['class' => 'hidden-price'],
                'constraints' => [
                    new Range(['min' => 0, 'minMessage' => 'mbhpricebundle.form.pricecachegeneratortype.price_cant_be_less_minus_one']),
                ],
            ])
            ->add('childPriceFake', TextType::class, [
                'label' => 'mbhpricebundle.form.pricecachegeneratortype.price_for_children_defaulp_place',
                'attr' => [
                    'class' => 'text-price',
                    'placeholder' => $pricePlaceHolder
                ],
                'group' => 'mbhpricebundle.form.pricecachegeneratortype.price',
                'required' => false,
            ])
            ->add('additionalPrice', HiddenType::class, [
                'required' => false,
                'attr' => ['class' => 'hidden-price'],
                'constraints' => [
                    new Range(['min' => 0, 'minMessage' => 'mbhpricebundle.form.pricecachegeneratortype.price_can_not_be_less_than_zero'])
                ],
            ])
            ->add('additionalPriceFake', TextType::class, [
                'label' => 'mbhpricebundle.form.pricecachegeneratortype.price_adult_extra_places',
                'attr' => [
                    'class' => 'text-price',
                    'placeholder' => $pricePlaceHolder
                ],
                'group' => 'mbhpricebundle.form.pricecachegeneratortype.price',
                'required' => false,
            ])
            ->add('additionalChildrenPrice', HiddenType::class, [
                'required' => false,
                'attr' => ['class' => 'hidden-price'],
                'constraints' => [
                    new Range(['min' => 0, 'minMessage' => 'mbhpricebundle.form.pricecachegeneratortype.price_can_not_be_less_than_zero'])
                ],
            ])
            ->add('additionalChildrenPriceFake', TextType::class, [
                'label' => 'mbhpricebundle.form.pricecachegeneratortype.tcena_detskogo_dop_mesta',
                'attr' => [
                    'class' => 'text-price',
                    'placeholder' => $pricePlaceHolder
                ],
                'group' => 'mbhpricebundle.form.pricecachegeneratortype.price',
                'required' => false,
            ])
        ;

        if ($isIndividualAdditionalPrices) {
            for ($i = 1; $i < $isIndividualAdditionalPrices; $i++) {
                $builder
                    ->add('additionalPrice' . $i, HiddenType::class, [
                        'required' => false,
                        'attr' => ['class' => 'hidden-price'],
                        'constraints' => [
                            new Range(['min' => 0, 'minMessage' => 'mbhpricebundle.form.pricecachegeneratortype.price_can_not_be_less_than_zero'])
                        ],
                    ]);
                $builder
                    ->add('additionalPriceFake' . $i, TextType::class, [
                        'label' => 'mbhpricebundle.form.pricecachegeneratortype.price_adult_extra_places' . ' #' . ($i + 1),
                        'attr' => [
                            'class' => 'text-price',
                            'placeholder' => $pricePlaceHolder
                        ],
                        'group' => 'mbhpricebundle.form.pricecachegeneratortype.price',
                        'required' => false
                    ])
                ;
                $builder
                    ->add('additionalChildrenPrice' . $i, HiddenType::class, [
                        'required' => false,
                        'attr' => ['class' => 'hidden-price'],
                        'constraints' => [
                            new Range(['min' => 0, 'minMessage' => 'mbhpricebundle.form.pricecachegeneratortype.price_can_not_be_less_than_zero'])
                        ],
                    ]);
                $builder
                    ->add('additionalChildrenPriceFake' . $i, TextType::class, [
                        'label' => 'mbhpricebundle.form.pricecachegeneratortype.tcena_detskogo_dop_mesta' . ' #' . ($i + 1),
                        'attr' => [
                            'class' => 'text-price',
                            'placeholder' => $pricePlaceHolder
                        ],
                        'group' => 'mbhpricebundle.form.pricecachegeneratortype.price',
                        'required' => false
                    ]);
            }
            $builder->add('additionalPricesCount', HiddenType::class, ['data' => $isIndividualAdditionalPrices]);
        }

        $builder->add('saveForm', CheckboxType::class, [
            'label' => 'mbhpricebundle.form.pricecachegeneratortype.remember',
            'group' => 'mbhpricebundle.form.pricecachegeneratortype.remember_to_reuse',
            'required' => false
        ]);
    }

    public function checkDates($data, ExecutionContextInterface $context)
    {
        if ($data['begin'] >= $data['end']) {
            $context->addViolation('mbhpricebundle.form.pricecachegeneratortype.beginning_period_should_be_less_than_end_period');
        }
        if ($data['end']->diff($data['begin'])->format("%a") > 370) {
            $context->addViolation('mbhpricebundle.form.pricecachegeneratortype.period_can_not_be_more_than_year');
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'weekdays' => [],
            'hotel' => null,
            'useCategories' => false,
            'constraints' => new Callback([$this, 'checkDates'])
        ]);
    }

    public function getBlockPrefix()
    {
        return 'mbh_price_bundle_price_cache_generator';
    }

}
