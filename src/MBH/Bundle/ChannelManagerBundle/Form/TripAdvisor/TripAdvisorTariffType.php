<?php

namespace MBH\Bundle\ChannelManagerBundle\Form\TripAdvisor;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\ChannelManagerBundle\Document\TripAdvisorTariff;
use MBH\Bundle\PriceBundle\Document\Tariff;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TripAdvisorTariffType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
//            ->add('tariff', DocumentType::class, [
//                'class' => Tariff::class,
//                'label' => 'form.trip_advisor_tariff_type.sync_tariff.label',
//                'query_builder' => function(DocumentRepository $repository) use ($options) {
//                    $builder = $repository->createQueryBuilder()->field('hotel.id')->equals($options['hotel']->getId());
//                    return $builder;
//                },
//                'required' => true,
//                'group' => false
//            ])
            ->add('refundableType', ChoiceType::class, [
                'choices' => TripAdvisorTariff::getRefundableTypes(),
                'choice_label' => function ($value) {
                    return 'form.trip_advisor_tariff_type.refundable_type.' . $value;
                },
                'label' => 'form.trip_advisor_tariff_type.refundable_type.label',
                'help' => 'form.trip_advisor_tariff_type.refundable_type.help',

            ])
//            ->add('deadline', DateTimeType::class, array(
//                'label' => 'form.trip_advisor_tariff_type.deadline.label',
//                'help' => 'form.trip_advisor_tariff_type.deadline.help',
//                'html5' => false,
//                'required' => false,
//                'date_format' => 'dd.MM.yyyy',
//                'time_widget' => 'single_text',
//                'date_widget' => 'single_text',
//                'attr' => [
//                    'data-is-date-time' => true,
//                ],
//                'group' => false
//            ))
//            ->add('isPenaltyExists', CheckboxType::class, [
//                'label' => 'form.trip_advisor_tariff_type.is_penalty_exists.label',
//                'help' => 'form.trip_advisor_tariff_type.is_penalty_exists.help',
//                'group' => false
//            ])
//            ->add('policyInfo', TextareaType::class, [
//                'label' => 'form.trip_advisor_tariff_type.policy_info.label',
//                'help' => 'form.trip_advisor_tariff_type.policy_info.help',
//                'group' => false
//            ])
//            ->add('fees', CollectionType::class, [
//                'entry_type' => TripAdvisorFeeType::class,
//                'group' => 'Комиссии',
////                'label' => 'form.trip_advisor_tariff_type.fees.label',
//                'required' => false
//            ])
        ;
        $builder->addEventListener(FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($builder)
            {
                $form = $event->getForm();
                $child = $event->getData();

                if ($child instanceof TripAdvisorTariff) {
                    $form->all()['refundableType']->getConfig()->getOptions()['group'] = $child->getTariff()->getName();
                    $form->getConfig()->getOptions()['tariff'] = $child;
                }
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => TripAdvisorTariff::class,
                'hotel' => null,
                'tariff' => null
            ]);
    }

    public function getBlockPrefix()
    {
        return 'mbhchannel_manager_bundle_trip_advisor_tariff_type';
    }
}
