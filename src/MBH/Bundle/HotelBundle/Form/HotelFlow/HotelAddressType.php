<?php

namespace MBH\Bundle\HotelBundle\Form\HotelFlow;

use MBH\Bundle\BaseBundle\Form\MultiLanguagesType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatorInterface;

class HotelAddressType extends AbstractType
{
    private $translator;

    public function __construct(TranslatorInterface $translator) {
        $this->translator = $translator;
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
                ],
                'help' => $cityHelp
            ])
            ->add('settlement', MultiLanguagesType::class, [
                'group' => 'form.hotelExtendedType.address',
                'data' => $builder->getData(),
                'fields_options' => [
                    'label' => 'form.hotelExtendedType.settlement',
                    'required' => false,
                ],
                'field_type' => TextType::class,
            ])
            ->add('zipCode', TextType::class, [
                'label' => 'form.hotelExtendedType.zip_code',
                'group' => 'form.hotelExtendedType.address',
                'required' => false,
            ])
            ->add('street', MultiLanguagesType::class, [
                'group' => 'form.hotelExtendedType.address',
                'data' => $builder->getData(),
                'fields_options' => [
                    'label' => 'form.hotelExtendedType.street',
                    'required' => false,
                ],
                'field_type' => TextType::class,
            ])
            ->add('internationalStreetName', TextType::class, [
                'group' => 'form.hotelExtendedType.address',
                'label' => 'form.hotelExtendedType.international_street_name.label',
                'help' => 'form.hotelExtendedType.international_street_name.help',
                'required' => false
            ])
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