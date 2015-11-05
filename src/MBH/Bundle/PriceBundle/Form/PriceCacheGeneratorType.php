<?php

namespace MBH\Bundle\PriceBundle\Form;

use Doctrine\ODM\MongoDB\DocumentRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class PriceCacheGeneratorType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $isIndividualAdditionalPrices = 0;

        if ($options['hotel']) {
            foreach ($options['hotel']->getRoomTypes() as $roomType) {
                if ($options['categories']) {
                    if ($roomType->getCategory()->getIsIndividualAdditionalPrices()) {
                        if ($roomType->getAdditionalPlaces() > $isIndividualAdditionalPrices) {
                            $isIndividualAdditionalPrices = $roomType->getAdditionalPlaces();
                        }
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

        $repo = $options['categories'] ? 'MBHHotelBundle:RoomTypeCategory' : 'MBHHotelBundle:RoomType';

        $builder
            ->add('begin', 'date', array(
                'label' => 'Начало периода',
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'group' => 'Настройки',
                'data' => new \DateTime('midnight'),
                'required' => true,
                'attr' => array('class' => 'datepicker begin-datepiker input-remember', 'data-date-format' => 'dd.mm.yyyy'),
                'constraints' => [new NotBlank(), new Date()],
            ))
            ->add('end', 'date', array(
                'label' => 'Конец периода',
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'group' => 'Настройки',
                'required' => true,
                'attr' => array('class' => 'datepicker end-datepiker input-remember', 'data-date-format' => 'dd.mm.yyyy'),
                'constraints' => [new NotBlank(), new Date()],
            ))
            ->add('weekdays', 'choice', [
                'label' => 'Дни недели',
                'required' => false,
                'group' => 'Настройки',
                'multiple' => true,
                'choices' => $options['weekdays'],
                'help' => 'Дни недели для готорых будет произведена генерация наличия мест',
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
                'attr' => array('placeholder' => $options['hotel'] . ': все типы номеров'),
            ])
            ->add('tariffs', 'document', [
                'label' => 'Тарифы',
                'required' => true,
                'group' => 'Настройки',
                'multiple' => true,
                'class' => 'MBHPriceBundle:Tariff',
                'query_builder' => function (DocumentRepository $dr) use ($options) {
                    return $dr->fetchQueryBuilder($options['hotel']);
                },
                'help' => 'Тарифы для готорых будет произведена генерация цен',
                'attr' => array('placeholder' => $options['hotel'] . ': все тарифы'),
            ])
            ->add('price', 'text', [
                'label' => 'Цена',
                'group' => 'Цены',
                'required' => true,
                'data' => null,
                'attr' => ['class' => 'spinner--1f delete-prices'],
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
            ->add('singlePrice', 'text', [
                'label' => 'Цена 1-местного размещения',
                'group' => 'Цены',
                'required' => false,
                'data' => null,
                'attr' => ['class' => 'spinner-0f', 'placeholder' => 'данные будут удалены'],
                'help' => 'Цена при бронировании номера на одного человека.',
                'constraints' => [
                    new Range(['min' => 0, 'minMessage' => 'Цена не может быть меньше нуля'])
                ],
            ])
            ->add('childPrice', 'text', [
                'label' => 'Цена за детское осн. место',
                'group' => 'Цены',
                'required' => false,
                'data' => null,
                'attr' => ['class' => 'spinner-0f'],
                'constraints' => [
                    new Range(['min' => 0, 'minMessage' => 'Цена не может быть меньше минус одного']),
                ],
            ])
            ->add('additionalPrice', 'text', [
                'label' => 'Цена взрослого доп. места',
                'group' => 'Цены',
                'required' => false,
                'data' => null,
                'attr' => ['class' => 'spinner-0f delete-prices', 'placeholder' => 'данные будут удалены'],
                'constraints' => [
                    new Range(['min' => 0, 'minMessage' => 'Цена не может быть меньше нуля'])
                ],
            ])
            ->add('additionalChildrenPrice', 'text', [
                'label' => 'Цена детского доп. места',
                'group' => 'Цены',
                'required' => false,
                'data' => null,
                'attr' => ['class' => 'spinner-0f', 'placeholder' => 'данные будут удалены'],
                'constraints' => [
                    new Range(['min' => 0, 'minMessage' => 'Цена не может быть меньше нуля'])
                ],
            ]);

        if ($isIndividualAdditionalPrices) {
            for ($i = 1; $i < $isIndividualAdditionalPrices; $i++) {
                $builder
                    ->add('additionalPrice' . $i, 'text', [
                        'label' => 'Цена взрослого доп. места #' . ($i + 1),
                        'group' => 'Цены',
                        'required' => false,
                        'data' => null,
                        'attr' => ['class' => 'spinner-0f delete-prices', 'placeholder' => 'данные будут удалены'],
                        'constraints' => [
                            new Range(['min' => 0, 'minMessage' => 'Цена не может быть меньше нуля'])
                        ],
                    ])
                    ->add('additionalChildrenPrice' . $i, 'text', [
                        'label' => 'Цена детского доп. места #' . ($i + 1),
                        'group' => 'Цены',
                        'required' => false,
                        'data' => null,
                        'attr' => ['class' => 'spinner-0f delete-prices', 'placeholder' => 'данные будут удалены'],
                        'constraints' => [
                            new Range(['min' => 0, 'minMessage' => 'Цена не может быть меньше нуля'])
                        ],
                    ]);
            }
            $builder->add('additionalPricesCount', 'hidden', ['data' => $isIndividualAdditionalPrices]);
        }
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

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'weekdays' => [],
            'hotel' => null,
            'categories' => false,
            'constraints' => new Callback([$this, 'checkDates'])
        ]);
    }

    public function getName()
    {
        return 'mbh_bundle_pricebundle_price_cache_generator_type';
    }

}
