<?php

namespace MBH\Bundle\HotelBundle\Form\HotelFlow;

use MBH\Bundle\BaseBundle\Service\MBHFormBuilder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatorInterface;

class HotelAddressType extends AbstractType
{
    private $translator;
    private $formBuilder;

    public function __construct(TranslatorInterface $translator, MBHFormBuilder $formBuilder) {
        $this->translator = $translator;
        $this->formBuilder = $formBuilder;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $cityHelp = $this->translator->trans('form.organization_type.city.help',
            ['%plusButtonHtml%' => '<a class="add-billing-entity-button" data-entity-type="cities"><i class="fa fa-plus"></i></a>']);

        $builder
            ->add('cityId', TextType::class, [
                'label' => 'form.hotelExtendedType.city',
                'group' => 'form.hotelExtendedType.address',
                'required' => true,
                'attr' => [
                    'class' => 'citySelect  billing-city',
                    'placeholder' => 'form.hotelExtendedType.city',
                    'required' => true
                ],
                'help' => $cityHelp
            ]);

        $this->formBuilder->addMultiLangField($builder, TextType::class, 'settlement', [
            'group' => 'form.hotelExtendedType.address',
            'label' => 'form.hotelExtendedType.settlement',
            'required' => false,
        ]);

        $builder
            ->add('zipCode', TextType::class, [
                'label' => 'form.hotelExtendedType.zip_code',
                'group' => 'form.hotelExtendedType.address',
                'required' => false,
            ]);

        $this->formBuilder->addMultiLangField($builder, TextType::class, 'street', [
            'group' => 'form.hotelExtendedType.address',
            'label' => 'form.hotelExtendedType.street',
            'required' => false,
        ]);

        $builder
            ->add('house', TextType::class, [
                'label' => 'form.hotelExtendedType.house',
                'group' => 'form.hotelExtendedType.address',
                'required' => false
            ])
            ->add('corpus', TextType::class, [
                'label' => 'form.hotelExtendedType.corpus',
                'group' => 'form.hotelExtendedType.address',
                'required' => false
            ])
            ->add('flat', TextType::class, [
                'label' => 'form.hotelExtendedType.flat',
                'group' => 'form.hotelExtendedType.address',
                'required' => false,
            ]);

        return $builder;
    }
}