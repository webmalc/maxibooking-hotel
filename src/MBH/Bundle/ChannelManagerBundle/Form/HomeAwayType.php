<?php

namespace MBH\Bundle\ChannelManagerBundle\Form;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType;
use MBH\Bundle\HotelBundle\Document\Hotel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HomeAwayType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('isEnabled', CheckboxType::class, [
                    'label' => 'form.homeAwayType.is_included',
                    'value' => true,
                    'help' => 'form.homeAwayType.should_we_use_in_channel_manager'
                ]
            )
            ->add('main_tariff', DocumentType::class, [
                'label' => 'form.home_away_type.main_tariff.label',
                'help' => 'form.home_away_type.main_tariff.help',
                'class' => 'MBHPriceBundle:Tariff',
                'query_builder' => function (DocumentRepository $er) use ($options) {
                    $qb = $er->createQueryBuilder();
                    if ($options['hotel'] instanceof Hotel) {
                        $qb->field('hotel.id')->equals($options['hotel']->getId());
                    }
                    return $qb;
                },
                'required' => true,
            ])
            ->add('cancellationPolicy', TextareaType::class, [
                'label' => 'form.home_away_type.cancellation_policy.label'
            ])
            ->add('paymentType', InvertChoiceType::class, [
                'label' => 'form.home_away_type.payment_type.label',
                'choices' => $options['payment_types']
            ])
            ->add('locale', ChoiceType::class, [
                'label' => 'form.languageType.label',
                'choice_label' => function($label) {
                    return 'language.'.$label;
                },
                'choices' => $options['languages']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'MBH\Bundle\ChannelManagerBundle\Document\HomeAwayConfig',
                'hotel' => null,
                'payment_types' => [],
                'languages' => []
            )
        );
    }

    public function getBlockPrefix()
    {
        return 'mbh_bundle_channelmanagerbundle_home_away_type';
    }
}