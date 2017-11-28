<?php

namespace MBH\Bundle\HotelBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HotelType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fullTitle', TextType::class, [
                'label' => 'form.hotelType.name',
                'group' => 'form.hotelType.general_info',
                'required' => true,
                'attr' => ['placeholder' => 'form.hotelType.placeholder_my_hotel']
            ])
            ->add('title', TextType::class, [
                'label' => 'form.hotelType.inner_name',
                'group' => 'form.hotelType.general_info',
                'required' => false,
                'attr' => ['placeholder' => 'form.hotelType.placeholder_hotel'],
                'help' => 'form.hotelType.maxibooking_inner_name'
            ])
            ->add('internationalTitle', TextType::class, [
                'label' => 'form.hotelType.international_title',
                'group' => 'form.hotelType.general_info',
                'required' => false
            ])
            ->add('prefix', TextType::class, [
                'label' => 'form.hotelType.prefix',
                'group' => 'form.hotelType.general_info',
                'required' => false,
                'attr' => ['placeholder' => 'HTL'],
                'help' => 'form.hotelType.document_use_name'
            ])
            ->add('description', TextareaType::class, [
                'label' => 'form.hotelType.description',
                'group' => 'form.hotelType.general_info',
                'attr' => ['class' => 'tinymce'],
                'required' => false
            ])
            ->add('logoImage', HotelLogoImageType::class, [
                'label' => 'form.hotel_logo.image_file.help',
                'group' => 'form.hotelType.settings',
                'required' => false,
                'logo_image_delete_url' => $options['logo_image_delete_url']

            ])
            ->add('isHostel', CheckboxType::class, [
                'label' => 'form.hotelType.hostel',
                'group' => 'form.hotelType.settings',
                'value' => true,
                'required' => false,
                'help' => 'form.hotelType.hostel_hotel_or_not'
            ])
            ->add('isDefault', CheckboxType::class, [
                'label' => 'form.hotelType.is_default',
                'group' => 'form.hotelType.settings',
                'value' => true,
                'required' => false,
                'help' => 'form.hotelType.is_default_maxibooking'
            ])
            ->add('packageArrivalTime', ChoiceType::class, [
                'label' => 'form.hotelType.package_arrival_time.label',
                'group' => 'form.hotelType.settings',
                'required' => false,
                'choices' => range(0, 23)
            ])
            ->add('packageDepartureTime', ChoiceType::class, [
                'label' => 'form.hotelType.package_departure_time.label',
                'group' => 'form.hotelType.settings',
                'required' => false,
                'choices' => range(0, 23)
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'MBH\Bundle\HotelBundle\Document\Hotel',
            'types' => [],
            'imageUrl' => null,
            'logo_image_delete_url' => null
        ]);
    }

    public function getBlockPrefix()
    {
        return 'mbh_bundle_hotelbundle_hoteltype';
    }

}
