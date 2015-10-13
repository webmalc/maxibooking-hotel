<?php

namespace MBH\Bundle\ChannelManagerBundle\Form;

use MBH\Bundle\ChannelManagerBundle\Document\MyallocatorConfig;
use MBH\Bundle\ChannelManagerBundle\Services\MyAllocator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class MyallocatorType extends AbstractType
{

    /**
     * @var MyAllocator
     */
    protected $myallocator;

    public function __construct(MyAllocator $myallocator)
    {
        $this->myallocator = $myallocator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var MyallocatorConfig $config */
        $config = $options['config'];

        if (!$config || !$config->getToken()) {
            $builder
                ->add(
                    'username',
                    'text',
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
                    'password',
                    [
                        'label' => 'form.myallocatorType.password',
                        'mapped' => false,
                        'required' => true,
                        'help' => 'form.myallocatorType.password_desc',
                        'constraints' => [new NotBlank()]
                    ]
                );
        }

        if ($config) {
            $hotels = $this->myallocator->propertyList($config);

            $choices = [];
            foreach ($hotels as $hotel) {
                $choices[$hotel['id']] = $hotel['name'];
            }

            $builder->add('hotelId', 'choice', [
                'label' => 'form.myallocatorType.hotels',
                'required' => true,
                'choices' => $choices,
                'empty_value' => '',
                'constraints' => [new NotBlank()]
            ]);
        }

        $builder
            ->add(
                'isEnabled',
                'checkbox',
                [
                    'label' => 'form.myallocatorType.isEnabled',
                    'value' => true,
                    'required' => false,
                    'help' => 'form.myallocatorType.should_we_use_in_channel_manager'
                ]
            );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'MBH\Bundle\ChannelManagerBundle\Document\MyallocatorConfig',
                'config' => null
            )
        );
    }

    public function getName()
    {
        return 'mbh_bundle_channelmanagerbundle_myallocator_type';
    }

}
