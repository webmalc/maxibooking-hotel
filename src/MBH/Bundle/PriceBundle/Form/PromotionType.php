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
                'group' => 'form.promotionType.group.main',
            ])
            ->add('title', 'text', [
                'label' => 'form.promotionType.label.title',
                'group' => 'form.promotionType.group.main',
                'required' => false
            ])
            ->add('isIndividual', 'checkbox', [
                'label' => 'form.promotionType.label.isIndividual',
                'group' => 'form.promotionType.group.main',
                'required' => false
            ])
            ->add('discount', 'number', [
                'label' => 'form.promotionType.label.discount',
                'group' => 'form.promotionType.group.main',
                'required' => false,
            ])
            ->add('isPercentDiscount', 'checkbox', [
                'label' => 'form.promotionType.label.isPercentDiscount',
                'group' => 'form.promotionType.group.main',
                'required' => false
            ])
            ->add('comment', 'textarea', [
                'label' => 'form.promotionType.label.comment',
                'group' => 'form.promotionType.group.main',
                'required' => false
            ])
            ->add('freeAdultsQuantity', 'number', [
                'label' => 'form.promotionType.label.freeAdultsQuantity',
                'group' => 'form.promotionType.group.main',
                'required' => false,
                'attr' => [
                    'class' => 'spinner',
                ],
            ])
            ->add('freeChildrenQuantity', 'number', [
                'label' => 'form.promotionType.label.freeChildrenQuantity',
                'group' => 'form.promotionType.group.main',
                'required' => false,
                'attr' => [
                    'class' => 'spinner',
                ],
            ])
        ;
        $conditions = PromotionConditionFactory::getAvailableConditions();
        $builder
            ->add('condition', 'choice', [
                'label' => 'form.promotionType.label.condition',
                'required' => false,
                'group' => 'form.promotionType.group.conditions',
                'choices' => array_combine($conditions, $conditions),
                'choice_label' => function($value, $label) {
                    return 'form.promotionType.choice_label.condition.'.$label;
                }
            ])
            ->add('condition_quantity', 'number', [
                'label' => 'form.promotionType.label.condition_quantity',
                'group' => 'form.promotionType.group.conditions',
                'required' => false,
                'error_bubbling' => false,
                'attr' => [
                    'class' => 'spinner',
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
                            $executionContext->buildViolation('Заполните поля "Скидка" или "Количество детей бесплатно" или "Количество взрослых бесплатно"')
                            ->addViolation();
                            return false;
                        }
                        return false;
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