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

        $pricePlaceHolder = 'Укажите сумму или процент (15%) от цены';

        $builder
            ->add('begin', DateType::class, array(
                'label' => 'mbhpricebundle.form.pricecachegeneratortype.nachalo.perioda',
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
                'label' => 'mbhpricebundle.form.pricecachegeneratortype.konets.perioda',
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
                'label' => 'mbhpricebundle.form.pricecachegeneratortype.dni.nedeli',
                'required' => false,
                'group' => 'Настройки',
                'multiple' => true,
                'choices' => $options['weekdays'],
                'help' => 'mbhpricebundle.form.pricecachegeneratortype.dni.nedeli.dlya.kotorykh.budet.proizvedena.generatsiya.nalichiya.mest',
                'attr' => array('placeholder' => 'mbhpricebundle.form.pricecachegeneratortype.vse.dni.nedeli'),
            ])
            ->add('roomTypes', DocumentType::class, [
                'label' => 'mbhpricebundle.form.pricecachegeneratortype.tipy.nomerov',
                'required' => true,
                'group' => 'Настройки',
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
                'group' => 'Настройки',
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
            $builder->add('additionalPricesCount', HiddenType::class, ['data' => $isIndividualAdditionalPrices]);
        }

        $builder->add('saveForm', CheckboxType::class, [
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

    public function getBlockPrefix()
    {
        return 'mbh_price_bundle_price_cache_generator';
    }

}
