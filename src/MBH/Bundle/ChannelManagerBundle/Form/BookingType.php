<?php

namespace MBH\Bundle\ChannelManagerBundle\Form;

use MBH\Bundle\BaseBundle\Service\Currency;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BookingType extends AbstractType
{
    /**
     * @var Currency
     */
    protected $currency;

    public function __construct(Currency $currency)
    {
        $this->currency = $currency;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'isEnabled',
                CheckboxType::class,
                [
                    'label' => 'form.bookingType.is_included',
                    'value' => true,
                    'required' => false,
                    'help' => 'form.bookingType.should_we_use_in_channel_manager'
                ]
            )
            ->add(
                'hotelId',
                TextType::class,
                [
                    'label' => 'form.bookingType.hotel_id',
                    'required' => true,
                    'attr' => ['placeholder' => 'hotel id'],
                    'help' => 'form.bookingType.hotel_id_in_booking_com'
                ]
            )
            ->add(
                'currency',  \MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType::class,
                [
                    'choices' => $this->currency->codes(),
                    'label' => 'form.bookingType.currency',
                    'required' => false,
                    'help' => 'form.bookingType.currency_help',
                    'attr' => [
                        'class' => 'currency-input'
                    ]
                ]
            )
            ->add(
                'currencyDefaultRatio',
                TextType::class,
                [
                    'label' => 'form.bookingType.currencyDefaultRatio',
                    'required' => false,
                    'help' => 'form.bookingType.currencyDefaultRatio_help',
                    'attr' => [
                        'class' => 'ratio-spinner currency-default-ratio-input'
                    ]
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'MBH\Bundle\ChannelManagerBundle\Document\BookingConfig',
            )
        );
    }

    public function getBlockPrefix()
    {
        return 'mbh_bundle_channelmanagerbundle_booking_type';
    }

}
