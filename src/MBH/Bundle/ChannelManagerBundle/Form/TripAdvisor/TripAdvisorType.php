<?php

namespace MBH\Bundle\ChannelManagerBundle\Form\TripAdvisor;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType;
use MBH\Bundle\ChannelManagerBundle\Form\TripAdvisorTariffType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TripAdvisorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'isEnabled', CheckboxType::class, [
                    'label' => 'form.trip_advisor_type.is_included',
                    'value' => true,
                    'required' => false,
                    'help' => 'form.trip_advisor_type.should_we_use_in_channel_manager'
                ]
            )
            ->add(
                'hotelId', TextType::class, [
                    'label' => 'form.trip_advisor_type.hotel_id',
                    'required' => true,
//                    'attr' => ['placeholder' => ''],
                    'help' => 'form.trip_advisor_type.hotel_id_in_trip_advisor',
                ]
            )
            ->add('main_tariff', DocumentType::class, [
                'label' => 'form.trip_advisor_type.main_tariff.label',
                'help' => 'form.trip_advisor_type.main_tariff.help',
                'class' => 'MBHPriceBundle:Tariff',
                'query_builder' => function(DocumentRepository $er) use($options) {
                    $qb = $er->createQueryBuilder();
                    if ($options['hotel'] instanceof Hotel) {
                        $qb->field('hotel.id')->equals($options['hotel']->getId());
                    }
                    return $qb;
                },
                'placeholder' => '',
                'required' => true,
                'attr' => ['placeholder' => 'tarifftype.placeholder']
            ])
            ->add('locale', ChoiceType::class, [
                'label' => 'form.trip_advisor_type.language.label',
                'choice_label' => function($label) {
                    return 'language.'.$label;
                },
                'choices' => $options['languages']
            ])
            ->add('hotelUrl', TextType::class, [
                'label' => 'form.trip_advisor_type.hotel_url.label',
                'help' => 'form.trip_advisor_type.hotel_url.help'
            ])
            ->add('paymentPolicy', TextareaType::class, [
                'label' => 'form.trip_advisor_type.hotel_payment_policy.label',
                'help' => 'form.trip_advisor_type.hotel_payment_policy.help',
                'attr' => [
                    'placeholder' => 'form.trip_advisor_type.hotel_payment_policy.placeholder'
                ]
            ])
            ->add('termsAndConditions', TextareaType::class, [
                'label' => 'form.trip_advisor_type.terms_and_confitions.label',
                'help' => 'form.trip_advisor_type.terms_and_confitions.help'
            ])
            ->add('paymentType', InvertChoiceType::class, [
                'label' => 'form.trip_advisor_type.payment_type.label',
                'choices' => $options['payment_types']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'MBH\Bundle\ChannelManagerBundle\Document\TripAdvisorConfig',
                'hotel' => null,
                'languages' => [],
                'payment_types' => []
            )
        );
    }

    public function getBlockPrefix()
    {
        return 'mbh_bundle_channelmanagerbundle_trip_advisor_type';
    }

}
