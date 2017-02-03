<?php

namespace MBH\Bundle\PriceBundle\Form;

use MBH\Bundle\PriceBundle\Document\Promotion;
use MBH\Bundle\PriceBundle\Services\PromotionConditionFactory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Class PromotionType

 */
class PromotionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fullTitle', TextType::class, [
                'label' => 'form.promotionType.label.fullTitle',
                'group' => 'form.promotionType.group.main',
            ])
            ->add('title', TextType::class, [
                'label' => 'form.promotionType.label.title',
                'group' => 'form.promotionType.group.main',
                'required' => false
            ])
            ->add('isIndividual', CheckboxType::class, [
                'label' => 'form.promotionType.label.isIndividual',
                'group' => 'form.promotionType.group.main',
                'required' => false
            ])
            ->add('discount', NumberType::class, [
                'label' => 'form.promotionType.label.discount',
                'group' => 'form.promotionType.group.main',
                'required' => false,
            ])
            ->add('isPercentDiscount', CheckboxType::class, [
                'label' => 'form.promotionType.label.isPercentDiscount',
                'group' => 'form.promotionType.group.main',
                'required' => false
            ])
            ->add('comment', TextareaType::class, [
                'label' => 'form.promotionType.label.comment',
                'group' => 'form.promotionType.group.main',
                'required' => false
            ])
            ->add('freeAdultsQuantity', NumberType::class, [
                'label' => 'form.promotionType.label.freeAdultsQuantity',
                'group' => 'form.promotionType.group.main',
                'required' => false,
                'attr' => [
                    'class' => 'spinner',
                ],
            ])
            ->add('freeChildrenQuantity', NumberType::class, [
                'label' => 'form.promotionType.label.freeChildrenQuantity',
                'group' => 'form.promotionType.group.main',
                'required' => false,
                'attr' => [
                    'class' => 'spinner',
                ],
            ])
            ->add('childrenDiscount', NumberType::class, [
                'label' => 'form.promotionType.label.childrenDiscount',
                'group' => 'form.promotionType.group.main',
                'required' => false,
                'attr' => [
                    'class' => 'percent-spinner',
                ],
            ]);
        $conditions = PromotionConditionFactory::getAvailableConditions();
        $builder
            ->add('condition',  \MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType::class, [
                'label' => 'form.promotionType.label.condition',
                'required' => false,
                'group' => 'form.promotionType.group.conditions',
                'choices' => array_combine($conditions, $conditions),
                'choice_label' => function ($value, $label) {
                    return 'form.promotionType.choice_label.condition.' . $value;
                }
            ])
            ->add('condition_quantity', NumberType::class, [
                'label' => 'form.promotionType.label.condition_quantity',
                'group' => 'form.promotionType.group.conditions',
                'required' => false,
                'error_bubbling' => false,
                'attr' => [
                    'class' => 'spinner',
                ],
            ])
            ->add('additional_condition',  \MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType::class, [
                'label' => 'form.promotionType.label.add_condition',
                'required' => false,
                'group' => 'form.promotionType.group.conditions',
                'choices' => array_combine($conditions, $conditions),
                'choice_label' => function ($value, $label) {
                    return 'form.promotionType.choice_label.condition.' . $value;
                }
            ])
            ->add('additional_condition_quantity', NumberType::class, [
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
                    'callback' => function (Promotion $promotion, ExecutionContextInterface $executionContext) {
                        if (!$promotion->getDiscount() &&
                            !$promotion->getFreeChildrenQuantity() &&
                            !$promotion->getFreeAdultsQuantity() &&
                            !$promotion->getChildrenDiscount()
                        ) {
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

    public function getBlockPrefix()
    {
        return 'mbh_price_promotion';
    }
}