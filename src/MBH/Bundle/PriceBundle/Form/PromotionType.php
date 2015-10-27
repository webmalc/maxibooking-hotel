<?php

namespace MBH\Bundle\PriceBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Range;

/**
 * Class PromotionType
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
class PromotionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', 'text', [
                'label' => 'form.promotionType.label.title',
            ])
            ->add('fullTitle', 'text', [
                'label' => 'form.promotionType.label.fullTitle',
            ])
            ->add('isIndividual', 'checkbox', [
                'label' => 'form.promotionType.label.isIndividual',
                'required' => false
            ])
            ->add('discount', 'number', [
                'label' => 'form.promotionType.label.discount',
                'constraints' => [
                    new Range(['min' => 1])
                ]
            ])
            ->add('isPercentDiscount', 'checkbox', [
                'label' => 'form.promotionType.label.isPercentDiscount',
                'required' => false
            ])
            ->add('comment', 'textarea', [
                'label' => 'form.promotionType.label.comment',
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'MBH\Bundle\PriceBundle\Document\Promotion'
        ]);
    }

    public function getName()
    {
        return 'mbh_price_promotion';
    }
}