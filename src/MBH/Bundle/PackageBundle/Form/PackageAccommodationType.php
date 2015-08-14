<?php

namespace MBH\Bundle\PackageBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class PackageAccommodationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $optGroupRooms = $options['optGroupRooms'];

        if ($options['roomType']) {
            $name = $options['roomType']->getName();
            uksort($optGroupRooms, function ($a, $b) use ($name) {
                if ($a == $name) {
                    return -1;
                }

                return 1;
            });
        }

        $builder
            ->add('accommodation', 'document', [
                'label' =>  'form.packageAccommodationType.room',
                'required' => true,
                'empty_value' => '',
                'class' => 'MBHHotelBundle:Room',
                'group' => 'form.packageAccommodationType.choose_placement',
                'choices' => $optGroupRooms,
                'property' => 'name',
                'constraints' => new NotBlank()
            ])
            ->add('purposeOfArrival', 'choice', [
                'label' => 'form.packageMainType.arrival_purpose',
                'required' => false,
                'group' => 'form.packageAccommodationType.choose_placement',
                'multiple' => false,
                'choices' => $options['arrivals'],
                ])
            ->add('isCheckIn', 'checkbox', [
                'label' => 'form.packageAccommodationType.are_guests_checked_in',
                'value' => true,
                'group' => 'form.packageAccommodationType.check_in_group',
                'required' => false,
                'help' => 'form.packageAccommodationType.are_guests_checked_in_help'
            ])
            ->add('arrivalTime', 'datetime', array(
                'label' => 'form.packageMainType.check_in_time',
                'html5' => false,
                'group' => 'form.packageAccommodationType.check_in_group',
                'required' => false,
                'time_widget' => 'single_text',
                'date_widget' => 'single_text',
                'attr' => array('placeholder' => '12:00', 'class' => 'input-time'),
            ))
            ->add('isCheckOut', 'checkbox', [
                'label' => 'form.packageAccommodationType.are_guests_checked_out',
                'value' => true,
                'group' => 'form.packageAccommodationType.check_in_group',
                'required' => false,
                'help' => 'form.packageAccommodationType.are_guests_checked_out_help'
            ])
            ->add('departureTime', 'datetime', array(
                'label' => 'form.packageMainType.check_out_time',
                'html5' => false,
                'group' => 'form.packageAccommodationType.check_in_group',
                'required' => false,
                'time_widget' => 'single_text',
                'date_widget' => 'single_text',
                'attr' => array('placeholder' => '12:00', 'class' => 'input-time'),
            ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'MBH\Bundle\PackageBundle\Document\Package',
            'optGroupRooms' => [],
            'arrivals' => [],
            'roomType' => null
        ]);
    }

    public function getName()
    {
        return 'mbh_bundle_packagebundle_package_accommodation_type';
    }

}
