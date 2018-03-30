<?php


namespace MBH\Bundle\PriceBundle\Form\Batch;


use MBH\Bundle\PriceBundle\Form\SpecialsTransformedType;
use MBH\Bundle\PriceBundle\Lib\SpecialBatchHolder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractBatchType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'specials',
            SpecialsTransformedType::class,
            [
                'attr' => [
                    'class' => 'special-input form-control',
                ],

            ]
        );
    }

    /**
     * @param OptionsResolver $resolver
     * @throws \Symfony\Component\OptionsResolver\Exception\AccessException
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults(
                [
                    'data_class' => SpecialBatchHolder::class,
                    'hotel' => null
                ]
            );
    }

}