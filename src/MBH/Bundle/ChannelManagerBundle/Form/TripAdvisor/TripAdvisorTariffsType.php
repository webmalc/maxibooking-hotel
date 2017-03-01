<?php

namespace MBH\Bundle\ChannelManagerBundle\Form\TripAdvisor;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\ChannelManagerBundle\Document\TripAdvisorConfig;
use MBH\Bundle\PriceBundle\Document\Tariff;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TripAdvisorTariffsType extends AbstractType
{
    /** @var  DocumentManager $dm */
    private $dm;

    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('tariffs', CollectionType::class, [
                'entry_type' => TripAdvisorTariffType::class,
                'allow_add' => true,
                'entry_options' => [
                    'hotel' => $options['hotel']
                ],
                'group' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => TripAdvisorConfig::class,
                'hotel' => null
            ]);
    }

    public function getBlockPrefix()
    {
        return 'mbhchannel_manager_bundle_trip_advisor_tariffs_type';
    }
}
