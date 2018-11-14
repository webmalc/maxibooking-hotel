<?php

namespace MBH\Bundle\PriceBundle\Form;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\BaseBundle\Service\HotelSelector;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SpecialType extends AbstractType
{
    /**
     * @var HotelSelector
     */
    private $hotelSelector;

    public function __construct(HotelSelector $hotelSelector)
    {
        $this->hotelSelector = $hotelSelector;
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
                        'class' => 'datepicker end-datepicker input-small',
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
                'query_builder' => function(DocumentRepository $er) {
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
                'query_builder' => function(DocumentRepository $er) {
                    return $er->createQueryBuilder()
                        ->field('hotel')
                        ->references($this->hotelSelector->getSelected())
                        ->sort('fullTitle', 'asc');
                },
                'help' => 'special.roomTypes.help',
            ])
            ->add('limit', NumberType::class, [
                'label' => 'special.limit',
                'help' => 'special.limit.help',
                'group' => 'special.group.conditions',
                'attr' => ['class' => 'spinner-1'],
            ])
            ->add('isEnabled', CheckboxType::class, [
                'label' => 'isEnabled',
                'group' => 'form.group.config',
                'value' => true,
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MBH\Bundle\PriceBundle\Document\Special'
        ));
    }

    public function getBlockPrefix()
    {
        return 'mbh_bundle_pricebundle_special_type';
    }

}
