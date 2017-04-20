<?php

namespace MBH\Bundle\ChannelManagerBundle\Form\TripAdvisor;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\ChannelManagerBundle\Document\TripAdvisorTariff;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TripAdvisorTariffType extends AbstractType
{
    /** @var  DocumentManager $dm */
    private $dm;

    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var TripAdvisorTariff $tripAdvisorTariff */
        $tripAdvisorTariff = $options['tariffs'][$builder->getName()];
        $tariff = $tripAdvisorTariff->getTariff();

        $builder
            ->add('isEnabled', CheckboxType::class, [
                'label' => 'form.trip_advisor_tariff_type.isSynchronized.label',
                'help' => 'form.trip_advisor_tariff_type.isSynchronized.help',
                'required' => false,
                'group' => $tariff->getName(),
                'disabled' => !empty($options['unfilledFieldErrors'][$builder->getName()])
            ])
            ->add('refundableType', ChoiceType::class, [
                'choices' => TripAdvisorTariff::getRefundableTypes(),
                'choice_label' => function ($value) {
                    return 'form.trip_advisor_tariff_type.refundable_type.' . $value;
                },
                'label' => 'form.trip_advisor_tariff_type.refundable_type.label',
                'help' => 'form.trip_advisor_tariff_type.refundable_type.help',
                'empty_data'  => null,
                'group' => $tariff->getName()
            ])
            ->add('deadline', NumberType::class, [
                'label' => 'form.trip_advisor_tariff_type.deadline.label',
                'help' => 'form.trip_advisor_tariff_type.deadline.help',
                'attr' => [
                    'class' => 'days-spinner',
                ],
                'group' => $tariff->getName()
            ])
            ->add('isPenaltyExists', CheckboxType::class, [
                'label' => 'form.trip_advisor_tariff_type.is_penalty_exists.label',
                'help' => 'form.trip_advisor_tariff_type.is_penalty_exists.help',
                'group' => $tariff->getName(),
                'required' => false
            ])
            ->add('policyInfo', TextareaType::class, [
                'label' => 'form.trip_advisor_tariff_type.policy_info.label',
                'help' => 'form.trip_advisor_tariff_type.policy_info.help',
                'group' => $tariff->getName(),
                'required' => false
            ])
//            ->add('fees', CollectionType::class, [
//                'entry_type' => TripAdvisorFeeType::class,
//                'group' => 'Комиссии',
////                'label' => 'form.trip_advisor_tariff_type.fees.label',
//                'required' => false
//            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => TripAdvisorTariff::class,
                'hotel' => null,
                'tariffs' => null,
                'unfilledFieldErrors' => null
            ]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['unfilledFields'] = $options['unfilledFieldErrors'][$view->vars['name']];
    }

    public function getBlockPrefix()
    {
        return 'mbhchannel_manager_bundle_trip_advisor_tariff_type';
    }
}
