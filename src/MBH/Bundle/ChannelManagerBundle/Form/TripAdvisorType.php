<?php

namespace MBH\Bundle\ChannelManagerBundle\Form;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Doctrine\ODM\MongoDB\DocumentRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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
                'required' => false,
                'attr' => ['placeholder' => 'tarifftype.placeholder']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'MBH\Bundle\ChannelManagerBundle\Document\TripAdvisorConfig',
                'hotel' => null,
            )
        );
    }

    public function getBlockPrefix()
    {
        return 'mbh_bundle_channelmanagerbundle_trip_advisor_type';
    }

}
