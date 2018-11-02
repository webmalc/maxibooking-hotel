<?php

namespace MBH\Bundle\HotelBundle\Form;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Form\FormWithMultiLangFields;
use MBH\Bundle\BaseBundle\Form\LanguageType;
use MBH\Bundle\BaseBundle\Form\MultiLanguagesType;
use MBH\Bundle\ClientBundle\Service\ClientConfigManager;
use MBH\Bundle\HotelBundle\Document\Hotel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class HotelContactInformationType extends FormWithMultiLangFields
{
    const MAP_URL_BEGIN = 'https://www.google.com/maps/';
    /** @var  DocumentManager $dm */
    private $dm;
    private $translator;

    public function __construct(ClientConfigManager $clientConfigManager, DocumentManager $dm, TranslatorInterface $translator)
    {
        parent::__construct($clientConfigManager);
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
                'attr' => [
                    'class' => 'citySelect  billing-city',
                    'placeholder' => 'form.hotelExtendedType.city',
                ],
                'help' => $cityHelp
            ]);
        $builder = $this->addMultiLangField($builder, TextType::class, 'settlement', [
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

        $builder = $this->addMultiLangField($builder, TextType::class, 'street', [
            'group' => 'form.hotelExtendedType.address',
            'label' => 'form.hotelExtendedType.street',
            'required' => false,
        ]);
        $builder
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
//            ->add('mapUrl', TextType::class, [
//                'label' => 'form.hotel_contact_information_type.map_url',
//                'required' => false,
//                'group' => 'form.hotelExtendedType.location',
//                'help' => '<a target="_blank" href="https://www.google.ru/maps">https://www.google.ru/maps</a>',
//                'constraints' => [new Callback([$this, 'checkMapUrl'])],
//            ])
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
            'data_class' => Hotel::class
        ]);
    }

    public function checkMapUrl($mapUrl, ExecutionContextInterface $context)
    {
        $isGoogleMapUrl = substr($mapUrl, 0, strlen(self::MAP_URL_BEGIN)) === self::MAP_URL_BEGIN;
        if (!$isGoogleMapUrl) {
            $context->addViolation('validator.hotel.map_url');
        }
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
