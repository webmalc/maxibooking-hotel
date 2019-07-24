<?php

namespace MBH\Bundle\ChannelManagerBundle\Form;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\PriceBundle\Document\TariffRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HomeAwayTariffType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Hotel $hotel */
        $hotel = $options['hotel'];
        $selectedTariff = $hotel
            ->getHomeAwayConfig()
            ->getTariffs()
            ->current();

        $builder
            ->add('tariff', DocumentType::class, [
                'label' => 'form.homeaway_tariff_type.tariff.label',
                'class' => Tariff::class,
                'query_builder' => static function(TariffRepository $repository) use ($options) {
                    return $repository->fetchQueryBuilder($options['hotel'], null, true);
                },
                'help' => 'form.homeaway_tariff_type.tariff.help',
                'data' => $selectedTariff ? $selectedTariff->getTariff() : $hotel->getBaseTariff()
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'hotel' => null,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'mbhchannel_manager_bundle_homeaway_tariff_type';
    }
}
