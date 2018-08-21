<?php

namespace MBH\Bundle\ChannelManagerBundle\Form;

use MBH\Bundle\BaseBundle\Service\Currency;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

class BookingType extends ChannelManagerConfigType
{
    /**
     * @var Currency
     */
    protected $currency;

    public function __construct(Currency $currency, TranslatorInterface $translator)
    {
        parent::__construct($translator);
        $this->currency = $currency;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
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
        $resolver->setDefaults([
            'data_class' => 'MBH\Bundle\ChannelManagerBundle\Document\BookingConfig',
            'channelManagerName' => 'Booking.com'
        ]);
    }

    public function getBlockPrefix()
    {
        return 'mbh_bundle_channelmanagerbundle_booking_type';
    }

}
