<?php

namespace MBH\Bundle\HotelBundle\Form;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Form\LanguageType;
use MBH\Bundle\BaseBundle\Form\MultiLanguagesType;
use MBH\Bundle\HotelBundle\Document\Hotel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

class HotelContactInformationType extends AbstractType
{
    /** @var  DocumentManager $dm */
    private $dm;
    private $translator;

    public function __construct(DocumentManager $dm, TranslatorInterface $translator)
    {
        $this->dm = $dm;
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
                'mapped' => false,
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
            ])
        ;

        $builder
            ->add('aboutLink', TextType::class, [
                'group' => 'form.hotelExtendedType.hotel_description_links.group',
                'label' => 'form.hotelExtendedType.hotel_description_links.about_link.label',
                'required' => false,
            ])
            ->add('contactsLink', TextType::class, [
                'group' => 'form.hotelExtendedType.hotel_description_links.group',
                'label' => 'form.hotelExtendedType.hotel_description_links.contacts_link.label',
                'required' => false,
            ])
            ->add('mapLink', TextType::class, [
                'group' => 'form.hotelExtendedType.hotel_description_links.group',
                'label' => 'form.hotelExtendedType.hotel_description_links.map_link.label',
                'required' => false,
            ])
            ->add('roomsLink', TextType::class, [
                'group' => 'form.hotelExtendedType.hotel_description_links.group',
                'label' => 'form.hotelExtendedType.hotel_description_links.rooms_link.label',
                'required' => false,
            ])
            ->add('pollLink', TextType::class, [
                'group' => 'form.hotelExtendedType.hotel_description_links.group',
                'label' => 'form.hotelExtendedType.hotel_description_links.poll_link.label',
                'required' => false,
            ]);

        $builder
            ->add('latitude', TextType::class, [
                'label' => 'form.hotelExtendedType.latitude',
                'group' => 'form.hotelExtendedType.location',
                'required' => false,
                'attr' => ['placeholder' => '55.752014'],
                'help' => 'form.hotelExtendedType.gps_coordinates_latitude<br><a href="#" data-toggle="modal" data-target="#hotel_coordinates_help">form.hotelExtendedType.know_hotel_coordinates</a>'
            ])
            ->add('longitude', TextType::class, [
                'label' => 'form.hotelExtendedType.longitude',
                'group' => 'form.hotelExtendedType.location',
                'required' => false,
                'attr' => ['placeholder' => '37.617515'],
                'help' => 'form.hotelExtendedType.gps_coordinates_longitude<br><a href="#" data-toggle="modal" data-target="#hotel_coordinates_help">form.hotelExtendedType.know_hotel_coordinates</a>'
            ]);

        $builder
            ->add('contactInformation', ContactInfoType::class)
            ->add('supportedLanguages', LanguageType::class, [
                'multiple' => true,
                'label' => 'form.contact_info_type.available_languages.label',
                'group' => 'form.hotelType.general_info',
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Hotel::class,
        ]);
    }

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $view->children['contactInformation']->vars['embedded'] = true;
    }

    public function getBlockPrefix()
    {
        return 'mbhhotel_bundle_hotel_contact_information_type';
    }
}
