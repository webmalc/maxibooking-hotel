<?php

namespace MBH\Bundle\ChannelManagerBundle\Form;

use MBH\Bundle\ChannelManagerBundle\Document\HomeAwayRoom;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HomeAwayRoomTypeForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var HomeAwayRoom $homeAwayRoom */
        $homeAwayRoom = $options['roomTypes'][$builder->getName()];
        $roomType = $homeAwayRoom->getRoomType();

        $builder
            ->add('isEnabled', CheckboxType::class, [
                'group' => $roomType->getName(),
                'label' => 'form.home_away_room_type.is_enabled.label',
                'help' => 'form.home_away_room_type.is_enabled.help',
                'required' => false
            ])
            ->add('rentalAgreement', TextareaType::class, [
                'group' => $roomType->getName(),
                'label' => 'form.home_away_room_type.rental_agreement.label',
                'help' => 'form.home_away_room_type.rental_agreement.help',
                'required' => false
            ])
            ->add('headLine', TextType::class, [
                'group' => $roomType->getName(),
                'label' => 'form.home_away_room_type.headline.label',
                'required' => false
            ])
            ->add('listingType', ChoiceType::class, [
                'choices' => HomeAwayRoom::getListingTypes(),
                'group' => $roomType->getName(),
                'choice_label' => function($value) {
                    return $value . '.translation';
                },
                'required' => false,
                'label' => 'form.home_away_room_type.listing_type.label'
            ])
            ->add('bedRoomSubType', ChoiceType::class, [
                'choices' => HomeAwayRoom::getBedRoomSubTypes(),
                'group' => $roomType->getName(),
                'choice_label' => function($value) {
                    return $value . '.translation';
                },
                'required' => false,
                'label' => 'form.home_away_room_type.bed_room_sub_type.label'
            ])
            ->add('bathSubType', ChoiceType::class, [
                'choices' => HomeAwayRoom::getRoomBathSubTypes(),
                'group' => $roomType->getName(),
                'choice_label' => function($value) {
                    return $value . '.translation';
                },
                'required' => false,
                'label' =>'form.home_away_room_type.bath_room_sub_type.label'
            ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'roomTypes' => null,
                'data_class' => HomeAwayRoom::class
            ]);
    }

    public function getBlockPrefix()
    {
        return 'mbhchannel_manager_bundle_trip_advisor_room_type';
    }
}
