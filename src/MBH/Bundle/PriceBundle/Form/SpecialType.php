<?php

namespace MBH\Bundle\PriceBundle\Form;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\BaseBundle\Service\HotelSelector;
use MBH\Bundle\HotelBundle\Document\Room;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PackageBundle\Services\Search\SearchFactory;
use MBH\Bundle\PriceBundle\Document\Promotion;
use MBH\Bundle\PriceBundle\Document\Special;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Regex;

class SpecialType extends AbstractType
{
    /**
     * @var HotelSelector
     */
    private $hotelSelector;
    /**
     * @var SearchFactory
     */
    private $search;

    public function __construct(HotelSelector $hotelSelector, SearchFactory $search)
    {
        $this->hotelSelector = $hotelSelector;
        $this->search = $search;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fullTitle', TextType::class, [
                'label' => 'form.fullTitle',
                'group' => 'form.info.group',
                'required' => true,
                'attr' => ['placeholder' => 'special.fullTitle.placeholder']
            ])
            ->add('title', TextType::class, [
                'label' => 'form.title',
                'group' => 'form.info.group',
                'required' => false,
                'attr' => ['placeholder' => 'special.title.placeholder'],
                'help' => 'form.title.help'
            ])
            ->add('description', TextareaType::class, [
                'label' => 'form.description',
                'group' => 'form.info.group',
                'required' => false,
                'help' => 'special.description.help'
            ])
            ->add('begin', DateType::class, [
                    'label' => 'form.begin',
                    'group' => 'special.group.dates',
                    'widget' => 'single_text',
                    'format' => 'dd.MM.yyyy',
                    'help' => 'special.begin.help',
                    'attr' => [
                        'class' => 'datepicker begin-datepicker input-small',
                        'data-date-format' => 'dd.mm.yyyy',
                    ],
                ]
            )
            ->add(
                'end', DateType::class, [
                    'label' => 'form.end',
                    'group' => 'special.group.dates',
                    'widget' => 'single_text',
                    'format' => 'dd.MM.yyyy',
                    'help' => 'special.end.help',
                    'attr' => [
                        'class' => 'datepicker end-datepicker input-small',
                        'data-date-format' => 'dd.mm.yyyy',
                    ],
                ]
            )
            ->add('promotion', DocumentType::class, [
                'class' => Promotion::class,
                'group' => 'form.group.discount',
                'required' => false,
                'empty_data' => '',
                'label' => 'Акция',
                'help' => 'При наличии акции значение скидки берется из нее, иначе из поля ниже.',
            ])
            ->add('discount', NumberType::class, [
                'label' => 'special.discount',
                'group' => 'form.group.discount',
                'attr' => ['class' => 'spinner-0'],
                'help' => 'special.discount.help',
            ])
            ->add('isPercent', CheckboxType::class, [
                'label' => 'special.isPercent',
                'group' => 'form.group.discount',
                'value' => true,
                'required' => false,
            ])
            ->add('displayFrom', DateType::class, [
                    'label' => 'special.displayFrom',
                    'group' => 'special.group.conditions',
                    'widget' => 'single_text',
                    'format' => 'dd.MM.yyyy',
                    'help' => 'special.displayFrom.help',
                    'attr' => [
                        'class' => 'datepicker begin-datepicker input-small',
                        'data-date-format' => 'dd.mm.yyyy',
                    ],
                ]
            )
            ->add('displayTo', DateType::class, [
                    'label' => 'special.displayTo',
                    'group' => 'special.group.conditions',
                    'widget' => 'single_text',
                    'format' => 'dd.MM.yyyy',
                    'help' => 'special.displayTo.help',
                    'attr' => [
                        'class' => 'datepicker begin-datepicker input-small',
                        'data-date-format' => 'dd.mm.yyyy',
                    ],
                ]
            )
            ->add('tariffs', DocumentType::class, [
                'label' => 'special.tariffs',
                'group' => 'special.group.conditions',
                'class' => 'MBH\Bundle\PriceBundle\Document\Tariff',
                'multiple' => true,
                'required' => false,
                'query_builder' => function (DocumentRepository $er) {
                    return $er->createQueryBuilder()
                        ->field('hotel')->references($this->hotelSelector->getSelected())
                        ->sort('fullTitle', 'asc');
                },
                'help' => 'special.tariffs.help',
            ])
            ->add('roomTypes', DocumentType::class, [
                'label' => 'special.roomTypes',
                'group' => 'special.group.conditions',
                'class' => 'MBH\Bundle\HotelBundle\Document\RoomType',
                'multiple' => true,
                'group_by' => 'category',
                'required' => false,
                'query_builder' => function (DocumentRepository $er) {
                    return $er->createQueryBuilder()
                        ->field('hotel')
                        ->references($this->hotelSelector->getSelected())
                        ->sort('fullTitle', 'asc');
                },
                'help' => 'special.roomTypes.help',
            ])
            ->add('virtualRoom', DocumentType::class, [
                'label' => 'special.virtualRoom',
                'group' => 'special.group.conditions',
                'class' => 'MBH\Bundle\HotelBundle\Document\Room',
                'multiple' => false,
                'required' => false,
                'query_builder' => function (DocumentRepository $er) {
                    return $er->createQueryBuilder()
                        ->field('hotel')
                        ->references($this->hotelSelector->getSelected());
                },
                'help' => 'special.virtualRoom.help'
            ])
            ->add('limit', NumberType::class, [
                'label' => 'special.limit',
                'help' => 'special.limit.help',
                'group' => 'special.group.conditions',
                'attr' => ['class' => 'spinner-1']
            ])
            ->add('isEnabled', CheckboxType::class, [
                'label' => 'isEnabled',
                'group' => 'form.group.config',
                'value' => true,
                'required' => false,
            ]);

        $defaultPriceModifier = function (FormInterface $form, Room $virtualRoom = null) {
            $choices = [];
            /** @var RoomType $roomType */
            if ($virtualRoom) {
                $roomType = $virtualRoom->getRoomType();
                $capacity = $roomType->getTotalPlaces();
                $choices = [];
                foreach (range(1, $capacity) as $adultPlace) {
                    foreach (range(0, $capacity - $adultPlace) as $childPlace) {
                        $choices[] = $adultPlace . '_' . ($childPlace);
                    }

                }
            }

            $form->add('defaultPrice', ChoiceType::class, [
                'label' => 'special.defaultPrice',
                'required' => false,
                'attr' => [
                    'class' => 'plain-html'
                ],
                'group' => 'special.group.conditions',
                'help' => 'special.defaultPrice.help',
                'choices' => array_combine($choices, $choices),
                'constraints' => [
                    new Regex(['pattern' => "/^\d_\d$/", 'message' => 'Ошибка цены по-умолчанию. Пример: 3_0 - трое взрослых, нуль детей.']),

                ]
            ]);
        };
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($defaultPriceModifier) {
            $virtualRoom = $event->getData()->getVirtualRoom();
            $defaultPriceModifier($event->getForm(), $virtualRoom);

        });
        $builder->get('virtualRoom')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($defaultPriceModifier) {
                $virtualRoom = $event->getForm()->getData();
                $defaultPriceModifier($event->getForm()->getParent(), $virtualRoom);
            }

        );
//
//        $builder->get('virtualRoom')->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
//            $event->stopPropagation();
//        }, 900);
    }



    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MBH\Bundle\PriceBundle\Document\Special',
        ));
    }

    public function getBlockPrefix()
    {
        return 'mbh_bundle_pricebundle_special_type';
    }

}
