<?php

namespace MBH\Bundle\ChannelManagerBundle\Form;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\HotelBundle\Document\Hotel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
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
                    'required' => false,
                    'help' => 'form.homeAwayType.should_we_use_in_channel_manager'
                ]
            )
            ->add('hotelId', TextType::class, [
                    'label' => 'form.homeAwayType.hotel_id',
                    'required' => true,
                    'attr' => ['placeholder' => 'hotel id'],
                    'help' => 'form.homeAwayType.hotel_id_in_home_away'
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
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'MBH\Bundle\ChannelManagerBundle\Document\HomeAwayConfig',
                'hotel' => null,
            )
        );
    }

    public function getBlockPrefix()
    {
        return 'mbh_bundle_channelmanagerbundle_home_away_type';
    }
}