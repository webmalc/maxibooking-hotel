<?php

namespace MBH\Bundle\ChannelManagerBundle\Form\TripAdvisor;

use MBH\Bundle\ChannelManagerBundle\Document\TripAdvisorConfig;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TripAdvisorTariffsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('tariffs', CollectionType::class, [
                'entry_type' => TripAdvisorTariffType::class,
                'entry_options' => [
                    'hotel' => $options['hotel'],
                    'tariffs' => $builder->getData()->getTariffs(),
                    'unfilledFieldErrors' => $options['unfilledFieldErrors']
                ],
                'group' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => TripAdvisorConfig::class,
                'hotel' => null,
                'group' => null,
                'unfilledFieldErrors' => null
            ]);
    }

    public function getBlockPrefix()
    {
        return 'mbhchannel_manager_bundle_trip_advisor_tariffs_type';
    }
}
