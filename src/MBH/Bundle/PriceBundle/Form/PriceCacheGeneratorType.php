<?php

namespace MBH\Bundle\PriceBundle\Form;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Lib\PriceCacheHolderDataGeneratorForm;
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
    /**
     * @var string[]
     */
    private $weekdays;

    public function __construct(array $weekdays) {
        $this->weekdays = $weekdays;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $isIndividualAdditionalPrices = 0;

        /** @var PriceCacheHolderDataGeneratorForm $generator */
        $generator = $builder->getData();
        $hotel = $generator->getHotel();

        if ($hotel !== null) {
            /** @var RoomType $roomType */
            foreach ($hotel->getRoomTypes() as $roomType) {
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

        $pricePlaceHolder = 'Укажите сумму или процент (15%) от цены';

        $builder
            ->add('begin', DateType::class, array(
                'label' => 'Начало периода',
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'group' => 'Настройки',
                'data' => new \DateTime('midnight'),
                'required' => true,
                'attr' => [
                    'class' => 'datepicker begin-datepicker input-remember',
                    'data-date-format' => 'dd.mm.yyyy'
                ],
                'constraints' => [new NotBlank(), new Date()],
            ))
            ->add('end', DateType::class, array(
                'label' => 'Конец периода',
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'group' => 'Настройки',
                'required' => true,
                'attr' => [
                    'class' => 'datepicker end-datepicker input-remember',
                    'data-date-format' => 'dd.mm.yyyy'
                ],
                'constraints' => [new NotBlank(), new Date()],
            ))
            ->add('weekdays',  \MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType::class, [
                'label' => 'Дни недели',
                'required' => false,
                'group' => 'Настройки',
                'multiple' => true,
                'choices' => $this->weekdays,
                'help' => 'Дни недели для которых будет произведена генерация наличия мест',
                'attr' => array('placeholder' => 'все дни недели'),
            ])
            ->add('roomTypes', DocumentType::class, [
                'label' => 'Типы номеров',
                'required' => true,
                'group' => 'Настройки',
                'multiple' => true,
                'class' => $repo,
                'query_builder' => function (DocumentRepository $dr) use ($hotel) {
                    return $dr->fetchQueryBuilder($hotel);
                },
                'help' => 'Типы номеров для которых будет произведена генерация цен',
                'attr' => array('placeholder' => $hotel . ': все типы номеров', 'class' => 'select-all'),
            ])
            ->add('tariffs', DocumentType::class, [
                'label' => 'Тарифы',
                'required' => true,
                'group' => 'Настройки',
                'multiple' => true,
                'class' => 'MBHPriceBundle:Tariff',
                'query_builder' => function (DocumentRepository $dr) use ($hotel) {
                    return $dr->fetchChildTariffsQuery($hotel, 'prices');
                },
                'help' => 'Тарифы для которых будет произведена генерация цен',
                'attr' => array('placeholder' => $hotel . ': все тарифы', 'class' => 'select-all'),
            ])
            ->add('price', TextType::class, [
                'label' => 'Цена',
                'group' => 'Цены',
                'required' => true,
                'attr' => [
                    'class' => 'spinner--1f delete-prices',
                    'placeholder' => $pricePlaceHolder
                ],
                'constraints' => [
                    new Range(['min' => -1, 'minMessage' => 'Цена не может быть меньше минус одного']),
                    new NotBlank()
                ],
            ])
            ->add('isPersonPrice', CheckboxType::class, [
                'label' => 'Цена за человека?',
                'group' => 'Цены',
                'value' => true,
                'required' => false,
                'help' => 'Цена за человека или за номер?'
            ])
            ->add('singlePrice', HiddenType::class, [
                'required' => false,
                'attr' => ['class' => 'hidden-price'],
                'constraints' => [
                    new Range(['min' => 0, 'minMessage' => 'Цена не может быть меньше нуля'])
                ],
            ])
            ->add('singlePriceFake', TextType::class, [
                'label' => 'Цена 1-местного размещения',
                'group' => 'Цены',
                'attr' => [
                    'class' => 'text-price',
                    'placeholder' => $pricePlaceHolder
                ],
                'required' => false,
                'help' => 'Цена при бронировании номера на одного человека.'
            ])
            ->add('additionalPrice', HiddenType::class, [
                'required' => false,
                'attr' => ['class' => 'hidden-price'],
                'constraints' => [
                    new Range(['min' => 0, 'minMessage' => 'Цена не может быть меньше нуля'])
                ],
            ])
            ->add('additionalPriceFake', TextType::class, [
                'label' => 'Цена 1-местного размещения',
                'group' => 'Цены',
                'attr' => [
                    'class' => 'text-price',
                    'placeholder' => $pricePlaceHolder
                ],
                'required' => false,
                'help' => 'Цена при бронировании номера на одного человека.'
            ])
            ->add('childPrice', HiddenType::class, [
                'required' => false,
                'attr' => ['class' => 'hidden-price'],
                'constraints' => [
                    new Range(['min' => 0, 'minMessage' => 'Цена не может быть меньше минус одного']),
                ],
            ])
            ->add('childPriceFake', TextType::class, [
                'label' => 'Цена за детское осн. место',
                'attr' => [
                    'class' => 'text-price',
                    'placeholder' => $pricePlaceHolder
                ],
                'group' => 'Цены',
                'required' => false,
            ])
            ->add('additionalPrice', HiddenType::class, [
                'required' => false,
                'attr' => ['class' => 'hidden-price'],
                'constraints' => [
                    new Range(['min' => 0, 'minMessage' => 'Цена не может быть меньше нуля'])
                ],
            ])
            ->add('additionalPriceFake', TextType::class, [
                'label' => 'Цена взрослого доп. места',
                'attr' => [
                    'class' => 'text-price',
                    'placeholder' => $pricePlaceHolder
                ],
                'group' => 'Цены',
                'required' => false,
            ])
            ->add('additionalChildrenPrice', HiddenType::class, [
                'required' => false,
                'attr' => ['class' => 'hidden-price'],
                'constraints' => [
                    new Range(['min' => 0, 'minMessage' => 'Цена не может быть меньше нуля'])
                ],
            ])
            ->add('additionalChildrenPriceFake', TextType::class, [
                'label' => 'Цена детского доп. места',
                'attr' => [
                    'class' => 'text-price',
                    'placeholder' => $pricePlaceHolder
                ],
                'group' => 'Цены',
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
                            new Range(['min' => 0, 'minMessage' => 'Цена не может быть меньше нуля'])
                        ],
                    ]);
                $builder
                    ->add('additionalPriceFake' . $i, TextType::class, [
                        'label' => 'Цена взрослого доп. места #' . ($i + 1),
                        'attr' => [
                            'class' => 'text-price',
                            'placeholder' => $pricePlaceHolder
                        ],
                        'group' => 'Цены',
                        'required' => false
                    ])
                ;
                $builder
                    ->add('additionalChildrenPrice' . $i, HiddenType::class, [
                        'required' => false,
                        'attr' => ['class' => 'hidden-price'],
                        'constraints' => [
                            new Range(['min' => 0, 'minMessage' => 'Цена не может быть меньше нуля'])
                        ],
                    ]);
                $builder
                    ->add('additionalChildrenPriceFake' . $i, TextType::class, [
                        'label' => 'Цена детского доп. места #' . ($i + 1),
                        'attr' => [
                            'class' => 'text-price',
                            'placeholder' => $pricePlaceHolder
                        ],
                        'group' => 'Цены',
                        'required' => false
                    ]);
            }
        }

        $builder->add('saveForm', CheckboxType::class, [
            'label' => 'Запомнить?',
            'group' => 'Запомнить для повторного использования',
            'required' => false
        ]);
    }

    public function checkDates(PriceCacheHolderDataGeneratorForm $data, ExecutionContextInterface $context)
    {
        if ($data->getBegin() > $data->getEnd()) {
            $context->addViolation('Начало периода должно быть меньше конца периода.');
        }
        if ($data->getEnd()->diff($data->getBegin())->format("%a") > 370) {
            $context->addViolation('Период не может быть больше года.');
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'    => PriceCacheHolderDataGeneratorForm::class,
            'useCategories' => false,
            'constraints'   => new Callback([$this, 'checkDates']),
        ]);
    }

    public function getBlockPrefix()
    {
        return 'mbh_price_bundle_price_cache_generator';
    }

}
