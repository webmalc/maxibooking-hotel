<?php

namespace MBH\Bundle\PriceBundle\Form;

use MBH\Bundle\PriceBundle\Document\Promotion;
use MBH\Bundle\PriceBundle\Services\PromotionConditionFactory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Class PromotionType
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
class PromotionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fullTitle', 'text', [
                'label' => 'form.promotionType.label.fullTitle',
            ])
            ->add('title', 'text', [
                'label' => 'form.promotionType.label.title',
                'required' => false
            ])
            ->add('isIndividual', 'checkbox', [
                'label' => 'form.promotionType.label.isIndividual',
                'required' => false
            ])
            ->add('discount', 'number', [
                'label' => 'form.promotionType.label.discount',
                'constraints' => [
                    new Range(['min' => 1])
                ],
                'required' => false,
            ])
            ->add('isPercentDiscount', 'checkbox', [
                'label' => 'form.promotionType.label.isPercentDiscount',
                'required' => false
            ])
            ->add('comment', 'textarea', [
                'label' => 'form.promotionType.label.comment',
                'required' => false
            ])
            ->add('freeChildrenQuantity', 'number', [
                'label' => 'form.promotionType.label.freeChildrenQuantity',
                'required' => false,
                'attr' => [
                    'style' => 'width:100px',
                ],
            ])
            ->add('freeAdultsQuantity', 'number', [
                'label' => 'form.promotionType.label.freeAdultsQuantity',
                'required' => false,
                'attr' => [
                    'style' => 'width:100px',
                ],
            ])
            ;
        $conditions = PromotionConditionFactory::getAvailableConditions();
        $builder
            ->add('condition', 'choice', [
                'label' => 'form.promotionType.label.condition',
                'required' => false,
                'choices' => array_combine($conditions, $conditions),
                'choice_label' => function($value, $label) {
                    return 'form.promotionType.choice_label.condition.'.$label;
                }
            ])
            ->add('condition_quantity', 'number', [
                'label' => 'form.promotionType.label.condition_quantity',
                'required' => false,
                'attr' => [
                    'style' => 'width:100px',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Promotion::class,
            'constraints' => [
                new Callback([
                    'callback' => function(Promotion $promotion, ExecutionContextInterface $executionContext) {
                        if (!$promotion->getDiscount() && !$promotion->getFreeChildrenQuantity() && !$promotion->getFreeAdultsQuantity()) {
                            $executionContext->buildViolation('Заполните поля скидка или количество детей/взрослых бесплатно');
                            return false;
                        }
                        return true;
                    }
                ])
            ]
        ]);
    }

    public function getName()
    {
        return 'mbh_price_promotion';
    }
}