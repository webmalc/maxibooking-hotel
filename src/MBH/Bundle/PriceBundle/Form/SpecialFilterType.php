<?php

namespace MBH\Bundle\PriceBundle\Form;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\BaseBundle\Service\HotelSelector;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SpecialFilterType extends AbstractType
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
            ->add('begin', DateType::class, [
                    'label' => 'form.begin',
                    'group' => 'special.group.dates',
                    'widget' => 'single_text',
                    'format' => 'dd.MM.yyyy',
                    'required' => false,
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
                    'required' => false,
                    'help' => 'special.end.help',
                    'attr' => [
                        'class' => 'datepicker end-datepicker input-small',
                        'data-date-format' => 'dd.mm.yyyy',
                    ],
                ]
            )
            ->add('tariff', DocumentType::class, [
                'label' => 'special.tariffs',
                'group' => 'special.group.conditions',
                'class' => 'MBH\Bundle\PriceBundle\Document\Tariff',
                'required' => false,
                'query_builder' => function(DocumentRepository $er) {
                    return $er->createQueryBuilder()
                        ->field('isEnabled')->equals(true)
                        ->field('hotel')->references($this->hotelSelector->getSelected())
                        ->sort('fullTitle', 'asc');
                },
                'help' => 'special.tariffs.help',
            ])
            ->add('roomType', DocumentType::class, [
                'label' => 'special.roomTypes',
                'group' => 'special.group.conditions',
                'class' => 'MBH\Bundle\HotelBundle\Document\RoomType',
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
            ->add('isEnabled', CheckboxType::class, [
                'label' => 'isEnabled',
                'group' => 'form.group.config',
                'value' => true,
                'required' => false,
            ])
            ->add('isStrict', CheckboxType::class, [
                'label' => 'isStrict',
                'group' => 'form.group.config',
                'value' => true,
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MBH\Bundle\PriceBundle\Lib\SpecialFilter'
        ));
    }

    public function getBlockPrefix()
    {
        return '';
    }

}
