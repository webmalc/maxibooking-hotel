<?php

namespace MBH\Bundle\HotelBundle\Form;

use MBH\Bundle\BaseBundle\Form\LanguageType;
use MBH\Bundle\BaseBundle\Service\MBHFormBuilder;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Form\HotelFlow\HotelAddressType;
use MBH\Bundle\HotelBundle\Form\HotelFlow\HotelLocationType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HotelContactInformationType extends AbstractType
{
    private $mbhFormBuilder;

    public function __construct(MBHFormBuilder $formBuilder)
    {
        $this->mbhFormBuilder = $formBuilder;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->mbhFormBuilder->addMergedFormFields($builder, HotelAddressType::class, $builder->getData());

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

        $this->mbhFormBuilder->addMergedFormFields($builder, HotelLocationType::class, $builder->getData());

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
