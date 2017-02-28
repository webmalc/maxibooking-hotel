<?php

namespace MBH\Bundle\ChannelManagerBundle\Form;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\ChannelManagerBundle\Document\TripAdvisorTariff;
use MBH\Bundle\ChannelManagerBundle\Form\TripAdvisor\TripAdvisorFeeType;
use MBH\Bundle\PriceBundle\Document\Tariff;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TripAdvisorTariffType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('tariff', DocumentType::class, [
                'class' => Tariff::class,
                'label' => 'form.trip_advisor_tariff_type.sync_tariff.label',
                'query_builder' => function(DocumentRepository $repository) use ($options) {
                    $builder = $repository->createQueryBuilder()->field('hotel.id')->equals($options['hotel']->getId());
                    return $builder;
                },
                'required' => true
            ])
            ->add('refundableType', ChoiceType::class, [
                'choices' => TripAdvisorTariff::getRefundableTypes(),
                'choice_label' => function ($value) {
                    return 'form.trip_advisor_tariff_type.refundable_type.' . $value;
                }
            ])
            ->add('deadline', DateTimeType::class, array(
                'label' => 'form.trip_advisor_tariff_type.deadline.label',
                'help' => 'form.trip_advisor_tariff_type.deadline.help',
                'html5' => false,
                'required' => false,
                'date_format' => 'dd.MM.yyyy',
                'time_widget' => 'single_text',
                'date_widget' => 'single_text',
            ))
            ->add('isPenaltyExists', CheckboxType::class, [

            ])
            ->add('policyInfo', TextareaType::class, [

            ])
            ->add('fees', CollectionType::class, [
                'entry_type' => TripAdvisorFeeType::class
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => TripAdvisorTariff::class
            ]);
    }

    public function getBlockPrefix()
    {
        return 'mbhchannel_manager_bundle_trip_advisor_tariff_type';
    }
}
