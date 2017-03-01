<?php

namespace MBH\Bundle\ChannelManagerBundle\Form\TripAdvisor;

use MBH\Bundle\BaseBundle\Service\Currency;
use MBH\Bundle\ChannelManagerBundle\Model\TripAdvisor\TripAdvisorFee;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TripAdvisorFeeType extends AbstractType
{
    private $currencyService;

    public function __construct(Currency $currency)
    {
        $this->currencyService = $currency;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('amountType', ChoiceType::class, [
                'choices' => TripAdvisorFee::getFeeAmountTypes(),
                'choice_label' => function($value) {
                    return 'form.trip_advisor_fee_type.amount_type.' . $value;
                },
                'label' => 'form.trip_advisor_fee_type.amount_type.label',
                'group' => false
            ])
            ->add('currency', ChoiceType::class, [
                'choices' => $this->currencyService->codes(),
                'label' => 'form.trip_advisor_fee_type.currency.label',
                'required' => false,
                'group' => false
            ])
            ->add('daysBeforeArrival', NumberType::class, [
                'label' => 'form.trip_advisor_fee_type.days_before_arrival.label',
                'attr' => [
                    'class' => 'days-spinner'
                ],
                'group' => false
            ])
            ->add('amount', NumberType::class, [
                'label' => 'form.trip_advisor_fee_type.price.label',
                'required' => true,
                'attr' => [
                    'class' => 'price-spinner',
                ],
                'group' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => TripAdvisorFee::class
            ]);
    }

    public function getBlockPrefix()
    {
        return 'mbhchannel_manager_bundle_trip_advisor_fee_type';
    }
}
