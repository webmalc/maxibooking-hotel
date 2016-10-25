<?php

namespace MBH\Bundle\PriceBundle\Form;

use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\PriceBundle\Validator\Constraints\Tariff;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Date;
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

        $pricePlaceHolder = 'Укажите сумму или процент (15%) от цены';

        $builder
            ->add('begin', 'date', array(
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
            ->add('end', 'date', array(
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
            ->add('weekdays', 'choice', [
                'label' => 'Дни недели',
                'required' => false,
                'group' => 'Настройки',
                'multiple' => true,
                'choices' => $options['weekdays'],
                'help' => 'mbhpricebundle.form.pricecachegeneratortype.dninedelidlyagotorykhbudetproizvedenageneratsiyanalichiyamest',
                'attr' => array('placeholder' => 'все дни недели'),
            ])
            ->add('roomTypes', 'document', [
                'label' => 'Типы номеров',
                'required' => true,
                'group' => 'Настройки',
                'multiple' => true,
                'class' => $repo,
                'query_builder' => function (DocumentRepository $dr) use ($options) {
                    return $dr->fetchQueryBuilder($options['hotel']);
                },
                'help' => 'Типы номеров для готорых будет произведена генерация цен',
                'attr' => array('placeholder' => $options['hotel'] . ': все типы номеров', 'class' => 'select-all'),
            ])
            ->add('tariffs', 'document', [
                'label' => 'Тарифы',
                'required' => true,
                'group' => 'Настройки',
                'multiple' => true,
                'class' => 'MBHPriceBundle:Tariff',
                'query_builder' => function (DocumentRepository $dr) use ($options) {
                    return $dr->fetchChildTariffsQuery($options['hotel'], 'prices');
                },
                'help' => 'Тарифы для готорых будет произведена генерация цен',
                'attr' => array('placeholder' => $options['hotel'] . ': все тарифы', 'class' => 'select-all'),
            ])
            ->add('price', 'text', [
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
            ->add('isPersonPrice', 'checkbox', [
                'label' => 'Цена за человека?',
                'group' => 'Цены',
                'value' => true,
                'required' => false,
                'help' => 'Цена за человека или за номер?'
            ])
            ->add('singlePrice', 'hidden', [
                'required' => false,
                'attr' => ['class' => 'hidden-price'],
                'constraints' => [
                    new Range(['min' => 0, 'minMessage' => 'Цена не может быть меньше нуля'])
                ],
            ])
            ->add('singlePriceFake', 'text', [
                'label' => 'Цена 1-местного размещения',
                'group' => 'Цены',
                'attr' => [
                    'class' => 'text-price',
                    'placeholder' => $pricePlaceHolder
                ],
                'required' => false,
                'help' => 'Цена при бронировании номера на одного человека.'
            ])
            ->add('additionalPrice', 'hidden', [
                'required' => false,
                'attr' => ['class' => 'hidden-price'],
                'constraints' => [
                    new Range(['min' => 0, 'minMessage' => 'Цена не может быть меньше нуля'])
                ],
            ])
            ->add('additionalPriceFake', 'text', [
                'label' => 'Цена 1-местного размещения',
                'group' => 'Цены',
                'attr' => [
                    'class' => 'text-price',
                    'placeholder' => $pricePlaceHolder
                ],
                'required' => false,
                'help' => 'Цена при бронировании номера на одного человека.'
            ])
            ->add('childPrice', 'hidden', [
                'required' => false,
                'attr' => ['class' => 'hidden-price'],
                'constraints' => [
                    new Range(['min' => 0, 'minMessage' => 'Цена не может быть меньше минус одного']),
                ],
            ])
            ->add('childPriceFake', 'text', [
                'label' => 'Цена за детское осн. место',
                'attr' => [
                    'class' => 'text-price',
                    'placeholder' => $pricePlaceHolder
                ],
                'group' => 'Цены',
                'required' => false,
            ])
            ->add('additionalPrice', 'hidden', [
                'required' => false,
                'attr' => ['class' => 'hidden-price'],
                'constraints' => [
                    new Range(['min' => 0, 'minMessage' => 'Цена не может быть меньше нуля'])
                ],
            ])
            ->add('additionalPriceFake', 'text', [
                'label' => 'Цена взрослого доп. места',
                'attr' => [
                    'class' => 'text-price',
                    'placeholder' => $pricePlaceHolder
                ],
                'group' => 'Цены',
                'required' => false,
            ])
            ->add('additionalChildrenPrice', 'hidden', [
                'required' => false,
                'attr' => ['class' => 'hidden-price'],
                'constraints' => [
                    new Range(['min' => 0, 'minMessage' => 'Цена не может быть меньше нуля'])
                ],
            ])
            ->add('additionalChildrenPriceFake', 'text', [
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
                    ->add('additionalPrice' . $i, 'hidden', [
                        'required' => false,
                        'attr' => ['class' => 'hidden-price'],
                        'constraints' => [
                            new Range(['min' => 0, 'minMessage' => 'Цена не может быть меньше нуля'])
                        ],
                    ]);
                $builder
                    ->add('additionalPriceFake' . $i, 'text', [
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
                    ->add('additionalChildrenPrice' . $i, 'hidden', [
                        'required' => false,
                        'attr' => ['class' => 'hidden-price'],
                        'constraints' => [
                            new Range(['min' => 0, 'minMessage' => 'Цена не может быть меньше нуля'])
                        ],
                    ]);
                $builder
                    ->add('additionalChildrenPriceFake' . $i, 'text', [
                        'label' => 'Цена детского доп. места #' . ($i + 1),
                        'attr' => [
                            'class' => 'text-price',
                            'placeholder' => $pricePlaceHolder
                        ],
                        'group' => 'Цены',
                        'required' => false
                    ]);
            }
            $builder->add('additionalPricesCount', 'hidden', ['data' => $isIndividualAdditionalPrices]);
        }

        $builder->add('saveForm', 'checkbox', [
            'label' => 'Запомнить?',
            'group' => 'Запомнить для повторного использования',
            'required' => false
        ]);
    }

    public function checkDates($data, ExecutionContextInterface $context)
    {
        if ($data['begin'] >= $data['end']) {
            $context->addViolation('Начало периода должно быть меньше конца периода.');
        }
        if ($data['end']->diff($data['begin'])->format("%a") > 370) {
            $context->addViolation('Период не может быть больше года.');
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

    public function getName()
    {
        return 'mbh_price_bundle_price_cache_generator';
    }

}
