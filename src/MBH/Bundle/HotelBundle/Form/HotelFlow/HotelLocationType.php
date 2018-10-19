<?php

namespace MBH\Bundle\HotelBundle\Form\HotelFlow;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class HotelLocationType extends AbstractType
{
    const MAP_URL = 'https://www.google.com/maps/';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
//            ->add('mapUrl', TextType::class, [
//                'label' => 'form.hotel_contact_information_type.map_url',
//                'required' => false,
//                'group' => 'form.hotelExtendedType.location',
//                'help' => '<a target="_blank" href="' . self::MAP_URL . '">' . self::MAP_URL . '</a>',
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
    }

    public function checkMapUrl($mapUrl, ExecutionContextInterface $context)
    {
        //TODO: После мержа с airbnb-веткой, заменить на вызов из сервиса Utils::startsWith
        $isGoogleMapUrl = substr($mapUrl, 0, strlen(self::MAP_URL)) === self::MAP_URL;
        if (!$isGoogleMapUrl) {
            $context->addViolation('validator.hotel.map_url');
        }
    }

    public function getBlockPrefix()
    {
        return 'mbhhotel_bundle_hotel_location_type';
    }
}
