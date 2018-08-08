<?php

namespace MBH\Bundle\ChannelManagerBundle\Form;

use MBH\Bundle\BaseBundle\Service\Currency;
use MBH\Bundle\ChannelManagerBundle\Document\MyallocatorConfig;
use MBH\Bundle\ChannelManagerBundle\Services\MyAllocator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class MyallocatorType extends AbstractType
{
    /**
     * @var MyAllocator
     */
    protected $myallocator;

    /**
     * @var Currency
     */
    protected $currency;

    public function __construct($myallocator, $currency)
    {
        $this->myallocator = $myallocator;
        $this->currency = $currency;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var MyallocatorConfig $config */
        $config = $options['config'];

        if (!$config || !$config->getToken()) {
            $builder
                ->add(
                    'username',
                    TextType::class,
                    [
                        'label' => 'form.myallocatorType.username',
                        'mapped' => false,
                        'required' => true,
                        'help' => 'form.myallocatorType.username_desc',
                        'constraints' => [new NotBlank()]
                    ]
                )
                ->add(
                    'password',
                    PasswordType::class,
                    [
                        'label' => 'form.myallocatorType.password',
                        'mapped' => false,
                        'required' => true,
                        'help' => 'form.myallocatorType.password_desc',
                        'constraints' => [new NotBlank()]
                    ]
                );
        }

        if ($config && $config->getToken()) {
            $hotels = $this->myallocator->propertyList($config);

            $choices = [];
            foreach ($hotels as $hotel) {
                $choices[$hotel['id']] = $hotel['name'];
            }

            $builder->add('hotelId',  \MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType::class, [
                'label' => 'form.myallocatorType.hotels',
                'required' => true,
                'choices' => $choices,
                'placeholder' => '',
                'constraints' => [new NotBlank()]
            ]);
        }

        if ($config->isReadyToSync()) {
            $builder
                ->add(
                    'isEnabled',
                    CheckboxType::class,
                    [
                        'label' => 'form.myallocatorType.isEnabled',
                        'value' => true,
                        'required' => false,
                        'help' => 'form.myallocatorType.should_we_use_in_channel_manager'
                    ]
                );
        }
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
        $resolver->setDefaults(
            array(
                'data_class' => 'MBH\Bundle\ChannelManagerBundle\Document\MyallocatorConfig',
                'config' => null
            )
        );
    }

    public function getBlockPrefix()
    {
        return 'mbh_bundle_channelmanagerbundle_myallocator_type';
    }

}
