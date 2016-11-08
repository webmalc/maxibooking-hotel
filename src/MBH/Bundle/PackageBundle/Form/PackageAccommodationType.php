<?php

namespace MBH\Bundle\PackageBundle\Form;

use MBH\Bundle\HotelBundle\Document\Room;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class PackageAccommodationType

 */
class PackageAccommodationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('purposeOfArrival', ChoiceType::class, [
                'label' => 'form.packageMainType.arrival_purpose',
                'required' => false,
                'group' => 'form.packageAccommodationType.choose_placement',
                'multiple' => false,
                'choices' => $options['arrivals'],
            ])
            ->add('isCheckIn', CheckboxType::class, [
                'label' => 'form.packageAccommodationType.are_guests_checked_in',
                'value' => true,
                'group' => 'form.packageAccommodationType.check_in_group',
                'required' => false,
                'help' => 'form.packageAccommodationType.are_guests_checked_in_help'
            ])
        ;

        $arrivalTimeOptions = [
            'label' => 'form.packageMainType.check_in_time',
            'html5' => false,
            'group' => 'form.packageAccommodationType.check_in_group',
            'required' => false,
            'time_widget' => 'single_text',
            'date_widget' => 'single_text',
            'attr' => ['placeholder' => '12:00', 'class' => 'input-time'],
            'date_format' => 'dd.MM.yyyy',
        ];

        if ($options['hasEarlyCheckIn']) {
           $arrivalTimeOptions['help'] = 'form.packageAccommodationType.hasEarlyCheckIn';
        }

        $builder->add('arrivalTime', DateTimeType::class, $arrivalTimeOptions);

        $isCheckOutOptions = [
            'label' => 'form.packageAccommodationType.are_guests_checked_out',
            'value' => true,
            'group' => 'form.packageAccommodationType.check_in_group',
            'required' => false,
            'help' => 'form.packageAccommodationType.are_guests_checked_out_help'
        ];
        if ($options['debt']) {
            //$isCheckOutOptions['attr'] = ['disabled' => 'disabled'];
            $isCheckOutOptions['help'] = 'form.packageAccommodationType.can_not_checkout_with_debt';
            /*$isCheckOutOptions['constraints'] = [
                new EqualTo(['value' => false])
            ];*/
        }
        $builder->add('isCheckOut', CheckboxType::class, $isCheckOutOptions);

        $departureTimeOptions = [
            'label' => 'form.packageMainType.check_out_time',
            'html5' => false,
            'group' => 'form.packageAccommodationType.check_in_group',
            'required' => false,
            'time_widget' => 'single_text',
            'date_widget' => 'single_text',
            'attr' => ['placeholder' => '12:00', 'class' => 'input-time'],
            'date_format' => 'dd.MM.yyyy',
        ];

        if ($options['hasLateCheckOut']) {
            $departureTimeOptions['help'] = 'form.packageAccommodationType.hasLateCheckOut';
        }

        $builder->add('departureTime', DateTimeType::class, $departureTimeOptions);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'MBH\Bundle\PackageBundle\Document\Package',
            'optGroupRooms' => [],
            'arrivals' => [],
            'roomType' => null,
            'debt' => false,
            'roomStatusIcons' => [],
            'hasEarlyCheckIn' => false,
            'hasLateCheckOut' => false,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'mbh_bundle_packagebundle_package_accommodation_type';
    }

}
