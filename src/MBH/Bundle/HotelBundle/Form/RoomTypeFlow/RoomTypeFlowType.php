<?php

namespace MBH\Bundle\HotelBundle\Form\RoomTypeFlow;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Service\MBHFormBuilder;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\HotelBundle\Document\RoomTypeRepository;
use MBH\Bundle\HotelBundle\Form\RoomTypeType;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\PriceBundle\Document\TariffRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

class RoomTypeFlowType extends AbstractType
{
    private $mbhFormBuilder;
    private $dm;

    public function __construct(MBHFormBuilder $mbhFormBuilder, DocumentManager $dm)
    {
        $this->mbhFormBuilder = $mbhFormBuilder;
        $this->dm = $dm;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        switch ($options['flow_step']) {
            case 1:
                $builder
                    ->add(
                        'roomType',
                        DocumentType::class,
                        [
                            'label' => 'room_type_flow_type.room_type.label',
                            'required' => true,
                            'class' => 'MBHHotelBundle:RoomType',
                            'query_builder' => function (RoomTypeRepository $dr) use ($options) {
                                return $dr->fetchQueryBuilder($options['hotel']);
                            },
                            'data' => $options['roomType'],
                        ]
                    );
                break;
            case 2:
                $this->mbhFormBuilder->mergeFormFields(
                    $builder,
                    RoomTypeType::class,
                    $builder->getData(),
                    ['roomSpace', 'facilities', 'description']
                );
                break;
            case 4:
                $this->mbhFormBuilder->mergeFormFields(
                    $builder,
                    RoomTypeType::class,
                    $builder->getData(),
                    ['places', 'additionalPlaces']
                );
                break;
            case 5:
                $builder
                    ->add(
                        'rooms',
                        TextType::class,
                        [
                            'label' => 'mbhpricebundle.form.roomcachegeneratortype.kolichestvo.mest',
                            'required' => true,
                            'data' => $options['rooms'],
                            'attr' => ['class' => 'spinner'],
                            'constraints' => [
                                new Range(
                                    [
                                        'min' => 0,
                                        'minMessage' => 'mbhpricebundle.room_cache_generator_type.number_of_places_cannot_be_less_then_one',
                                    ]
                                ),
                                new NotBlank(),
                            ],
                            'help' => 'room_type_flow_type.rooms.help',
                        ]
                    );
                break;
            case 6:
                $builder
                    ->add(
                        'isPersonPrice',
                        CheckboxType::class,
                        [
                            'label' => 'mbhpricebundle.form.pricecachegeneratortype.price_for_people',
                            'group' => 'mbhpricebundle.form.pricecachegeneratortype.price',
                            'required' => false,
                            'help' => 'mbhpricebundle.form.pricecachegeneratortype.price_for_people_or_number',
                            'attr' => ($options['isPersonPrice'] ? ['checked' => 'checked'] : [])
                        ]
                    );
                break;
            case 7:
                $builder
                    ->add(
                        'begin',
                        DateType::class,
                        array(
                            'label' => 'mbhpricebundle.form.pricecachegeneratortype.nachaloperioda',
                            'widget' => 'single_text',
                            'format' => 'dd.MM.yyyy',
                            'group' => 'mbhpricebundle.form.pricecachegeneratortype.settings',
                            'data' => $options['begin'],
                            'required' => true,
                            'attr' => [
                                'class' => 'datepicker begin-datepicker',
                                'data-date-format' => 'dd.mm.yyyy',
                            ],
                            'constraints' => [new NotBlank(), new Date()],
                        )
                    )
                    ->add(
                        'end',
                        DateType::class,
                        array(
                            'label' => 'mbhpricebundle.form.pricecachegeneratortype.konetsperioda',
                            'widget' => 'single_text',
                            'format' => 'dd.MM.yyyy',
                            'group' => 'mbhpricebundle.form.pricecachegeneratortype.settings',
                            'required' => true,
                            'attr' => [
                                'class' => 'datepicker end-datepicker',
                                'data-date-format' => 'dd.mm.yyyy',
                            ],
                            'constraints' => [new NotBlank(), new Date()],
                            'data' => $options['end'],
                        )
                    );
                break;
            case 8:
                $builder
                    ->add(
                        'tariff',
                        DocumentType::class,
                        [
                            'label' => 'room_type_flow_type.tariff.label',
                            'required' => true,
                            'class' => Tariff::class,
                            'query_builder' => function (TariffRepository $dr) use ($options) {
                                return $dr->fetchQueryBuilder($options['hotel'], null, true);
                            },
                            'data' => is_null($options['tariff'])
                                ? $this->dm->getRepository('MBHPriceBundle:Tariff')->fetchBaseTariff($options['hotel'])
                                : $options['tariff'],
                        ]
                    );
                break;
            case 9:
                $builder
                    ->add(
                        'price',
                        TextType::class,
                        [
                            'label' => 'mbhpricebundle.form.pricecachegeneratortype.tsena',
                            'group' => 'mbhpricebundle.form.pricecachegeneratortype.price',
                            'required' => true,
                            'attr' => [
                                'class' => 'price-spinner',
                                'placeholder' => 'mbhpricebundle.form.pricecachegeneratortype.change_sum',
                            ],
                            'constraints' => [
                                new Range(
                                    [
                                        'min' => 1,
                                        'minMessage' => 'mbhpricebundle.form.pricecachegeneratortype.price_cant_be_less_minus_one',
                                    ]
                                ),
                                new NotBlank(),
                            ],
                            'data' => $options['price'],
                        ]
                    );

                /** @var RoomType $roomType */
                $roomType = $options['roomType'];
                if ($roomType->getAdditionalPlaces() !== 0) {
                    $builder
                        ->add(
                            'additionalPrice',
                            TextType::class,
                            [
                                'label' => 'mbhpricebundle.form.pricecachegeneratortype.price_adult_extra_places',
                                'attr' => [
                                    'class' => 'price-spinner',
                                    'placeholder' => 'mbhpricebundle.form.pricecachegeneratortype.change_sum_or_percent',
                                ],
                                'group' => 'mbhpricebundle.form.pricecachegeneratortype.price',
                                'required' => false,
                                'data' => $options['additionalPrice']
                            ]
                        );
                }
                break;
            default:
                throw new \InvalidArgumentException('Invalid number of step');
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults(
                [
                    'hotel' => null,
                    'roomType' => null,
                    'tariff' => null,
                    'begin' => null,
                    'end' => null,
                    'isPersonPrice' => false,
                    'price' => null,
                    'additionalPrice' => null,
                    'rooms' => null
                ]
            );
    }

    public function getBlockPrefix()
    {
        return 'mbhhotel_bundle_room_type_flow';
    }
}
