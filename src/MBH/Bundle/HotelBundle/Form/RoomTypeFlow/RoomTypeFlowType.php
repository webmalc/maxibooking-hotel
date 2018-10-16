<?php

namespace MBH\Bundle\HotelBundle\Form\RoomTypeFlow;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Service\MBHFormBuilder;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\HotelRepository;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\HotelBundle\Document\RoomTypeRepository;
use MBH\Bundle\HotelBundle\Form\RoomTypeType;
use MBH\Bundle\HotelBundle\Service\FlowManager;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\PriceBundle\Document\TariffRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

class RoomTypeFlowType extends AbstractType
{
    private $mbhFormBuilder;
    private $dm;
    private $flowManager;

    public function __construct(MBHFormBuilder $mbhFormBuilder, DocumentManager $dm, FlowManager $flowManager)
    {
        $this->mbhFormBuilder = $mbhFormBuilder;
        $this->dm = $dm;
        $this->flowManager = $flowManager;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        switch ($options['flow_step']) {
            case RoomTypeFlow::HOTEL_STEP:
                $builder
                    ->add(
                        'hotel', DocumentType::class, [
                            'label' => 'room_type_flow_type.hotel.label',
                            'required' => true,
                            'class' => Hotel::class,
                            'query_builder' => function (HotelRepository $repository) {
                                return $repository->getQBWithAvailable();
                            },
                            'data' => $options['hotel']
                        ]
                    );
                break;
            case RoomTypeFlow::ROOM_TYPE_STEP:
                $builder
                    ->add(
                        'roomType', DocumentType::class, [
                            'label' => 'room_type_flow_type.room_type.label',
                            'required' => true,
                            'class' => 'MBHHotelBundle:RoomType',
                            'query_builder' => function (RoomTypeRepository $dr) use ($options) {
                                return $dr->fetchQueryBuilder($options['hotel']);
                            },
                            'data' => $options['roomType'],
                            'expanded' => true,
                            'multiple' => false
                        ]
                    );
                break;
            case RoomTypeFlow::ROOM_DESCRIPTION_STEP:
                $this->mbhFormBuilder->mergeFormFields(
                    $builder,
                    RoomTypeType::class,
                    $builder->getData(),
                    ['roomSpace', 'facilities', 'description']
                );
                break;
            case RoomTypeFlow::NUM_OF_ROOMS_STEP:
                $this->mbhFormBuilder->mergeFormFields(
                    $builder,
                    RoomTypeType::class,
                    $builder->getData(),
                    ['places', 'additionalPlaces']
                );
                break;
            case RoomTypeFlow::ROOM_CACHES_STEP:
                $builder
                    ->add(
                        'rooms',
                        TextType::class,
                        [
                            'label' => 'mbhpricebundle.form.roomcachegeneratortype.kolichestvo.mest',
                            'required' => true,
                            'data' => is_null($options['rooms']) ? 0 : $options['rooms'],
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
            case RoomTypeFlow::PERIOD_STEP:
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
            case RoomTypeFlow::TARIFF_STEP:
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
            case RoomTypeFlow::PRICE_STEP:
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

    /**
     * @param FormView $view
     * @param FormInterface $form
     * @param array $options
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        if ($options['flow_step'] === RoomTypeFlow::ROOM_TYPE_STEP) {
            $roomTypes = $this->dm->getRepository(RoomType::class)->fetch($options['hotel']);
            $roomTypeIds = array_map(function (RoomType $roomType) {
                return $roomType->getId();
            }, $roomTypes->toArray());
            $progressRates = $this->flowManager->getProgressRateByFlowId(RoomTypeFlow::FLOW_TYPE, array_values($roomTypeIds));
            $view->children['roomType']->vars['flowProgressRates'] = $progressRates;
            $view->children['roomType']->vars['selectedRoomTypeId'] = $options['roomType'] ? $options['roomType']->getId() : null;
        }
    }

    public function getBlockPrefix()
    {
        return 'mbhhotel_bundle_room_type_flow';
    }
}
