<?php

namespace MBH\Bundle\PriceBundle\Form;

use MBH\Bundle\BaseBundle\Service\HotelSelector;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use MBH\Bundle\PriceBundle\Document\Criteria\TariffQueryCriteria;
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

    public function __construct(HotelSelector $hotelSelector)
    {
        $this->hotelSelector = $hotelSelector;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('begin', DateType::class, [
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'required' => false
            ])
            ->add('end', DateType::class, [
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'required' => false
            ])
            ->add('isOnline',  \MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType::class, [
                'required' => false,
                'choices' => [
                    TariffQueryCriteria::ON => 'status.on',
                    TariffQueryCriteria::OFF => 'status.off'
                ]
            ])
            ->add('isEnabled',  \MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType::class, [
                'required' => false,
                'choices' => [
                    TariffQueryCriteria::ON => 'state.on',
                    TariffQueryCriteria::OFF => 'state.off'
                ]
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
