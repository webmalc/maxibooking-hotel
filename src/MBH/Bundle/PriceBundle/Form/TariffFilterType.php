<?php

namespace MBH\Bundle\PriceBundle\Form;

use MBH\Bundle\BaseBundle\Service\HotelSelector;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TariffFilterType extends AbstractType
{
    /**
     * @var HotelSelector
     */
    private $hotelSelector;

    /**
     * @var string $search
     */
    private $search;

    public function __construct($data)
    {
        if($data instanceof HotelSelector) {
            $this->hotelSelector = $data;
        } else {
            $this->search = $data;
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('begin', DateType::class, [
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'required' => false,
                'data' => new \DateTime('midnight')
            ])
            ->add('end', DateType::class, [
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'required' => false
            ])
            ->add('isOnline',  ChoiceType::class, [
                'required' => false,
                'choices' => [
                    'status.on' => true,
                    'status.off' => false
                ]
            ])
            ->add('isEnabled', CheckboxType::class, [
                'label' => 'isEnabled',
                'group' => 'form.group.config',
                'value' => true,
                'required' => false,
            ])
            ->add('search', TextType::class, [
                'required' => false
            ]);

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MBH\Bundle\PriceBundle\Lib\TariffFilter'
        ));
    }

    public function getBlockPrefix()
    {
        return '';
    }

}
